<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usaha extends Model
{
    use HasFactory;
    protected $table = 'usahas';
    protected $guarded = [];

    protected $casts = [
        'enable_wa_notification' => 'boolean',
        'enable_email_notification' => 'boolean',
    ];
}
