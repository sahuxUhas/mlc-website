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
            // ডাটাবেস/মডেল অনুযায়ী কলামের নাম guest_name ও body
            'guest_name'       => fake()->name(),
            'guest_email'      => fake()->safeEmail(),
            'body'             => fake()->paragraph(),
            'status'           => 'pending',
            'ip_address'       => fake()->ipv4(),
        ];
    }

    public function approved(): static { return $this->state(fn () => ['status' => 'approved']); }
    public function spam(): static     { return $this->state(fn () => ['status' => 'spam']); }
}
