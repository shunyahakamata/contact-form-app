<?php

namespace Tests\Unit;

use App\Http\Requests\Api\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreApiContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_data_passes_validation(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $request = new StoreContactRequest;

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_data_fails_validation(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([
            'first_name' => '',
            'last_name' => '',
            'gender' => 9,
            'email' => 'invalid-email',
            'tel' => 'abc',
            'address' => '',
            'category_id' => 999999,
            'detail' => '',
            'tag_ids' => [999999],
        ], $request->rules());

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('last_name', $errors);
        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('tel', $errors);
        $this->assertArrayHasKey('address', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('detail', $errors);
        $this->assertArrayHasKey('tag_ids.0', $errors);
    }
}
