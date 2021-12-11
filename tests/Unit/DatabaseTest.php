<?php

namespace Bit\Translatable\Tests\Unit;

use Bit\Translatable\Tests\Fixtures\Post;
use Bit\Translatable\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_expect_database_when_create_a_model_with_default_locale()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        $post = Post::factory()->create();

        $this->assertDatabaseCount('posts', 1)
            ->assertDatabaseCount('post_translations', 1)
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'locale' => $post->currentLocale(),
            ]);
    }

    public function test_expect_database_when_make_a_new_translation_with_specified_locale()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        $post = Post::factory()->translated('vi')->create();

        $this->assertDatabaseCount('posts', 1)
            ->assertDatabaseCount('post_translations', 2)
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'locale' => $post->currentLocale(),
            ])
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'locale' => 'vi',
            ]);
    }

    public function test_expect_database_when_update_the_translation_with_default_locale()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
       
        $post = Post::factory()->translated('vi')->create();
        $post->title = 'Update';
        $post->save();
        $post = $post->translated()->first();

        $this->assertDatabaseCount('posts', 1)
            ->assertDatabaseCount('post_translations', 2)
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'title' => $post->title,
                'locale' => $post->currentLocale(),
            ]);
    }

    public function test_expect_database_when_update_the_translation_with_specified_locale()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        $locale = 'vi';
        $post = Post::factory()->translated('vi')->create();
        $post->translation('vi')->fill(['title' => 'Update']);
        $post->save();
        $translatedPost = $post->translated($locale)->first();

        $this->assertDatabaseCount('posts', 1)
            ->assertDatabaseCount('post_translations', 2)
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'title' => $translatedPost->title,
                'locale' => $locale,
            ]);
    }
}
