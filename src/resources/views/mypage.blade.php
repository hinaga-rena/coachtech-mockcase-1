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
                    <img class="user__icon" src="{{ \Storage::url($user->profile->img_url) }}" alt="">
                @else
                    <img id="myImage" class="user__icon" src="{{ asset('img/icon.png') }}" alt="">
                @endif
            </div>
            <p class="user__name">{{ $user->name }}</p>

            {{-- 🔽 平均評価の表示 --}}
            @if ($user->average_rating)
                <div class="user__rating">
                    <span>平均評価:</span>
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $user->average_rating)
                            ★
                        @else
                            ☆
                        @endif
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
            <li><a href="/mypage?page=transactions">取引中の商品</a></li>
        </ul>
    </div>

    {{-- ▼ 商品表示エリア --}}
    <div class="items">
        @if(request()->get('page') === 'transactions')
            {{-- 🔽 取引中商品表示 --}}
            @foreach ($transactions as $transaction)
                <div class="item" style="position: relative;">
                    <a href="{{ route('transactions.show', $transaction->id) }}">
                        <div class="item__img--container">
                            <img src="{{ \Storage::url($transaction->product->img_url) }}" class="item__img" alt="商品画像">
                        </div>
                        @if($transaction->messages->count() > 0)
                            <span class="badge" style="
                                position: absolute;
                                top: 5px;
                                left: 5px;
                                background: red;
                                color: white;
                                border-radius: 50%;
                                padding: 4px 7px;
                                font-size: 12px;">
                                {{ $transaction->messages->count() }}
                            </span>
                        @endif
                        <p class="item__name">{{ $transaction->product->name }}</p>
                    </a>
                </div>
            @endforeach

        @else
            {{-- 🔽 出品/購入商品表示（既存） --}}
            @foreach ($items as $item)
                <div class="item">
                    <a href="/item/{{ $item->id }}">
                        <div class="item__img--container {{ $item->sold() ? 'sold' : '' }}">
                            <img src="{{ \Storage::url($item->img_url) }}" class="item__img" alt="商品画像">
                        </div>
                        <p class="item__name">{{ $item->name }}</p>
                    </a>
                </div>
            @endforeach
        @endif
    </div>

</div>
@endsection