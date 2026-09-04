<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = 'branche';
    protected $fillable = ['name', 'wa_number'];

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'branch_id', 'id');
    }
}
