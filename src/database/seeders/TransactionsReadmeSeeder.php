<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\SoldItem;
use App\Models\Message;
use App\Models\Evaluation;

class TransactionsReadmeSeeder extends Seeder
{
    public function run()
    {
        // ▼ 商品IDごとの出品者（ItemsReadmeSeeder と一致）
        //   1〜5: seller = 3, 6〜10: seller = 4
        $sellerOf = function (int $productId): int {
            return $productId <= 5 ? 3 : 4;
        };
        // ▼ デモ購入者：1〜5 は buyer=4、6〜10 は buyer=3
        $buyerOf = function (int $productId): int {
            return $productId <= 5 ? 4 : 3;
        };

        // ▼ 住所テンプレ（SoldItem 用）
        $addr = [
            3 => [ // ユーザー3（東京）
                'sending_postcode' => '1500001',
                'sending_address'  => '東京都渋谷区神宮前1-1-1',
                'sending_building' => 'ハピネスタワー301',
            ],
            4 => [ // ユーザー4（大阪）
                'sending_postcode' => '5300001',
                'sending_address'  => '大阪府大阪市北区梅田1-1-1',
                'sending_building' => 'スマイルビル502',
            ],
        ];

        // ▼ 今回触る商品ID
        $targetProductIds = range(1, 10);

        DB::transaction(function () use ($targetProductIds, $sellerOf, $buyerOf, $addr) {

            // ---- 既存データのクリーンアップ（対象商品に関係するもののみ） ----
            // 取引IDを拾ってから関連メッセージ→取引→評価→購入履歴の順で削除
            $txIds = Transaction::whereIn('product_id', $targetProductIds)->pluck('id');

            if ($txIds->isNotEmpty()) {
                Message::whereIn('transaction_id', $txIds)->delete();
            }
            Evaluation::whereIn('product_id', $targetProductIds)->delete();
            Transaction::whereIn('product_id', $targetProductIds)->delete();
            SoldItem::whereIn('item_id', $targetProductIds)->delete();

            // =======================
            // 1,6: 出品中（何も作らない）
            // =======================
            // → 何も入れません。出品されたままの状態になります。

            // =======================
            // 2,7: 取引中（未完了）
            // =======================
            foreach ([2, 7] as $pid) {
                $sellerId = $sellerOf($pid);
                $buyerId  = $buyerOf($pid);

                // 未完了の取引だけ作成（購入履歴はまだ作らない）
                $t = Transaction::create([
                    'buyer_id'     => $buyerId,
                    'seller_id'    => $sellerId,
                    'product_id'   => $pid,
                    'is_completed' => false,
                ]);

                // 簡単なチャット2通
                Message::create([
                    'user_id'        => $buyerId,
                    'transaction_id' => $t->id,
                    'content'        => "商品{$pid}の件、よろしくお願いします！",
                    'created_at'     => Carbon::now()->subMinutes(8),
                ]);
                Message::create([
                    'user_id'        => $sellerId,
                    'transaction_id' => $t->id,
                    'content'        => "ご連絡ありがとうございます。発送準備いたします！",
                    'created_at'     => Carbon::now()->subMinutes(4),
                ]);
            }

            // =========================================
            // 3,4,5,8,9,10: 取引完了（購入履歴 + 完了取引）
            // =========================================
            foreach ([3, 4, 5, 8, 9, 10] as $pid) {
                $sellerId = $sellerOf($pid);
                $buyerId  = $buyerOf($pid);

                // 購入履歴
                SoldItem::create([
                    'user_id'          => $buyerId,
                    'item_id'          => $pid,
                    'sending_postcode' => $addr[$buyerId]['sending_postcode'],
                    'sending_address'  => $addr[$buyerId]['sending_address'],
                    'sending_building' => $addr[$buyerId]['sending_building'],
                ]);

                // 完了取引
                $t = Transaction::create([
                    'buyer_id'     => $buyerId,
                    'seller_id'    => $sellerId,
                    'product_id'   => $pid,
                    'is_completed' => true,
                ]);

                // チャット
                Message::create([
                    'user_id'        => $buyerId,
                    'transaction_id' => $t->id,
                    'content'        => "商品{$pid}の購入手続き完了しました。ありがとうございました！",
                    'created_at'     => Carbon::now()->subMinutes(10),
                ]);
                Message::create([
                    'user_id'        => $sellerId,
                    'transaction_id' => $t->id,
                    'content'        => "ご購入ありがとうございます。評価もよろしくお願いします！",
                    'created_at'     => Carbon::now()->subMinutes(5),
                ]);

                // 相互評価（例：どちらも★5）
                Evaluation::create([
                    'from_user_id' => $buyerId,
                    'to_user_id'   => $sellerId,
                    'product_id'   => $pid,
                    'rating'       => 5,
                    'comment'      => '丁寧なご対応でした！',
                ]);
                Evaluation::create([
                    'from_user_id' => $sellerId,
                    'to_user_id'   => $buyerId,
                    'product_id'   => $pid,
                    'rating'       => 5,
                    'comment'      => 'スムーズなお取引、ありがとうございました！',
                ]);
            }
        });

        // これで最終状態は：
        // - 商品1,6 … 出品中（誰も購入していない）
        // - 商品2,7 … 取引中（未完了）
        // - 商品3,4,5,8,9,10 … 完了
    }
}