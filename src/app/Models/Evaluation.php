<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'product_id',
        'rating',
        'comment',
    ];

    // 🔽 リレーション（評価した人）
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    // 🔽 リレーション（評価された人）
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // 🔽 リレーション（評価対象の商品）
    public function product()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }
}