<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';
    protected $fillable = ['products_id', 'code', 'name', 'telp', 'address', 'limit', 'types_id', 'status_id'];

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
