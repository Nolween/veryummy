<?php

use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\User;

it('denies access to user admin recipes page with reports if user is not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('admin-recipes.index', ['type' => 0]));

    $response->assertStatus(403);
});

it('denies access when rules are not respected', function() {
   $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

   $response = $this->actingAs($user)->get(route('admin-recipes.index', ['type' => 999]));

    $response->assertStatus(404);

});


it('access to admin accepted recipes list page with reports', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    // Create some recipes
    $recipes = Recipe::factory()->count(10)->create(['is_accepted' => 1]);

    foreach ($recipes as $recipe) {
        RecipeOpinion::factory()->create(['recipe_id' => $recipe->id, 'is_reported' => 1]);
    }

    $response = $this->actingAs($user)->get(route('admin-recipes.index', ['type' => 1]));

    $response->assertStatus(200)
             ->assertViewIs('adminrecipeslist')
             ->assertViewHas(['recipes', 'total', 'search','typeList']);
    // Assert that the 10 recipes are displayed
    expect($response->viewData('recipes')->count())->toBe(10);

});


it('access to admin waiting for validation recipes list page', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    // Create some recipes
    $recipes = Recipe::factory()->count(10)->create(['is_accepted' => null]);

    $response = $this->actingAs($user)->get(route('admin-recipes.index', ['type' => 0]));

    $response->assertStatus(200)
             ->assertViewIs('adminrecipeslist')
             ->assertViewHas(['recipes', 'total', 'search','typeList']);
    // Assert that the 10 recipes are displayed
    expect($response->viewData('recipes')->count())->toBe(10);

});

it('access to admin validated recipes list page', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    // Create some recipes
    $recipes = Recipe::factory()->count(10)->create(['is_accepted' => 1]);

    $response = $this->actingAs($user)->get(route('admin-recipes.index', ['type' => 2]));

    $response->assertStatus(200)
             ->assertViewIs('adminrecipeslist')
             ->assertViewHas(['recipes', 'total', 'search','typeList']);
    // Assert that the 10 recipes are displayed
    expect($response->viewData('recipes')->count())->toBe(10);

});


it('access to admin refused recipes list page', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    // Create some recipes
    $recipes = Recipe::factory()->count(10)->create(['is_accepted' => 0]);

    $response = $this->actingAs($user)->get(route('admin-recipes.index', ['type' => 3]));

    $response->assertStatus(200)
             ->assertViewIs('adminrecipeslist')
             ->assertViewHas(['recipes', 'total', 'search','typeList']);
    // Assert that the 10 recipes are displayed
    expect($response->viewData('recipes')->count())->toBe(10);

});
