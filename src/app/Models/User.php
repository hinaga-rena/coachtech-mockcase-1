<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Evaluation;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 🔽 プロフィール
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // 🔽 いいね
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // 🔽 コメント
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }  

    // 🔽 出品した商品
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // 🔽 自分が受け取った評価一覧
    public function receivedEvaluations()
    {
        return $this->hasMany(Evaluation::class, 'to_user_id');
    }

    // 🔽 平均評価（小数は四捨五入）
    public function getAverageRatingAttribute()
    {
        $count = $this->receivedEvaluations()->count();
        if ($count === 0) {
            return null; // 評価がまだない
        }

        $avg = $this->receivedEvaluations()->avg('rating');
        return round($avg); // 四捨五入
    }
}