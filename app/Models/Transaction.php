<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    protected $guarded = [];

    public function casier() 
    {
        return $this->belongsTo(User::class, 'users_id', 'id');    
    }

    public function customer() 
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'id');    
    }

    public function product() 
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');    
    }
}
