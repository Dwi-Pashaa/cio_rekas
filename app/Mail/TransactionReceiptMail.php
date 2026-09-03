<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\Usaha;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public ?Usaha $usaha;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, ?Usaha $usaha = null)
    {
        $this->transaction = $transaction->loadMissing(['customer', 'customer.type', 'product', 'casier', 'branch']);
        $this->usaha = $usaha ?? Usaha::latest()->first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $storeName = $this->usaha->name ?? config('app.name', 'CIO REKAS');
        $code = $this->transaction->customer?->code ?? $this->transaction->id;

        return new Envelope(
            subject: "Struk Transaksi Pembelian - {$storeName} (SN: {$code})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction_receipt',
            with: [
                'transaction' => $this->transaction,
                'usaha'       => $this->usaha,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
