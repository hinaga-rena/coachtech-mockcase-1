<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade'); // 評価する人（購入者 or 出品者）
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');   // 評価される人（相手）
            $table->foreignId('product_id')->constrained('items')->onDelete('cascade');   // 商品ID
            $table->unsignedTinyInteger('rating'); // 1〜5 の評価（星）
            $table->text('comment')->nullable();   // コメント（任意）
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
}