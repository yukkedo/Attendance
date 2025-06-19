<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GeneralTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            '山田 太郎',
            '佐藤 花子',
            '鈴木 次郎',
            '高橋 恵',
            '田中 一郎',
        ];

        foreach ($names as $index => $name) {
            DB::table('users')->insert(
                [
                    'name' => $name,
                    'email' => 'user' . ($index + 1) . '@example.com',
                    'password' => Hash::make('user12345'),
                ]
            );
        }
    }
}
