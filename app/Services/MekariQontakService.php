<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Usaha;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MekariQontakService
{
    protected string $baseUrl;
    protected ?string $token;
    protected ?string $channelId;
    protected ?string $templateId;
    protected ?string $xenditTemplateId;

    public function __construct(?Usaha $usaha = null)
    {
        $usaha = $usaha ?? Usaha::latest()->first();

        $this->baseUrl = config('services.qontak.base_url', env('QONTAK_BASE_URL', 'https://service-chat.qontak.com'));
        $this->token = config('services.qontak.token') ?: ($usaha?->qontak_token ?: env('QONTAK_TOKEN'));
        $this->channelId = config('services.qontak.channel_id') ?: ($usaha?->qontak_channel_id ?: env('QONTAK_CHANNEL_ID'));
        $this->templateId = config('services.qontak.template_id') ?: ($usaha?->qontak_template_id ?: env('QONTAK_TEMPLATE_ID'));
        $this->xenditTemplateId = config('services.qontak.xendit_template_id') ?: (env('QONTAK_XENDIT_TEMPLATE_ID') ?: $this->templateId);
    }

    /**
     * Normalisasi format nomor handphone ke standar internasional Indonesia (62xxx).
     */
    public static function formatPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Hapus spasi, strip, tanda kurung, dan karakter non-digit
        $clean = preg_replace('/[^\d]/', '', $phone);

        if (empty($clean)) {
            return null;
        }

        // 08xxxx -> 628xxxx
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Cek apakah konfigurasi Mekari Qontak sudah terisi lengkap.
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->channelId) && !empty($this->templateId);
    }

    /**
     * Kirim notifikasi WhatsApp transaksi menggunakan template Mekari Qontak.
     *
     * @param string $toPhone
     * @param string $toName
     * @param Transaction $transaction
     * @param string $recipientType 'agent' | 'admin' | 'branch'
     * @return array
     */
    public function sendTransactionNotification(string $toPhone, string $toName, Transaction $transaction, string $recipientType = 'agent'): array
    {
        $formattedPhone = self::formatPhoneNumber($toPhone);

        if (!$formattedPhone) {
            return ['status' => false, 'message' => 'Nomor WhatsApp tidak valid.'];
        }

        if (!$this->isConfigured()) {
            Log::warning('[Mekari Qontak] Pengiriman WhatsApp dibatalkan: Konfigurasi Token, Channel ID, atau Template ID belum lengkap.');
            return ['status' => false, 'message' => 'Kredensial Mekari Qontak belum lengkap.'];
        }

        $customer = $transaction->customer;
        $product = $transaction->product;
        $casier = $transaction->casier;
        $branch = $transaction->branch;
        $date = Carbon::parse($transaction->created_at)->translatedFormat('d F Y, H:i');

        $isXendit = in_array(strtolower($transaction->payment_method ?? ''), ['xendit', 'transfer']);
        $activeTemplateId = !empty($this->templateId) ? $this->templateId : $this->xenditTemplateId;

        // URL struk digital / link pembayaran Xendit
        $linkUrl = ($isXendit && !empty($transaction->xendit_invoice_url) && $transaction->payment_status !== 'paid')
            ? $transaction->xendit_invoice_url
            : route('transaksi.receipt.public', ['id' => $transaction->id]);

        // Keterangan Metode & Status Pembayaran
        if ($isXendit) {
            $paymentInfo = ($transaction->payment_status === 'paid') 
                ? 'TRANSFER (Lunas)' 
                : 'TRANSFER XENDIT (Belum Bayar)';
        } else {
            $paymentInfo = 'CASH / TUNAI';
        }

        // Parameter body dinamis yang dikirimkan ke WhatsApp Template Mekari Qontak
        $bodyParameters = [
            [
                'key' => '1',
                'value' => 'recipient_name',
                'value_text' => $toName,
            ],
            [
                'key' => '2',
                'value' => 'customer_name',
                'value_text' => $customer?->name ?? 'Pelanggan',
            ],
            [
                'key' => '3',
                'value' => 'serial_number',
                'value_text' => (string) ($customer?->code ?? '-'),
            ],
            [
                'key' => '4',
                'value' => 'product_name',
                'value_text' => $product?->name ?? 'Paket',
            ],
            [
                'key' => '5',
                'value' => 'qty',
                'value_text' => (string) ($transaction->qty ?? 1) . ' Unit',
            ],
            [
                'key' => '6',
                'value' => 'total_amount',
                'value_text' => 'Rp ' . number_format($transaction->total ?? 0, 0, ',', '.'),
            ],
            [
                'key' => '7',
                'value' => 'payment_info',
                'value_text' => $paymentInfo,
            ],
            [
                'key' => '8',
                'value' => 'transaction_date',
                'value_text' => $date,
            ],
            [
                'key' => '9',
                'value' => 'cashier_name',
                'value_text' => $branch?->name ?? ($casier?->name ?? 'Kasir'),
            ],
            [
                'key' => '10',
                'value' => 'invoice_url',
                'value_text' => $linkUrl,
            ],
        ];

        $payload = [
            'to_number' => $formattedPhone,
            'to_name' => $toName,
            'message_template_id' => $activeTemplateId,
            'channel_integration_id' => $this->channelId,
            'language' => [
                'code' => 'id',
            ],
            'parameters' => [
                'body' => $bodyParameters,
            ],
        ];

        try {
            $endpoint = rtrim($this->baseUrl, '/') . '/api/open/v1/broadcasts/whatsapp/direct';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("[Mekari Qontak] Berhasil mengirim notifikasi WA ({$recipientType}) ke {$formattedPhone} (Struk #{$transaction->id})");
                return ['status' => true, 'data' => $response->json()];
            } else {
                Log::error("[Mekari Qontak] Gagal mengirim notifikasi WA ke {$formattedPhone}: " . $response->body());
                return ['status' => false, 'message' => $response->body()];
            }
        } catch (Exception $e) {
            Log::error("[Mekari Qontak] Error request ke API Mekari Qontak: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
