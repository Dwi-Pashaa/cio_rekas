<?php

namespace App\Services;

use App\Mail\TransactionReceiptMail;
use App\Models\Transaction;
use App\Models\Usaha;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected MekariQontakService $qontakService;

    public function __construct(MekariQontakService $qontakService)
    {
        $this->qontakService = $qontakService;
    }

    /**
     * Jalankan pengiriman notifikasi transaksi (WhatsApp & Email) berdasarkan setting aktif.
     */
    public function sendTransactionNotifications(Transaction $transaction): array
    {
        $usaha = Usaha::latest()->first();
        $results = [
            'wa_agent'   => null,
            'wa_branch'  => null,
            'wa_admin'   => null,
            'email_agent'=> null,
        ];

        if (!$usaha) {
            return $results;
        }

        // Load relasi yang dibutuhkan jika belum ter-load
        $transaction->loadMissing(['customer', 'product', 'casier', 'branch']);
        $customer = $transaction->customer;
        $branch = $transaction->branch;

        // 1. NOTIFIKASI WHATSAPP (Mekari Qontak)
        if ($usaha->enable_wa_notification) {
            // A. Kirim ke nomor WhatsApp Agent
            if ($customer && !empty($customer->telp)) {
                try {
                    $results['wa_agent'] = $this->qontakService->sendTransactionNotification(
                        $customer->telp,
                        $customer->name ?? 'Agent',
                        $transaction,
                        'agent'
                    );
                } catch (Exception $e) {
                    Log::error('[NotificationService] Error kirim WA ke Agent: ' . $e->getMessage());
                    $results['wa_agent'] = ['status' => false, 'message' => $e->getMessage()];
                }
            } else {
                Log::info("[NotificationService] Skip WA Agent: No. telepon agent kosong (Trx #{$transaction->id})");
            }

            // B. Kirim ke nomor WhatsApp Kasir Cabang Terpilih
            if ($branch && !empty($branch->wa_number)) {
                try {
                    $results['wa_branch'] = $this->qontakService->sendTransactionNotification(
                        $branch->wa_number,
                        'Kasir ' . ($branch->name ?? 'Cabang'),
                        $transaction,
                        'branch'
                    );
                    Log::info("[NotificationService] Berhasil kirim WA ke Kasir Cabang {$branch->name} ({$branch->wa_number}) untuk Trx #{$transaction->id}");
                } catch (Exception $e) {
                    Log::error('[NotificationService] Error kirim WA ke Kasir Cabang: ' . $e->getMessage());
                    $results['wa_branch'] = ['status' => false, 'message' => $e->getMessage()];
                }
            } else {
                Log::info("[NotificationService] Skip WA Kasir Cabang: No. WhatsApp cabang belum diatur untuk Cabang " . ($branch->name ?? 'N/A'));
            }

            // C. Kirim ke nomor WhatsApp Admin
            if (!empty($usaha->admin_wa_number)) {
                try {
                    $results['wa_admin'] = $this->qontakService->sendTransactionNotification(
                        $usaha->admin_wa_number,
                        'Admin ' . ($usaha->name ?? 'Toko'),
                        $transaction,
                        'admin'
                    );
                } catch (Exception $e) {
                    Log::error('[NotificationService] Error kirim WA ke Admin: ' . $e->getMessage());
                    $results['wa_admin'] = ['status' => false, 'message' => $e->getMessage()];
                }
            } else {
                Log::info("[NotificationService] Skip WA Admin: No. WhatsApp admin belum diatur di Pengaturan");
            }
        }

        // 2. NOTIFIKASI EMAIL (SMTP) - HANYA KE PELANGGAN
        if ($usaha->enable_email_notification) {
            if ($customer && !empty($customer->email)) {
                try {
                    Mail::to($customer->email)->send(new TransactionReceiptMail($transaction, $usaha));
                    Log::info("[NotificationService] Berhasil mengirim email struk transaksi ke {$customer->email} (Trx #{$transaction->id})");
                    $results['email_agent'] = ['status' => true, 'message' => 'Email terkirim ke ' . $customer->email];
                } catch (Exception $e) {
                    Log::error("[NotificationService] Gagal mengirim email ke {$customer->email}: " . $e->getMessage());
                    $results['email_agent'] = ['status' => false, 'message' => $e->getMessage()];
                }
            } else {
                Log::info("[NotificationService] Skip Email: Pelanggan tidak memiliki alamat email (Trx #{$transaction->id})");
            }
        }

        return $results;
    }
}
