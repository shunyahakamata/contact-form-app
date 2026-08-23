<?php

namespace Tests\Unit;

use App\Http\Requests\Api\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexApiContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_filters_pass_validation(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $request = new IndexContactRequest;

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-08-23',
            'per_page' => 10,
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_filters_fail_validation(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make([
            'gender' => 9,
            'category_id' => 999999,
            'date' => 'invalid-date',
            'per_page' => 0,
        ], $request->rules());

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }
}
