<?php

use App\Enums\Diets;
use App\Enums\RecipeTypes;
use App\Models\Recipe;
use App\Models\User;

it('denies userIndex recipes page if banned user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('my-recipes.list'));

    $response->assertStatus(403);
});

it('denies userIndex recipes page if not authentificated', function () {
    $recipe = Recipe::factory()->create();

    $response = $this->get(route('my-recipes.list'));

    $response->assertStatus(403);
});

it('denies userIndex recipes page if rules are not respected', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);


    $response = $this->actingAs($user)->get(
        route('my-recipes.list', [
            'type' => 'test',
            'diet' => 'test',
        ])
    );

    $response->assertStatus(302)
             ->assertSessionHasErrors(['type', 'diet']);
});


it('shows recipes of the user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    // Create some recipes
    $recipes = Recipe::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('my-recipes.list'));

    $response->assertOk()
             ->assertViewIs('myrecipes')
             ->assertViewHas('recipes')
             ->assertViewHas(['recipes', 'total', 'types', 'diet', 'type', 'search', 'types']);
//    Expect having  recipes
    expect($response['recipes'])->count(5);
});


it('shows recipes of user with differents parameters', function() {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $expectedRecipeType = fake()->randomElement(RecipeTypes::allValues());
    $expectedRecipeDiet = fake()->randomElement(Diets::allValues());

    // Create some recipes
    $expectedRecipe = Recipe::factory()->create(['user_id' => $user->id, 'recipe_type' => $expectedRecipeType, 'diets' => [$expectedRecipeDiet], 'name' => 'test']);

    //Create some recipes with different parameters
    $recipes = Recipe::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(
        route('my-recipes.list', [
            'type' => $expectedRecipeType,
            'diet' => $expectedRecipeDiet,
            'name' => 'test',
        ])
    );

    $response->assertOk()
             ->assertViewIs('myrecipes')
             ->assertViewHas('recipes')
             ->assertViewHas(['recipes', 'total', 'types', 'diet', 'type', 'search', 'types']);

    expect($response['recipes'])->count(1);
});
