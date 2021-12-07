<?php

namespace Bit\Translatable\Tests\Unit;

use Bit\Translatable\Tests\Fixtures\Post;
use Bit\Translatable\Tests\TestCase;
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
                'locale' => App::currentLocale(),
            ]);
    }

    public function test_expect_database_when_make_a_new_translation_with_specified_locale()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        $post = Post::factory()->translatedVietnamese()->create();


        $this->assertDatabaseCount('posts', 1)
            ->assertDatabaseCount('post_translations', 2)
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'locale' => App::currentLocale(),
            ])
            ->assertDatabaseHas('post_translations', [
                'post_id' => $post->getKey(),
                'locale' => 'vi',
            ]);
    }
}
