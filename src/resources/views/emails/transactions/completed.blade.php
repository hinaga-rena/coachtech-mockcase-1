@component('mail::message')
# 取引完了のお知らせ

{{ $transaction->buyer->name }} さんとの取引が完了しました。
商品名: **{{ $transaction->product->name }}**

取引チャット画面から詳細を確認できます。

@component('mail::button', ['url' => route('transactions.show', $transaction->id)])
取引画面を開く
@endcomponent

@endcomponent