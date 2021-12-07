<?php

namespace Bit\Translatable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Bit\Translatable\Tests\Fixtures\Post;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $title = $this->faker->sentence(),
            'slug' => Str::slug($title),
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return Factory
     */
    public function translatedVietnamese(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'title:vi' => $title = $this->faker->sentence(),
                'slug:vi' => Str::slug($title),
            ];
        });
    }
}
