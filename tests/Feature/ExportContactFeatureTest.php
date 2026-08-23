<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_download_csv(): void
    {
        $user = User::factory()->create();

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
            'detail' => 'CSVテスト',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8'
        );
        $response->assertHeader(
            'content-disposition',
            'attachment; filename="contacts.csv"'
        );
    }

    public function test_csv_can_be_filtered(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => '男性のお問い合わせ',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'detail' => '女性のお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?gender=1');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $this->assertStringContainsString('taro@example.com', $content);
        $this->assertStringNotContainsString('hanako@example.com', $content);
    }

    public function test_csv_is_exported_in_latest_order(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $oldContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => '古いお問い合わせ',
        ]);

        $oldContact->created_at = now()->subDay();
        $oldContact->save();

        $newContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'new@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'detail' => '新しいお問い合わせ',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $newPosition = strpos($content, 'new@example.com');
        $oldPosition = strpos($content, 'old@example.com');

        $this->assertNotFalse($newPosition);
        $this->assertNotFalse($oldPosition);
        $this->assertLessThan($oldPosition, $newPosition);
    }

    public function test_guest_cannot_download_csv(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect('/login');
    }
}
