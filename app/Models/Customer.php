<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';
    protected $fillable = [
        'user_id',
        'products_id',
        'code',
        'name',
        'telp',
        'email',
        'nik',
        'address',
        'limit',
        'types_id',
        'status_id',
        'payment_methods',
    ];

    protected $casts = [
        'payment_methods' => 'array',
    ];

    protected $appends = ['encrypted_code', 'nfc_url'];

    /**
     * Enkripsi Serial Number menjadi token URL-safe.
     */
    public static function encryptCode(string $code): string
    {
        if (empty($code)) {
            return '';
        }
        $encrypted = Crypt::encryptString($code);
        return strtr($encrypted, '+/=', '-_,');
    }

    /**
     * Dekripsi token URL-safe kembali ke Serial Number asli.
     */
    public static function decryptCode(string $token): ?string
    {
        try {
            if (empty($token)) {
                return null;
            }
            $base64 = strtr($token, '-_,', '+/=');
            return Crypt::decryptString($base64);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Accessor untuk token terenkripsi.
     */
    public function getEncryptedCodeAttribute(): string
    {
        return self::encryptCode($this->code ?? '');
    }

    /**
     * Accessor untuk full link transaksi NFC Agent.
     */
    public function getNfcUrlAttribute(): string
    {
        if (empty($this->code)) {
            return '';
        }
        return route('transaksi.agent.create', ['token' => $this->encrypted_code]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product() 
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');    
    }

    public function type() 
    {
        return $this->belongsTo(CustomerType::class, 'types_id', 'id');    
    }

    public function status() 
    {
        return $this->belongsTo(CustomerStatus::class, 'status_id', 'id');    
    }
}
