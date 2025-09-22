<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => 1,
            'postcode' => '1080014',
            'address' => '東京都港区芝5丁目29-20610',
            'building' => 'クロスオフィス三田',
        ];
        Profile::create($param);

        $param = [
            'user_id' => 2,
            'postcode' => '1080014',
            'address' => '東京都港区芝5丁目29-20610',
            'building' => 'クロスオフィス三田',
        ];
        Profile::create($param);

        // ユーザーA（seller1@example.com）
        $param = [
        'user_id' => 3,
        'postcode' => '1500001',
        'address' => '東京都渋谷区神宮前1-1-1',
        'building' => 'ハピネスタワー301',
        ];
        Profile::create($param);

        // ユーザーB（seller2@example.com）
        $param = [
        'user_id' => 4,
        'postcode' => '5300001',
        'address' => '大阪府大阪市北区梅田1-1-1',
        'building' => 'スマイルビル502',
        ];
        Profile::create($param);

        // ユーザーC（unused@example.com）
        $param = [
        'user_id' => 5,
        'postcode' => '4600001',
        'address' => '愛知県名古屋市中区栄1-1-1',
        'building' => 'ラッキーハウス707',
        ];
        Profile::create($param);
    }
}
