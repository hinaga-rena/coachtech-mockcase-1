@extends('layouts.default')

@section('title', 'メッセージ編集')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/messages.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="edit-container">
    <h2>メッセージ編集</h2>

    {{-- バリデーションエラー --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="edit-form" action="{{ route('messages.update', $message->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <label for="content">本文</label>
        <textarea id="content" name="content" required>{{ old('content', $message->content) }}</textarea>

        <label for="image">画像（任意）</label>
        @if ($message->image_path)
            <img src="{{ Storage::url($message->image_path) }}" alt="現在の画像" width="150"><br>
        @endif
        <input id="image" type="file" name="image">

        <button type="submit">更新する</button>
    </form>

    <a href="{{ route('transactions.show', $message->transaction_id) }}">← チャットに戻る</a>
</div>
@endsection