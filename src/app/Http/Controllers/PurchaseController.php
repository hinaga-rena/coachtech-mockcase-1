<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\AddressRequest;
use App\Models\Item;
use App\Models\User;
use App\Models\SoldItem;
use App\Models\Profile;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;

class PurchaseController extends Controller
{
    /**
     * 購入確認ページ
     */
    public function index($item_id, Request $request)
    {
        $item = Item::findOrFail($item_id);
        $user = User::findOrFail(Auth::id());
        return view('purchase', compact('item', 'user'));
    }

    /**
     * Checkout セッションを作成して Stripe にリダイレクト
     * 3DS が必要なら自動で認証画面に遷移する
     */
    public function purchase($item_id, Request $request)
    {
        $item = Item::findOrFail($item_id);

        // ここで配送先の最低限のバリデーション（必要に応じて調整）
        $request->validate([
            'destination_postcode'  => ['required', 'string'],
            'destination_address'   => ['required', 'string'],
            'destination_building'  => ['nullable', 'string'],
        ]);

        // 成功後に DB 登録するため配送先を一時保存（URLに出さない）
        Session::put('purchase.shipping', [
            'postcode' => $request->destination_postcode,
            'address'  => $request->destination_address,
            'building' => $request->destination_building,
        ]);

        Stripe::setApiKey(config('stripe.stripe_secret_key'));

        $session = CheckoutSession::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'], // 3DS は自動要求される
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    // Stripe の金額は最小単位（¥1 = 1）
                    'unit_amount' => (int) $item->price,
                ],
                'quantity' => 1,
            ]],
            'customer_email' => Auth::user()->email ?? null,
            'success_url' => route('purchase.success', ['item_id' => $item_id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('purchase.index',  ['item_id' => $item_id]),
        ]);

        return redirect($session->url);
    }

    /**
     * 決済成功後コールバック
     * Checkout セッションを検証し、支払い成功を確認してから DB 登録
     */
    public function success($item_id, Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            throw new Exception('Missing session_id.');
        }

        Stripe::setApiKey(config('stripe.stripe_secret_key'));

        // セッション検証（支払済みかどうか）
        $session = CheckoutSession::retrieve($sessionId);
        if (($session->payment_status ?? null) !== 'paid') {
            throw new Exception('Payment not completed.');
        }

        // 既に売却登録済みなら二重登録を防止
        if (SoldItem::where('item_id', $item_id)->exists()) {
            return redirect('/')->with('flashSuccess', '決済は完了しています。');
        }

        // 住所はセッションから取り出し（無ければユーザープロファイルを利用）
        $shipping = Session::pull('purchase.shipping', []);
        $profile  = Profile::where('user_id', Auth::id())->first();

        $sending_postcode = $shipping['postcode'] ?? ($profile->postcode ?? '');
        $sending_address  = $shipping['address']  ?? ($profile->address ?? '');
        $sending_building = $shipping['building'] ?? ($profile->building ?? null);

        // 決済はすでに完了しているので、ここでは DB 反映のみ（Charges.create は不要！）
        SoldItem::create([
            'user_id'          => Auth::id(),
            'item_id'          => $item_id,
            'sending_postcode' => $sending_postcode,
            'sending_address'  => $sending_address,
            'sending_building' => $sending_building,
        ]);

        return redirect('/')->with('flashSuccess', '決済が完了しました！');
    }

    /**
     * 配送先住所の編集ページ
     */
    public function address($item_id)
    {
        $user = User::findOrFail(Auth::id());
        return view('address', compact('user', 'item_id'));
    }

    /**
     * 配送先住所の更新
     */
    public function updateAddress(AddressRequest $request)
    {
        $user = User::findOrFail(Auth::id());

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'postcode' => $request->postcode,
                'address'  => $request->address,
                'building' => $request->building,
            ]
        );

        return redirect()->route('purchase.index', ['item_id' => $request->item_id]);
    }
}