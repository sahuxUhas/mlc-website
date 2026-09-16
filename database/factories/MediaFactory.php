<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::random(12).'.jpg';

        return [
            'file_name'  => $name,
            'path'       => 'uploads/general/'.$name,
            'type'       => 'image',
            'mime_type'  => 'image/jpeg',
            'size'       => random_int(20000, 400000),
            'folder'     => 'general',
            'alt_text'   => fake()->sentence(3),
        ];
    }
}
