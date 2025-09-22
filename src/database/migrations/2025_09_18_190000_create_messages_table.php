<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id'); // どの取引のメッセージか
            $table->unsignedBigInteger('user_id');        // 投稿者（購入者 or 出品者）
            $table->text('content');                      // 本文
            $table->string('image_path')->nullable();     // 画像（任意）
            $table->timestamp('read_at')->nullable();     // 既読かどうかの判定用
            $table->timestamps();

            // 外部キー制約
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
