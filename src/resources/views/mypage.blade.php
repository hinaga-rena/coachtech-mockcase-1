@extends('layouts.default')

@section('title','マイページ')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/index.css') }}">
<link rel="stylesheet" href="{{ asset('/css/mypage.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="container">

    {{-- ▼ ユーザー情報表示 --}}
    <div class="user">
        <div class="user__info">
            <div class="user__img">
                @if (isset($user->profile->img_url))
                    {{-- プロフィール画像は storage 側想定 --}}
                    <img class="user__icon" src="{{ \Storage::url($user->profile->img_url) }}" alt="">
                @else
                    <img id="myImage" class="user__icon" src="{{ asset('img/icon.png') }}" alt="">
                @endif
            </div>
            <p class="user__name">{{ $user->name }}</p>

            {{-- 平均評価 --}}
            @if ($user->average_rating)
                <div class="user__rating">
                    <span>平均評価:</span>
                    @for ($i = 1; $i <= 5; $i++)
                        {{ $i <= $user->average_rating ? '★' : '☆' }}
                    @endfor
                    <span>({{ $user->average_rating }}/5)</span>
                </div>
            @else
                <p class="user__rating">まだ評価はありません</p>
            @endif
        </div>

        <div class="mypage__user--btn">
            <a class="btn2" href="/mypage/profile">プロフィールを編集</a>
        </div>
    </div>

    {{-- ▼ タブ切り替え --}}
    <div class="border">
        <ul class="border__list">
            <li><a href="/mypage?page=sell">出品した商品</a></li>
            <li><a href="/mypage?page=buy">購入した商品</a></li>
            <li>
                <a href="/mypage?page=transactions" class="mp-tab-link">
                    取引中の商品
                    @if($unreadCount > 0)
                        <span class="mp-unread-badge">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    {{-- ▼ 商品表示エリア --}}
<div class="items">
    @if (request()->get('page') === 'transactions')
        {{-- 取引中商品表示 --}}
        @foreach ($transactions as $transaction)
            @php
                $p = $transaction->product->img_url;
                $txImg = \Illuminate\Support\Str::startsWith($p, 'products/')
                    ? asset($p)
                    : \Storage::url($p);

                // コントローラで withCount('unread_count') を付けている想定
                $unread = $transaction->unread_count ?? $transaction->messages->count();
            @endphp
            <div class="item">
                <a href="{{ route('transactions.show', $transaction->id) }}">
                    <div class="item__img--container" style="position: relative;">
                        <img src="{{ $txImg }}" class="item__img" alt="商品画像">

                        {{-- ★ 赤丸バッジ（画像左上） --}}
                        @if ($unread > 0)
                            <span class="mp-badge">{{ $unread }}</span>
                        @endif
                    </div>
                    <p class="item__name">{{ $transaction->product->name }}</p>
                </a>
            </div>
        @endforeach
    @else
        {{-- 出品/購入商品表示 --}}
        @foreach ($items as $item)
            @php
                $u = $item->img_url;
                $itemImg = \Illuminate\Support\Str::startsWith($u, 'products/')
                    ? asset($u)
                    : \Storage::url($u);
            @endphp
            <div class="item">
                <a href="/item/{{ $item->id }}">
                    <div class="item__img--container {{ $item->sold() ? 'sold' : '' }}">
                        <img src="{{ $itemImg }}" class="item__img" alt="商品画像">
                    </div>
                    <p class="item__name">{{ $item->name }}</p>
                </a>
            </div>
        @endforeach
    @endif
</div>


</div>
@endsection