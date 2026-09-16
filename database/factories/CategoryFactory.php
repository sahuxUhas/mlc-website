<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => fake()->unique()->word(),
            'slug'         => fake()->unique()->slug(2),
            'icon'         => 'ph-newspaper',
            'color'        => '#E21D2B',
            'is_visible'   => true,
            'show_on_home' => true,
            'show_in_menu' => true,
            'sort_order'   => 0,
        ];
    }
}
