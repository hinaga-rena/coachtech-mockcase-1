<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use App\Models\User;
use App\Models\Item;
use App\Models\SoldItem;
use App\Models\Transaction;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function profile(){

        $profile = Profile::where('user_id', Auth::id())->first();

        return view('profile',compact('profile'));
    }

    public function updateProfile(ProfileRequest $request){

        $img = $request->file('img_url');
        if (isset($img)){
            $img_url = Storage::disk('local')->put('public/img', $img);
        }else{
            $img_url = '';
        }

        $profile = Profile::where('user_id', Auth::id())->first();
        if ($profile){
            $profile->update([
                'user_id' => Auth::id(),
                'img_url' => $img_url,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building
            ]);
        }else{
            Profile::create([
                'user_id' => Auth::id(),
                'img_url' => $img_url,
                'postcode' => $request->postcode,
                'address' => $request->address,
                'building' => $request->building
            ]);
        }

        User::find(Auth::id())->update([
            'name' => $request->name
        ]);

        return redirect('/');
    }

    public function mypage(Request $request)
    {
        $user = User::find(Auth::id());
        $page = $request->page;

        $items         = collect();   // 出品 or 購入
        $transactions  = collect();   // 取引中
        $unreadCount   = 0;           // タブ横の未読合計

        // 自分が関係する未完了の取引（共通ベース）
        $baseTx = Transaction::query()
            ->where(function ($q) use ($user) {
                $q->where('buyer_id',  $user->id)
                ->orWhere('seller_id', $user->id);
            })
            ->where('is_completed', false);

        if ($page === 'buy') {
            $items = SoldItem::where('user_id', $user->id)->get()->map->item;

            // タブ用未読合計だけ取得
            $unreadCount = (int) (clone $baseTx)
                ->withCount(['messages as unread_count' => function ($q) use ($user) {
                    $q->whereNull('read_at')->where('user_id', '!=', $user->id);
                }])
                ->get()
                ->sum('unread_count');

        } elseif ($page === 'transactions') {
            // 新着メッセージ時刻で降順並び替え + 未読数も一緒に取得
            $transactions = (clone $baseTx)
                ->with('product')
                ->withMax('messages', 'created_at') // messages_max_created_at が付与
                ->withCount(['messages as unread_count' => function ($q) use ($user) {
                    $q->whereNull('read_at')->where('user_id', '!=', $user->id);
                }])
                ->orderByDesc('messages_max_created_at')
                ->get();

            // タブ用未読合計（一覧の各取引の未読数合計）
            $unreadCount = (int) $transactions->sum('unread_count');

        } else { // 出品した商品
            $items = Item::where('user_id', $user->id)->get();

            // タブ用未読合計だけ取得
            $unreadCount = (int) (clone $baseTx)
                ->withCount(['messages as unread_count' => function ($q) use ($user) {
                    $q->whereNull('read_at')->where('user_id', '!=', $user->id);
                }])
                ->get()
                ->sum('unread_count');
        }

        return view('mypage', compact('user', 'items', 'transactions', 'unreadCount'));
    }
}