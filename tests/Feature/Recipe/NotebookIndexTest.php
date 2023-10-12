<?php

use App\Enums\Diets;
use App\Enums\RecipeTypes;
use App\Models\Recipe;
use App\Models\User;

it('denies user to access to notebook page if not authentificated', function () {
    $response = $this->get(route('my-notebook.list'));

    $response->assertStatus(403);
});

it('denies user to access to notebook page if banned', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    $response = $this->actingAs($user)->get(route('my-notebook.list'));

    $response->assertStatus(403);
});

it('denies user to access to notebook page if rules are not respected', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('my-notebook.list', [
        'type' => 'test',
        'diet' => 'test',
    ]));

    $response->assertStatus(302)
        ->assertSessionHasErrors(['type', 'diet']);
});

it('shows recipes of the user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    // Create some recipes
    $recipes = Recipe::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('my-notebook.list'));

    $response->assertOk()
             ->assertViewIs('mynotebook')
             ->assertViewHas(['recipes', 'total', 'types', 'diet', 'type', 'search', 'types']);

    expect($response['recipes'])->count(5);
});

it('shows recipes of user with differents parameters', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $expectedType = fake()->randomElement(RecipeTypes::allValues());
    $expectedDiet = fake()->randomElement(Diets::allValues());

    $expetedRecipe = Recipe::factory()->create(['user_id' => $user->id, 'recipe_type' => $expectedType, 'diets' => $expectedDiet, 'name' => 'test']);

    // Create some recipes
    $recipes = Recipe::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('my-notebook.list', [
        'type' => $expectedType,
        'diet' => $expectedDiet,
        'name' => 'test'

    ]));

    $response->assertOk()
             ->assertViewIs('mynotebook')
             ->assertViewHas(['recipes', 'total', 'types', 'diet', 'type', 'search', 'types']);

    expect($response['recipes'])->count(1);
});
