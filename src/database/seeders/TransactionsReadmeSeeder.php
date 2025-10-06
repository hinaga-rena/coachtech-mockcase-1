<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Transaction;
use App\Models\SoldItem;
use App\Models\Message;
use App\Models\Evaluation;

class TransactionsReadmeSeeder extends Seeder
{
    public function run()
    {
        // ユーザー3＝東京（buyer/seller切替）、ユーザー4＝大阪
        $tokyoUserId = 3;
        $osakaUserId = 4;

        $tokyoAddr = [
            'sending_postcode' => '1500001',
            'sending_address'  => '東京都渋谷区神宮前1-1-1',
            'sending_building' => 'ハピネスタワー301',
        ];
        $osakaAddr = [
            'sending_postcode' => '5300001',
            'sending_address'  => '大阪府大阪市北区梅田1-1-1',
            'sending_building' => 'スマイルビル502',
        ];

        // -----------------------------
        // ユーザー3が商品1〜5を購入（完了）
        // -----------------------------
        for ($pid = 1; $pid <= 5; $pid++) {
            // 購入履歴
            SoldItem::create([
                'user_id'          => $tokyoUserId, // 購入者
                'item_id'          => $pid,
                'sending_postcode' => $tokyoAddr['sending_postcode'],
                'sending_address'  => $tokyoAddr['sending_address'],
                'sending_building' => $tokyoAddr['sending_building'],
            ]);

            // 取引（完了）
            $t = Transaction::create([
                'buyer_id'     => $tokyoUserId,
                'seller_id'    => $osakaUserId,
                'product_id'   => $pid,
                'is_completed' => true,
            ]);

            // チャット（2通）
            Message::create([
                'user_id'        => $tokyoUserId,
                'transaction_id' => $t->id,
                'content'        => "商品{$pid}の購入手続き完了しました。よろしくお願いします！",
                'created_at'     => Carbon::now()->subMinutes(10),
            ]);
            Message::create([
                'user_id'        => $osakaUserId,
                'transaction_id' => $t->id,
                'content'        => "ご購入ありがとうございます。迅速に発送いたします！",
                'created_at'     => Carbon::now()->subMinutes(5),
            ]);

            // 相互評価
            Evaluation::create([
                'from_user_id' => $tokyoUserId,
                'to_user_id'   => $osakaUserId,
                'product_id'   => $pid,
                'rating'       => 5,
                'comment'      => 'とても丁寧な対応でした！',
            ]);
            Evaluation::create([
                'from_user_id' => $osakaUserId,
                'to_user_id'   => $tokyoUserId,
                'product_id'   => $pid,
                'rating'       => 5,
                'comment'      => 'スムーズなお取引ありがとうございました！',
            ]);
        }

        // -----------------------------
        // ユーザー4が商品6〜10を購入（完了）
        // -----------------------------
        for ($pid = 6; $pid <= 10; $pid++) {
            SoldItem::create([
                'user_id'          => $osakaUserId,
                'item_id'          => $pid,
                'sending_postcode' => $osakaAddr['sending_postcode'],
                'sending_address'  => $osakaAddr['sending_address'],
                'sending_building' => $osakaAddr['sending_building'],
            ]);

            $t = Transaction::create([
                'buyer_id'     => $osakaUserId,
                'seller_id'    => $tokyoUserId,
                'product_id'   => $pid,
                'is_completed' => true,
            ]);

            Message::create([
                'user_id'        => $osakaUserId,
                'transaction_id' => $t->id,
                'content'        => "商品{$pid}の購入が完了しました。発送お願いします！",
                'created_at'     => Carbon::now()->subMinutes(9),
            ]);
            Message::create([
                'user_id'        => $tokyoUserId,
                'transaction_id' => $t->id,
                'content'        => "ありがとうございます。すぐに発送手配いたします！",
                'created_at'     => Carbon::now()->subMinutes(4),
            ]);

            Evaluation::create([
                'from_user_id' => $osakaUserId,
                'to_user_id'   => $tokyoUserId,
                'product_id'   => $pid,
                'rating'       => 5,
                'comment'      => '説明どおりの良品でした！',
            ]);
            Evaluation::create([
                'from_user_id' => $tokyoUserId,
                'to_user_id'   => $osakaUserId,
                'product_id'   => $pid,
                'rating'       => 5,
                'comment'      => 'ご連絡が早く安心して取引できました！',
            ]);
        }
    }
}