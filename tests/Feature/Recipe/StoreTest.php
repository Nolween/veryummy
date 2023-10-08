<?php

use App\Enums\RecipeTypes;
use App\Enums\Units;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('denies recipe store if user is not authentificated', function () {
    $response = $this->put(route('my-recipes.store'));

    $response->assertStatus(403);
});

it('denies recipe store if user is banned', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    $response = $this->actingAs($user)->put(route('my-recipes.store'));

    $response->assertStatus(403);
});

it('denies recipe store if user does not respect rules', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->put(route('my-recipes.store'), [
        'nom'             => 'a',
        'photoInput'      => 'test',
        'preparation'     => 'test',
        'cuisson'         => 'test',
        'parts'           => 'test',
        'stepCount'       => 'test',
        'type'            => 'test',
        'ingredientCount' => 'test',
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(
                 ['nom', 'photoInput', 'preparation', 'cuisson', 'parts', 'stepCount', 'type', 'ingredientCount']
             );
});

it('stores recipes', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    // Create some ingredients
    $ingredients = Ingredient::factory()->count(5)->create();

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
        'nom'             => fake()->word(),
        'photoInput'      => UploadedFile::fake()->image('photo.jpg'),
        'preparation'     => rand(10, 30),
        'cuisson'         => rand(10, 30),
        'parts'           => rand(1, 5),
        'stepCount'       => count($steps),
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredients->count(),
        'steps'           => $steps,
        'ingredients'     => $ingredientsArray,
    ];

    $response = $this->actingAs($user)->put(route('my-recipes.store'), $form);

    $response->assertStatus(302)
             ->assertSessionHas('newSuccess');
    expect($user->recipes()->count())->toBe(1);
});
