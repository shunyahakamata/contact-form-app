<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $caregoris = [
            '商品のお届けについて',
            '商品の交換について',
            '商品トラブル',
            'ショップへお問い合わせ',
            'その他',
        ];

        foreach ($caregoris as $category){
            Category::create([
                'content' => $category,
            ]);
        }
    }
}
