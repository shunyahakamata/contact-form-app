<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_admin(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    public function test_admin_search_filters_contacts(): void
    {
        $user = User::factory()->create();

        $category1 = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $category2 = Category::create([
            'content' => '商品トラブル',
        ]);

        Contact::create([
            'category_id' => $category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト1',
        ]);

        Contact::create([
            'category_id' => $category2->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'detail' => 'テスト2',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?keyword=山田&gender=1&category_id='.$category1->id);

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_admin_contacts_are_paginated_by_seven(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => 'その他',
        ]);

        for ($i = 1; $i <= 8; $i++) {
            Contact::create([
                'category_id' => $category->id,
                'first_name' => '太郎'.$i,
                'last_name' => '山田',
                'gender' => 1,
                'email' => "test{$i}@example.com",
                'tel' => '09012345678',
                'address' => '東京都',
                'building' => null,
                'detail' => 'テスト'.$i,
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $contacts = $response->viewData('contacts');

        $this->assertSame(7, $contacts->perPage());
        $this->assertCount(7, $contacts->items());
    }

    public function test_contact_detail_is_displayed(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品トラブル',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'お問い合わせ詳細テスト',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertSee('お問い合わせ詳細テスト');
        $response->assertSee($category->content);
    }

    public function test_contact_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => 'その他',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'delete@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => '削除テスト',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_authenticated_user_can_manage_tags(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/admin/tags', [
                'name' => '新規タグ',
            ]);

        $response->assertRedirect('/admin');

        $tag = Tag::where('name', '新規タグ')->firstOrFail();

        $response = $this
            ->actingAs($user)
            ->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');

        $response = $this
            ->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [
                'name' => '更新タグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新タグ',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_guest_cannot_manage_tags(): void
    {
        $tag = Tag::create([
            'name' => '質問',
        ]);

        $this->post('/admin/tags', [
            'name' => '新規タグ',
        ])->assertRedirect('/login');

        $this->get("/admin/tags/{$tag->id}/edit")
            ->assertRedirect('/login');

        $this->put("/admin/tags/{$tag->id}", [
            'name' => '更新タグ',
        ])->assertRedirect('/login');

        $this->delete("/admin/tags/{$tag->id}")
            ->assertRedirect('/login');
    }
}