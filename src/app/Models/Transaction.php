<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Evaluation;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    // 🔽 リレーション
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // この取引の商品に対する評価一覧（from_user_idやto_user_idでフィルター可能）
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'product_id', 'product_id');
    }
}