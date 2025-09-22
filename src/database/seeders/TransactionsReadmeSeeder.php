<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\SoldItem;
use App\Models\Message;
use App\Models\Evaluation;
use Illuminate\Support\Carbon;

class TransactionsReadmeSeeder extends Seeder
{
    public function run()
    {
        // 🔽 ユーザー4が商品1（ユーザー3の出品）を購入（取引中）
        $transaction = Transaction::create([
            'buyer_id' => 4,
            'seller_id' => 3,
            'product_id' => 1,
            'is_completed' => false, // ✅ 取引中にする
        ]);

        // 🔽 購入商品登録（購入履歴に必要）
        SoldItem::create([
            'user_id' => 4,          // 購入者ID
            'item_id' => 1,          // 商品ID
            'sending_postcode' => '123-4567',
            'sending_address' => '東京都渋谷区1-2-3',
            'sending_building' => 'サンプルマンション101',
        ]);

        // 🔽 チャットメッセージ（購入者→出品者）
        Message::create([
            'user_id' => 4,
            'transaction_id' => $transaction->id,
            'content' => 'はじめまして、よろしくお願いします。',
            'created_at' => Carbon::now()->subMinutes(10),
        ]);

        // 🔽 チャットメッセージ（出品者→購入者）
        Message::create([
            'user_id' => 3,
            'transaction_id' => $transaction->id,
            'content' => 'ご購入ありがとうございます！',
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        // 🔽 評価（購入者 → 出品者）
        Evaluation::create([
            'from_user_id' => 4,
            'to_user_id' => 3,
            'product_id' => 1,
            'rating' => 5,
            'comment' => 'とても丁寧な対応でした！',
        ]);

        // 🔽 評価（出品者 → 購入者）
        Evaluation::create([
            'from_user_id' => 3,
            'to_user_id' => 4,
            'product_id' => 1,
            'rating' => 5,
            'comment' => 'スムーズなお取引ありがとうございました！',
        ]);
    }
}