<?php

namespace Database\Factories;

use App\Helpers\ImageTransformation;
use App\Models\RecipeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $filename = fake()->randomNumber(6) . '-' . Str::slug(fake()->words(4, true), '-');

        // Dernier id de question
        // Génération d'une image PNG (AVIF Pas disponible) dans le dossier big
        $randomHex = substr(fake()->hexColor(), 1);
        Storage::disk('public')->put(
            'img/full/' . $filename . '.png',
            file_get_contents('https://dummyimage.com/640x480/' . $randomHex . '.png?text=' . $filename)
        );
        // Transformation en avif
        $gdImage = imagecreatefrompng(storage_path('app/public/img/full/' . $filename . '.png'));
        if (!imagepalettetotruecolor($gdImage)) {
            // Handle failure
            die('Failed to convert image to true color.');
        }
        imageavif($gdImage, storage_path('app/public/img/full/' . $filename . '.avif'));
        $resizeSmallImg = ImageTransformation::image_resize($gdImage, 240, 180);
        imageavif($resizeSmallImg, storage_path('app/public/img/thumbnail/' . $filename . '.avif'));

        imagedestroy($gdImage);
        imagedestroy($resizeSmallImg);
        // On efface le png original
        unlink(storage_path('app/public/img/full/' . $filename . '.png'));

        return [
            'user_id'                => User::inRandomOrder()->first()->id,
            'recipe_type_id'         => RecipeType::inRandomOrder()->first()->id,
            'name'                   => fake()->sentence(),
            'image'                  => $filename . '.avif',
            'making_time'            => rand(1, 100),
            'cooking_time'           => rand(1, 180),
            'servings'               => rand(1, 20),
            'score'                  => fake()->randomFloat(2, 1, 5),
            'is_accepted'            => fake()->boolean(90),
            'vegan_compatible'       => fake()->boolean(60),
            'vegetarian_compatible'  => fake()->boolean(80),
            'gluten_free_compatible' => fake()->boolean(90),
            'halal_compatible'       => fake()->boolean(80),
            'kosher_compatible'      => fake()->boolean(60),
            'created_at'             => now(),
            'updated_at'             => now(),
        ];
    }
}
