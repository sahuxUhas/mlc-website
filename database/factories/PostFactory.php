<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(6),
            'slug'           => fake()->unique()->slug(4),
            'category_id'    => Category::factory(),
            'excerpt'        => fake()->paragraph(),
            'content'        => '<p>'.fake()->paragraphs(4, true).'</p>',
            'status'         => 'published',
            'published_at'   => now(),
            'is_featured'    => false,
            'is_breaking'    => false,
            'allow_comments' => true,
            'views'          => 0,
        ];
    }

    public function draft(): static     { return $this->state(fn () => ['status' => 'draft', 'published_at' => null]); }
    public function pending(): static   { return $this->state(fn () => ['status' => 'pending', 'published_at' => null]); }
    public function published(): static { return $this->state(fn () => ['status' => 'published', 'published_at' => now()]); }
    public function archived(): static  { return $this->state(fn () => ['status' => 'archived']); }
    public function featured(): static  { return $this->state(fn () => ['is_featured' => true]); }
    public function breaking(): static  { return $this->state(fn () => ['is_breaking' => true]); }
}
