<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Usaha;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    /**
     * Buat Invoice Pembayaran Xendit (QRIS, VA, E-Wallet, Retail Outlet).
     * Standar Multi-Web Central Router: Prefix KASIR-TRX-{id}-{timestamp}
     */
    public function createInvoice(Transaction $transaction, Customer $customer, float $amount, Usaha $usaha): array
    {
        $secretKey = $usaha->xendit_secret_key ?: env('XENDIT_SECRET_KEY');

        if (empty($secretKey)) {
            throw new Exception('Xendit Secret API Key belum dikonfigurasi di Pengaturan Toko.');
        }

        $externalId = 'KASIR-TRX-' . $transaction->id . '-' . time();
        $payerEmail = $customer->email ?: (optional($transaction->casier)->email ?: 'customer@ciorekas.com');
        $productName = optional($transaction->product)->name ?? 'Paket Voucher Agent';

        $payload = [
            'external_id'          => $externalId,
            'amount'               => (int) $amount,
            'payer_email'          => $payerEmail,
            'description'          => "Pesanan Kasir Agent: {$customer->name} - {$productName} ({$transaction->qty} Unit)",
            'invoice_duration'     => 86400, // 24 jam
            'customer' => [
                'given_names'   => $customer->name,
                'email'         => $payerEmail,
                'mobile_number' => $customer->telp ?? '',
            ],
            'customer_notification_preference' => [
                'invoice_created'  => ['whatsapp', 'email'],
                'invoice_reminder' => ['whatsapp', 'email'],
                'invoice_paid'     => ['whatsapp', 'email'],
            ],
            'success_redirect_url' => route('dashboard'),
            'failure_redirect_url' => route('dashboard'),
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.xendit.co/v2/invoices', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update transaction with Xendit invoice details & KASIR external ID
                $transaction->update([
                    'xendit_external_id' => $externalId,
                    'xendit_invoice_id'  => $data['id'] ?? null,
                    'xendit_invoice_url' => $data['invoice_url'] ?? null,
                    'payment_status'     => 'pending',
                    'payment'            => 0,
                ]);

                return [
                    'status'             => true,
                    'external_id'        => $externalId,
                    'invoice_id'         => $data['id'] ?? null,
                    'invoice_url'        => $data['invoice_url'] ?? null,
                    'data'               => $data,
                ];
            } else {
                $errorMsg = $response->json()['message'] ?? $response->body();
                Log::error('[XenditService] Gagal membuat invoice: ' . $errorMsg);
                throw new Exception('Gagal membuat invoice Xendit: ' . $errorMsg);
            }
        } catch (Exception $e) {
            Log::error('[XenditService] Exception saat membuat invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle Webhook Callback dari Xendit / Central Router saat pembayaran masuk.
     */
    public function handleWebhook(array $payload, ?string $incomingToken, Usaha $usaha): array
    {
        $configuredToken = $usaha->xendit_webhook_token ?: env('XENDIT_WEBHOOK_TOKEN');

        if (!empty($configuredToken) && !empty($incomingToken) && $incomingToken !== $configuredToken) {
            Log::warning('[XenditService] Webhook callback token mismatch. Incoming Token: ' . $incomingToken . ' vs Configured Token: ' . $configuredToken);
            return ['status' => false, 'message' => 'Invalid webhook verification token.'];
        }

        $externalId = $payload['external_id'] ?? $payload['data']['external_id'] ?? null;
        $status = strtoupper($payload['status'] ?? $payload['data']['status'] ?? '');
        $paidAmount = $payload['paid_amount'] ?? $payload['amount'] ?? $payload['data']['amount'] ?? 0;
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? null;
        
        $paymentChannel = $payload['payment_channel'] 
            ?? $payload['payment_method'] 
            ?? $payload['bank_code'] 
            ?? $payload['data']['payment_channel'] 
            ?? $payload['data']['payment_method'] 
            ?? $payload['data']['bank_code'] 
            ?? null;

        // Jika ini adalah test webhook dari Dashboard Xendit
        if (str_starts_with((string)$externalId, 'disbursement_') || str_starts_with((string)$externalId, 'sample_') || empty($externalId)) {
            Log::info("[XenditService] Test/dummy webhook payload acknowledged: {$externalId}");
            return ['status' => true, 'message' => 'Kasir callback handled (test acknowledgment)'];
        }

        // Cari transaksi berdasarkan:
        // 1. xendit_external_id
        // 2. xendit_invoice_id
        // 3. ID dari parsing format KASIR-TRX-{id} atau TRX-{id}
        $transaction = null;

        if ($externalId) {
            $transaction = Transaction::with(['customer', 'product', 'branch', 'casier'])
                ->where('xendit_external_id', $externalId)
                ->first();
        }

        if (!$transaction && $invoiceId) {
            $transaction = Transaction::with(['customer', 'product', 'branch', 'casier'])
                ->where('xendit_invoice_id', $invoiceId)
                ->first();
        }

        if (!$transaction && $externalId) {
            if (preg_match('/(?:KASIR-)?TRX-(\d+)/i', $externalId, $matches)) {
                $trxId = (int) $matches[1];
                $transaction = Transaction::with(['customer', 'product', 'branch', 'casier'])->find($trxId);
            }
        }

        if (!$transaction) {
            Log::warning("[XenditService] Transaction not found for external_id: {$externalId}, invoice_id: {$invoiceId}");
            return ['status' => true, 'message' => 'Transaction not found in Kasir'];
        }

        // Cek apakah event ini adalah status lunas
        $isPaidEvent = in_array($status, ['PAID', 'SETTLED', 'COMPLETED', 'SUCCEEDED']) 
            || (!empty($payload['payment_id']) && $status !== 'FAILED');

        if ($isPaidEvent) {
            $paidAt = isset($payload['paid_at']) 
                ? \Carbon\Carbon::parse($payload['paid_at']) 
                : (isset($payload['data']['paid_at']) ? \Carbon\Carbon::parse($payload['data']['paid_at']) : now());

            $transaction->update([
                'payment_status'     => 'paid',
                'payment'            => $paidAmount ?: $transaction->total,
                'payment_channel'    => $paymentChannel ?: $transaction->payment_channel,
                'paid_at'            => $paidAt,
                'xendit_external_id' => $externalId ?: $transaction->xendit_external_id,
            ]);

            // Refresh data transaksi agar status 'paid' terupdate di memori
            $transaction->refresh();
            $transaction->loadMissing(['customer', 'product', 'casier', 'branch']);

            // Kirim notifikasi WhatsApp pembayaran Lunas ke Kasir Cabang, Agent, dan Admin
            try {
                app(NotificationService::class)->sendTransactionNotifications($transaction);
                Log::info("[XenditService] Notifikasi WhatsApp pelunasan berhasil dikirim untuk Trx #{$transaction->id}");
            } catch (Exception $e) {
                Log::error('[XenditService] Error trigger notifikasi webhook: ' . $e->getMessage());
            }

            Log::info("[XenditService] Transaksi Kasir #{$transaction->id} ({$externalId}) berhasil dilunasi via {$paymentChannel} ({$paidAmount})");

            return ['status' => 'success', 'message' => 'Kasir callback handled'];
        }

        if (in_array($status, ['EXPIRED'])) {
            $transaction->update(['payment_status' => 'expired']);
            return ['status' => 'success', 'message' => 'Kasir callback handled'];
        }

        return ['status' => 'success', 'message' => 'Kasir callback handled'];
    }
}
