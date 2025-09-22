<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| アプリの全ルート定義。
| 認証済みのユーザー向け機能は middleware(['auth','verified']) にまとめる。
|
*/

// トップ・商品関連
Route::get('/', [ItemController::class, 'index'])->name('items.list');        // 商品一覧
Route::get('/item/{item}', [ItemController::class, 'detail'])->name('item.detail'); // 商品詳細
Route::get('/item', [ItemController::class, 'search'])->name('items.search'); // 商品検索

// 認証済みユーザーのみ
Route::middleware(['auth', 'verified'])->group(function () {

    // ▼ 出品・購入関連
    Route::get('/sell', [ItemController::class, 'sellView'])->name('items.sell.view');
    Route::post('/sell', [ItemController::class, 'sellCreate'])->name('items.sell.create');

    // いいね・コメント
    Route::post('/item/like/{item_id}', [LikeController::class, 'create'])->name('items.like');
    Route::post('/item/unlike/{item_id}', [LikeController::class, 'destroy'])->name('items.unlike');
    Route::post('/item/comment/{item_id}', [CommentController::class, 'create'])->name('items.comment');

    // ▼ 購入（purchase ミドルウェア付き）
    Route::prefix('purchase/{item_id}')->middleware('purchase')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('purchase.index');
        Route::post('/', [PurchaseController::class, 'purchase'])->name('purchase.execute');
        Route::get('/success', [PurchaseController::class, 'success'])->name('purchase.success');
        Route::get('/address', [PurchaseController::class, 'address'])->name('purchase.address');
        Route::post('/address', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    });

    // ▼ マイページ
    Route::get('/mypage', [UserController::class, 'mypage'])->name('mypage');
    Route::get('/mypage/profile', [UserController::class, 'profile'])->name('mypage.profile');
    Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('mypage.profile.update');

    // ▼ 取引チャット
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/complete', [TransactionController::class, 'complete'])->name('transactions.complete');
    Route::post('/transactions/{transaction}/rate', [TransactionController::class, 'rate'])->name('transactions.rate');

    // ▼ チャット投稿・編集・削除
    Route::post('/transactions/{transaction}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}/edit', [MessageController::class, 'edit'])->name('messages.edit');
    Route::patch('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
});

// 認証関連（Fortify）
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('email')->name('login');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

// メール認証関連
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    // Fortify の認証フローをカスタマイズしているため session 経由
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/mypage/profile');
})->name('verification.verify');
