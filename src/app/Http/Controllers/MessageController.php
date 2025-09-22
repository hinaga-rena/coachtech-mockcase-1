<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Message;
use App\Models\Transaction;
use App\Http\Requests\StoreMessageRequest;

class MessageController extends Controller
{
    /**
     * メッセージ送信
     */
    public function store(StoreMessageRequest $request, Transaction $transaction)
    {
        $user = Auth::user();

        // 関係者かどうかチェック
        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();

        // 画像保存（ある場合）
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/messages');
        }

        // 保存
        Message::create([
            'transaction_id' => $transaction->id,
            'user_id'        => $user->id,
            'content'        => $validated['content'],
            'image_path'     => $imagePath,
        ]);

        return redirect()
            ->route('transactions.show', $transaction->id)
            ->with('success', 'メッセージを送信しました');
    }

    /**
     * 編集フォーム表示
     */
    public function edit(Message $message)
    {
        $user = Auth::user();

        // 自分の投稿か確認
        if ($message->user_id !== $user->id) {
            abort(403);
        }

        return view('messages.edit', compact('message'));
    }

    /**
     * 編集内容の保存
     */
    public function update(StoreMessageRequest $request, Message $message)
    {
        $user = Auth::user();

        // 投稿者本人のみ許可
        if ($message->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();

        // 新しい画像がある場合は上書き
        if ($request->hasFile('image')) {
            if ($message->image_path) {
                Storage::delete($message->image_path); // 既存画像削除
            }
            $message->image_path = $request->file('image')->store('public/messages');
        }

        // 内容更新
        $message->content = $validated['content'];
        $message->save();

        return redirect()
            ->route('transactions.show', $message->transaction_id)
            ->with('success', 'メッセージを更新しました');
    }

    /**
     * メッセージ削除
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();

        // 投稿者本人のみ削除可
        if ($message->user_id !== $user->id) {
            abort(403);
        }

        // 添付画像がある場合は削除
        if ($message->image_path) {
            Storage::delete($message->image_path);
        }

        $message->delete();

        return back()->with('success', 'メッセージを削除しました');
    }
}