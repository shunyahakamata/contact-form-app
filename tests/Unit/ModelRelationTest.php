<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_contacts(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test1@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'test2@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'detail' => 'テスト2',
        ]);

        $this->assertCount(2, $category->contacts);
    }

    public function test_contact_belongs_to_category(): void
    {
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
            'detail' => 'テスト',
        ]);

        $this->assertTrue($contact->category->is($category));
    }

    public function test_contact_can_sync_many_tags(): void
    {
        $category = Category::create([
            'content' => 'その他',
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
            'detail' => 'テスト',
        ]);

        $tag1 = Tag::create([
            'name' => '質問',
        ]);

        $tag2 = Tag::create([
            'name' => '要望',
        ]);

        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
    }

    public function test_tag_belongs_to_many_contacts(): void
    {
        $category = Category::create([
            'content' => 'ショップへのお問い合わせ',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test1@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト1',
        ]);

        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'test2@example.com',
            'tel' => '08012345678',
            'address' => '大阪府',
            'building' => null,
            'detail' => 'テスト2',
        ]);

        $tag->contacts()->attach([
            $contact1->id,
            $contact2->id,
        ]);

        $this->assertCount(2, $tag->contacts);
        $this->assertTrue($tag->contacts->contains($contact1));
        $this->assertTrue($tag->contacts->contains($contact2));
    }
}