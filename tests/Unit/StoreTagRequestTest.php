<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_tag_name_passes_validation(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => '質問'],
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_empty_tag_name_fails_validation(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => ''],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_tag_name_over_50_characters_fails_validation(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => str_repeat('あ', 51)],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
    }

    public function test_duplicate_tag_name_fails_validation(): void
    {
        Tag::create([
            'name' => '質問',
        ]);

        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => '質問'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
