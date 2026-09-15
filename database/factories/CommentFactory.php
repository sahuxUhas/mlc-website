<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Comment> */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'commentable_type' => Post::class,
            'commentable_id'   => Post::factory(),
            'name'             => fake()->name(),
            'email'            => fake()->safeEmail(),
            'comment'          => fake()->paragraph(),
            'status'           => 'pending',
            'ip_address'       => fake()->ipv4(),
        ];
    }

    public function approved(): static { return $this->state(fn () => ['status' => 'approved']); }
    public function spam(): static     { return $this->state(fn () => ['status' => 'spam']); }
}
