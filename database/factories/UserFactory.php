<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'           => fake()->name(),
            'email'          => fake()->unique()->safeEmail(),
            'password'       => static::$password ??= Hash::make('password'),
            'role'           => 'reporter',
            'designation'    => 'নিজেস্ব প্রতিবেদক',
            'is_active'      => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => 'super_admin', 'name' => 'সুপার অ্যাডমিন']);
    }

    public function editor(): static
    {
        return $this->state(fn () => ['role' => 'editor']);
    }

    public function reporter(): static
    {
        return $this->state(fn () => ['role' => 'reporter']);
    }

    public function moderator(): static
    {
        return $this->state(fn () => ['role' => 'moderator']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
