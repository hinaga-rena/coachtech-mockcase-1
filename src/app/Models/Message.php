<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'content',
        'image_path',
        'read_at',
    ];

    // ユーザー情報（投稿者）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 取引情報
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}