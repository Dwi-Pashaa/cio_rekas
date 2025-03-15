<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';
    protected $fillable = ['products_id', 'code', 'name', 'telp', 'address', 'limit', 'type', 'status'];

    public function product() 
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');    
    }
}
