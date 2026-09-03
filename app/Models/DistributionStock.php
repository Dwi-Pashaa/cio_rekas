<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionStock extends Model
{
    use HasFactory;

    protected $table = 'distribution_stocks';

    protected $fillable = [
        'type',
        'user_id',
        'product_name',
        'stock',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
