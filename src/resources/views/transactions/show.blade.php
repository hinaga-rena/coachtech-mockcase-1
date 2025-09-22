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

        {{-- 商品情報 --}}
        <div class="product__info">
            <img src="{{ Storage::url($transaction->product->img_url) }}" alt="商品画像" class="product__image">
            <div class="product__detail">
                <h3>{{ $transaction->product->name }}</h3>
                <p>¥{{ number_format($transaction->product->price) }}</p>
            </div>
        </div>

        {{-- チャットエリア --}}
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
                        {{-- 編集リンク --}}
                        <a href="{{ route('messages.edit', $message->id) }}">編集</a> |

                        {{-- 削除フォーム --}}
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
        <form action="{{ route('messages.store', $transaction->id) }}" method="POST" enctype="multipart/form-data" class="chat__form">
            @csrf

            {{-- エラーメッセージ表示 --}}
            @if ($errors->any())
                <div class="error-messages" style="color: red; margin-bottom: 10px;">
                    <ul>
                        @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <textarea name="content" placeholder="取引メッセージを入力してください">{{ old('content') }}</textarea>
            <input type="file" name="image">
            <button type="submit" class="chat__submit-btn">画像を追加して送信</button>
        </form>
    </main>

</div>
@endsection