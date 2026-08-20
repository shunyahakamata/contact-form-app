<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_valid_data_passes_validation(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストマンション101',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'お問い合わせ内容です',
        ];

        $request = new StoreContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }
    
    public function test_invalid_tel_fails_validation(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都渋谷区',
            'building' => 'テストマンション101',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'お問い合わせ内容です',
        ];

        $request = new StoreContactRequest();

        $validator = Validator::make(
            $data,
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tel'));
    }
}
