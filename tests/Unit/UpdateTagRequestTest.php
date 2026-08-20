<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_tag_name_can_be_kept(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        Route::get('/test/tags/{tag}', function () {
            return null;
        });

        $request = UpdateTagRequest::create(
            "/test/tags/{$tag->id}",
            'GET'
        );

        $request->setRouteResolver(function () use ($tag) {
            return tap(Route::getRoutes()->match($request = Request::create("/test/tags/{$tag->id}", 'GET')), function ($route) use ($tag) {
                $route->setParameter('tag', $tag);
            });
        });

        $validator = Validator::make(
            ['name' => '質問'],
            $request->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_duplicate_other_tag_name_fails_validation(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        Tag::create([
            'name' => '要望',
        ]);

        Route::get('/test/tags/{tag}', function () {
            return null;
        });

        $request = UpdateTagRequest::create(
            "/test/tags/{$tag->id}",
            'GET'
        );

        $request->setRouteResolver(function () use ($tag) {
            return tap(Route::getRoutes()->match($request = Request::create("/test/tags/{$tag->id}", 'GET')), function ($route) use ($tag) {
                $route->setParameter('tag', $tag);
            });
        });

        $validator = Validator::make(
            ['name' => '要望'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }
}
