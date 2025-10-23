<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Evaluation;
use App\Mail\TransactionCompleted;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{
    /**
     * 取引チャット画面
     */
    public function show(Transaction $transaction)
    {
        $user = Auth::user();

        // 当事者以外は403
        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            abort(403);
        }

        // ✅ 相手からの未読のみ既読化（先に実行してから eager load）
        $me = $user->id;
        $transaction->messages()
            ->whereNull('read_at')
            ->where('user_id', '!=', $me)
            ->update(['read_at' => now()]);

        // 関連の先読み（buyer/sellerも読んでおくとpartner参照が速い）
        $transaction->load([
            'product',
            'buyer',
            'seller',
            'messages.user',
            'evaluations',
        ]);

        // 立場
        $isBuyer = $transaction->buyer_id === $user->id;
        $isSeller = $transaction->seller_id === $user->id;

        // 相手
        $partner = $isBuyer ? $transaction->seller : $transaction->buyer;

        // 自分は評価済みか
        $hasRated = $transaction->evaluations->contains('from_user_id', $user->id);

        // ⭐ モーダル表示条件
        // 1) 完了直後（購入者）：complete() からのフラグ
        // 2) 完了済み かつ 未評価（出品者／購入者の再訪問時）
        $showRatingModal = session('showRatingModal', false) || ($transaction->is_completed && !$hasRated);

        // 自分の他取引（サイドバー）
        $otherTransactions = Transaction::where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            })
            ->where('id', '!=', $transaction->id)
            ->with('product')
            ->latest()
            ->get();

        return view('transactions.show', compact(
            'transaction',
            'user',
            'isBuyer',
            'isSeller',
            'showRatingModal',
            'otherTransactions',
            'partner'
        ));
    }

    /**
     * 取引完了（購入者のみ）
     * 完了後は評価モーダルを強制表示するためのセッションを付与
     */
    public function complete(Transaction $transaction)
    {
        $user = Auth::user();

        if ($transaction->buyer_id !== $user->id) {
            abort(403);
        }

        // すでに完了していれば何もしない（冪等）
        if (!$transaction->is_completed) {
            $transaction->update(['is_completed' => true]);

            Mail::to($transaction->seller->email)
                ->send(new TransactionCompleted($transaction));
        }

        // ⭐ 完了直後にモーダルを出すためのフラグ
        return redirect()
            ->route('transactions.show', $transaction->id)
            ->with('showRatingModal', true);
    }

    /**
     * 評価の送信（購入者・出品者どちらも可）
     * 二重評価を防止し、送信後は商品一覧へ
     */
    public function rate(Request $request, Transaction $transaction)
    {
        $user = Auth::user();

        // 当事者のみ
        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            abort(403);
        }

        // 取引が未完了なら評価させない（要件上：完了後評価）
        if (!$transaction->is_completed) {
            return back()->with('error', '取引完了後に評価できます。');
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // 既に自分が評価していたら弾く（二重投稿防止）
        $alreadyRated = Evaluation::where('from_user_id', $user->id)
            ->where('product_id', $transaction->product_id)
            ->exists();

        if ($alreadyRated) {
            return redirect()->route('items.list')->with('info', 'すでに評価済みです。');
        }

        // 相手ユーザーID
        $toUserId = ($transaction->buyer_id === $user->id)
            ? $transaction->seller_id
            : $transaction->buyer_id;

        Evaluation::create([
            'from_user_id' => $user->id,
            'to_user_id'   => $toUserId,
            'product_id'   => $transaction->product_id,
            'rating'       => $validated['rating'],
            'comment'      => $validated['comment'] ?? null,
        ]);

        // ⭐ 要件：評価送信後は商品一覧へ（FN014）
        return redirect()->route('items.list')->with('success', '評価を送信しました。');
    }
}