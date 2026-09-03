<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionHistory extends Model
{
    use HasFactory;

    protected $table = 'distribution_histories';

    protected $fillable = [
        'reference_no',
        'type',
        'sender_id',
        'receiver_id',
        'target_branch_id',
        'product_name',
        'qty',
        'stock_before',
        'stock_after',
        'notes',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function targetBranch()
    {
        return $this->belongsTo(Branch::class, 'target_branch_id', 'id');
    }
}
