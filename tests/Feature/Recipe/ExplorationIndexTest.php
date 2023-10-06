<?php

use App\Enums\Diets;
use App\Enums\RecipeTypes;
use App\Models\Recipe;

it('show exploration page with recipes without filters', function () {
    // Create some recipes
    $recipes = Recipe::factory()->count(10)->create(['is_accepted' => 1]);

    $response = $this->get(route('exploration.index'));

    $response->assertStatus(200);
    $response->assertViewIs('exploration');
    $response->assertViewHas([
        'total',
        'recipes',
        'types',
        'search',
        'diet',
        'type'
    ]);
    $response->assertSee($recipes->first()->name);
});

it('show exploration page with rules non respected', function() {

    $response = $this->get(route('exploration.index', [
        'name' => 'a',
        'type' => 999,
        'diet' => 999
    ]));

    $response->assertStatus(302)
             ->assertSessionHasErrors(['type', 'diet']);
});

it('show exploration page with filters', function() {
    $randomDiet = fake()->randomElement(Diets::allValues());
    $randomType = fake()->randomElement(RecipeTypes::allValues());


    //    Create Recipes
    $recipe = Recipe::factory()->create(['is_accepted' => 1, 'diets' => json_encode([$randomDiet]), 'recipe_type' => $randomType]);

    $response = $this->get(route('exploration.index', [
        'name' => $recipe->name,
        'type' => $randomType,
        'diet' => $randomDiet
    ]));

    $response->assertStatus(200);
    $response->assertViewIs('exploration');
    $response->assertViewHas([
        'total',
        'recipes',
        'types',
        'search',
        'diet',
        'type'
    ]);
    $response->assertSee($recipe->name);

    expect($response->viewData('recipes')->count())->toBe(1);
});
