<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = ['categories_id', 'code', 'name', 'stock', 'base_price', 'selling_price'];

    public function categori() 
    {
        return $this->belongsTo(Categori::class, 'categories_id', 'id');    
    }
}
