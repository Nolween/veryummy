<?php

use App\Models\Recipe;
use App\Models\User;

it('denies edit recipe page if banned user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('my-recipes.edit', ['id' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies edit recipe page if not authentificated', function () {
    $recipe = Recipe::factory()->create();

    $response = $this->get(route('my-recipes.edit', ['id' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies edit recipe page if not recipe owner', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $otherUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $recipe = Recipe::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->get(route('my-recipes.edit', ['id' => $recipe->id]));

    $response->assertStatus(403);
});

it('shows edit recipe page if recipe owner', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('my-recipes.edit', ['id' => $recipe->id]));

    $response->assertOk()->assertViewIs('recipeedit')->assertViewHas('recipe');
});
