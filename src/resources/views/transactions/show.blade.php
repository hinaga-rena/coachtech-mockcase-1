@extends('layouts.default')

@section('title', '取引チャット')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/chat.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="transaction__container">

    {{-- サイドバー（他の取引） --}}
    <aside class="sidebar">
        <h3>その他の取引</h3>
        <ul>
            @foreach($otherTransactions as $t)
                <li>
                    <a href="{{ route('transactions.show', $t->id) }}">
                        {{ $t->product->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    {{-- メインチャット画面 --}}
    <main class="chat__main">
        <div class="chat__header">
            <h2>「{{ $partner->name }}」さんとの取引画面</h2>
            @if($isBuyer && !$transaction->is_completed)
                <form action="{{ route('transactions.complete', $transaction->id) }}" method="POST">
                    @csrf
                    <button class="btn--complete" type="submit">取引を完了する</button>
                </form>
            @endif
        </div>

        {{-- 商品情報（public/products or storage 自動判定） --}}
        @php
            $prodPath = $transaction->product->img_url;
            $productImg = \Illuminate\Support\Str::startsWith($prodPath, 'products/')
                ? asset($prodPath)
                : \Storage::url($prodPath);
        @endphp

        <div class="product__info">
            <div class="product__image--container {{ ($transaction->product->sold() ?? false) ? 'sold' : '' }}">
                <img src="{{ $productImg }}" alt="商品画像" class="product__image">
            </div>
            <div class="product__detail">
                <h3>{{ $transaction->product->name }}</h3>
                <p>¥{{ number_format($transaction->product->price) }}</p>
            </div>
        </div>

        {{-- チャットメッセージ一覧 --}}
        <div class="chat__messages">
            @foreach($transaction->messages as $message)
                <div class="chat__message {{ $message->user_id === $user->id ? 'my-message' : 'other-message' }}">
                    <div class="chat__user">{{ $message->user->name }}</div>
                    <div class="chat__content">{{ $message->content }}</div>

                    @if($message->image_path)
                        <img src="{{ Storage::url($message->image_path) }}" alt="添付画像" class="chat__image">
                    @endif

                    @if($message->user_id === $user->id)
                        <div class="chat__actions">
                            <a href="{{ route('messages.edit', $message->id) }}">編集</a> |
                            <form action="{{ route('messages.destroy', $message->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- 評価モーダル --}}
        @if($showRatingModal)
        <div class="modal-overlay">
            <div class="rating__modal">
                <p><strong>取引が完了しました。</strong></p>
                <p>今回の取引相手はどうでしたか？</p>
                <form action="{{ route('transactions.rate', $transaction->id) }}" method="POST">
                    @csrf
                    <div class="rating__stars">
                        @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" required>
                            <label for="star{{ $i }}">★</label>
                        @endfor
                    </div>
                    <button type="submit" class="btn--submit-rating">送信する</button>
                </form>
            </div>
        </div>
        @endif

        {{-- チャット送信フォーム --}}
        <form id="chatForm" action="{{ route('messages.store', $transaction->id) }}" method="POST" enctype="multipart/form-data" class="chat__form">
            @csrf

            {{-- ✅ 要件通りのエラーメッセージのみ表示 --}}
            <div class="error-messages" style="color: red; margin-bottom: 10px;">
                @error('content')
                    @if($message === '本文を入力してください' || $message === '本文は400文字以内で入力してください')
                        <div>{{ $message }}</div>
                    @endif
                @enderror
                @error('image')
                    @if($message === '「.png」または「.jpeg」形式でアップロードしてください')
                        <div>{{ $message }}</div>
                    @endif
                @enderror
            </div>

            {{-- ✅ 入力保持（old() + localStorage） --}}
            <textarea id="chatContent" name="content" placeholder="取引メッセージを入力してください">{{ old('content') }}</textarea>

            {{-- ✅ 画像追加（赤文字でクリックできる） --}}
            <label for="chatImage" class="chat__image-btn">画像を追加</label>
            <input id="chatImage" type="file" name="image" accept=".png,.jpeg" style="display: none;">

            {{-- ✅ ボタン文言は「送信する」 --}}
            {{-- ✅ 紙飛行機マーク送信ボタン --}}
        <button type="submit" class="chat__submit-icon" title="送信">
            <img src="{{ asset('img/send-icon.svg') }}" alt="送信" />
        </button>
        </form>
    </main>
</div>

{{-- ✅ 入力保持スクリプト --}}
<script>
(function(){
    const txId = @json($transaction->id);
    const key = 'chat_draft_' + txId;
    const textarea = document.getElementById('chatContent');
    const form = document.getElementById('chatForm');

    // old()がない場合のみ localStorage から復元
    if (!textarea.value) {
        const draft = localStorage.getItem(key);
        if (draft) textarea.value = draft;
    }

    // 入力時に保存
    textarea.addEventListener('input', () => {
        localStorage.setItem(key, textarea.value);
    });

    // 送信時に削除
    form.addEventListener('submit', () => {
        localStorage.removeItem(key);
    });

    // 成功時にも削除
    @if (session('success'))
        localStorage.removeItem(key);
    @endif
})();
</script>
@endsection