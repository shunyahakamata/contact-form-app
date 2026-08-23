<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_filters_pass_validation(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $request = new ExportContactRequest;

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-08-23',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_gender_and_category_fail_validation(): void
    {
        $request = new ExportContactRequest;

        $validator = Validator::make([
            'gender' => 9,
            'category_id' => 999999,
        ], $request->rules());

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('category_id', $errors);
    }
}
