<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_list_api_returns_json(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト',
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    public function test_contact_detail_api_returns_json(): void
    {
        $category = Category::create([
            'content' => '商品トラブル',
        ]);

        $tag = Tag::create([
            'name' => '質問',
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
            'detail' => '詳細テスト',
        ]);

        $contact->tags()->sync([$tag->id]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $contact->id);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'category',
                'tags',
            ],
        ]);
    }

    public function test_nonexistent_contact_returns_404(): void
    {
        $response = $this->getJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }

    public function test_contact_can_be_created_by_api(): void
    {
        $category = Category::create([
            'content' => 'その他',
        ]);

        $tag = Tag::create([
            'name' => '要望',
        ]);

        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'api@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'API作成テスト',
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.email', 'api@example.com');

        $this->assertDatabaseHas('contacts', [
            'email' => 'api@example.com',
        ]);

        $contact = Contact::where('email', 'api@example.com')->firstOrFail();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_invalid_contact_data_returns_422(): void
    {
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '',
            'last_name' => '',
            'gender' => 9,
            'email' => 'invalid',
            'tel' => 'abc',
            'address' => '',
            'category_id' => 999999,
            'detail' => '',
            'tag_ids' => [999999],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
            'tag_ids.0',
        ]);
    }

    public function test_contact_can_be_updated_by_api(): void
    {
        $category = Category::create([
            'content' => 'その他',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => '更新前',
        ]);

        $data = [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'new@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => '更新後',
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'new@example.com',
            'detail' => '更新後',
        ]);
    }

    public function test_nonexistent_contact_update_returns_404(): void
    {
        $category = Category::create([
            'content' => 'その他',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $data = [
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'new@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => '更新後',
        ];

        $response = $this->putJson('/api/v1/contacts/999999', $data);

        $response->assertStatus(404);
    }

    public function test_contact_can_be_deleted_by_api(): void
    {
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

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_nonexistent_contact_delete_returns_404(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }
}
