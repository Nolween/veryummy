<?php

namespace Database\Factories;

use App\Enums\Diets;
use App\Enums\RecipeTypes;
use App\Helpers\ImageTransformation;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
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
            exit('Failed to convert image to true color.');
        }
        imageavif($gdImage, storage_path('app/public/img/full/' . $filename . '.avif'));
        $resizeSmallImg = ImageTransformation::image_resize($gdImage, 240, 180);
        // If thumbnail folder does not exist, create it
        if (!file_exists(storage_path('app/public/img/thumbnail'))) {
            mkdir(storage_path('app/public/img/thumbnail'));
        }
        imageavif($resizeSmallImg, storage_path('app/public/img/thumbnail/' . $filename . '.avif'));

        imagedestroy($gdImage);
        imagedestroy($resizeSmallImg);
        // On efface le png original
        unlink(storage_path('app/public/img/full/' . $filename . '.png'));

        $user = User::inRandomOrder()->first() ?? User::factory()->create();


        return [
            'user_id'      => $user->id,
            'recipe_type'  => fake()->randomElement(RecipeTypes::allValues()),
            'name'         => fake()->sentence(),
            'image'        => $filename . '.avif',
            'making_time'  => rand(1, 100),
            'cooking_time' => rand(1, 180),
            'servings'     => rand(1, 20),
            'score'        => fake()->randomFloat(2, 1, 5),
            'diets'        => [],
            'created_at'   => now(),
            'updated_at'   => now(),
            'is_accepted'  => fake()->boolean(90)
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Recipe $recipe) {
            for ($i = 0; $i < rand(3, 10); $i++) {
                RecipeIngredients::factory()->create([
                                                         'recipe_id' => $recipe->id,
                                                     ]);
            }

            // Defining diets of recipe
            $diets = Diets::allValues();
            // Définition des diets selon les ingrédients créés
            $recipeIngredients = $recipe->ingredients;
            foreach ($recipeIngredients as $recipeIngredient) {
                $ingredient = $recipeIngredient->ingredient;
                $ingredientDiets = $ingredient->diets;
                foreach ($diets as $diet) {
                    //    Si la diet n'est pas présent dans l'ingrédient, on la retire
                    if (!in_array($diet, $ingredientDiets)) {
                        unset($diets[array_search($diet, $diets)]);
                    }
                }
                if ($diets === []) {
                    break;
                }
            }
            $recipe->update([
                                'diets' => array_values($diets),
                            ]);
        });
    }
}
