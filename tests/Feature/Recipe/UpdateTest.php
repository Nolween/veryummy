<?php

use App\Enums\RecipeTypes;
use App\Enums\Units;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('denies access to update recipe if not authorized user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('my-recipes.update', ['recipeid' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies access to update recipe if not respecting rules', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('my-recipes.update', ['recipeid' => $recipe->id]), [
        'nom'             => 'a',
        'photoInput'      => 'test',
        'preparation'     => 'test',
        'cuisson'         => 'test',
        'parts'           => 'test',
        'stepCount'       => 'test',
        'type'            => 'test',
        'ingredientCount' => 'test',
        'recipeid'        => $recipe->id
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors([
                                          'nom',
                                          'photoInput',
                                          'preparation',
                                          'cuisson',
                                          'parts',
                                          'stepCount',
                                          'type',
                                          'ingredientCount',
                                      ]);
});

it('denies access to update recipe if not recipe owner', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $otherUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $recipe = Recipe::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->patch(route('my-recipes.update', ['recipeid' => $recipe->id]));

    $response->assertStatus(403);
});

it('updates recipe', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    // Create some ingredients
    $ingredients = Ingredient::factory()->count(5)->create();

    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $steps = [];
    for ($i = 0; $i < rand(1, 5); $i++) {
        $steps[] = fake()->sentence;
    }

    $ingredientsArray = [];
    foreach ($ingredients as $ingredient) {
        $ingredientsArray[] = [
            'ingredientId'       => $ingredient->id,
            'ingredientName'     => fake()->word(),
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 3),
            'stepDescription'    => fake()->sentence,
        ];
    }

    $form = [
        'nom'             => 'test',
        'photoInput'      => UploadedFile::fake()->image('photo.jpg'),
        'preparation'     => rand(10, 30),
        'cuisson'         => rand(10, 30),
        'parts'           => rand(1, 5),
        'stepCount'       => count($steps),
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredients->count(),
        'steps'           => $steps,
        'ingredients'     => $ingredientsArray,
        'recipeid'        => $recipe->id
    ];

    $response = $this->actingAs($user)->patch(route('my-recipes.update', ['recipeid' => $recipe->id]), $form);

    $response->assertStatus(302)->assertSessionHas('updateSuccess');

    expect(Recipe::where('name', 'test')->first())->not()->toBeNull();
});
