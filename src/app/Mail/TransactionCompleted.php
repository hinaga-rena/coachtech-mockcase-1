<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    /**
     * コンストラクタ
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * メールの内容を構築
     */
    public function build()
    {
        return $this->subject('取引完了のお知らせ')
                    ->markdown('emails.transactions.completed');
    }
}