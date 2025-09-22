<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '一般ユーザ1',
            'email' => 'general1@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザ2',
            'email' => 'general2@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ];
        User::create($param);

        // ↓↓↓ ここから本番用ユーザーを追加 ↓↓↓

        // CO01〜CO05出品用ユーザーA
        User::create([
            'name' => 'ユーザーA',
            'email' => 'seller1@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ]);

        // CO06〜CO10出品用ユーザーB
        User::create([
            'name' => 'ユーザーB',
            'email' => 'seller2@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ]);

        // 何も紐づけられていないユーザーC
        User::create([
            'name' => 'ユーザーC',
            'email' => 'unused@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ]);
    }
}
