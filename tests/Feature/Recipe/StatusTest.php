<?php

use App\Models\Recipe;
use App\Models\User;

it('denies update recipe status if user is not authentificated', function () {
    $response = $this->post(route('recipe.status'));

    $response->assertStatus(403);
});

it('denies update recipe status if user is banned', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    $response = $this->actingAs($user)->post(route('recipe.status'));

    $response->assertStatus(403);
});

it('denies update recipe status if rules are not respected', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->post(route('recipe.status'), [
        'is_favorite' => 'test',
        'is_reported' => 'test',
        'recipeid'    => 'test'
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['is_favorite', 'is_reported', 'recipeid']);
});

it('updates recipe status to favorite', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['is_accepted' => 1]);

    $response = $this->actingAs($user)->post(route('recipe.status'), [
        'is_favorite' => 1,
        'is_reported' => 0,
        'recipeid'    => $recipe->id
    ]);

    $response->assertStatus(302)
             ->assertSessionHas('statusSuccess');
//  Find the recipe opinion of the user and check if it is favorite
    expect($recipe->opinions()->where('user_id', $user->id)->first()->is_favorite)->toBe(1);
});

it('updates recipe status to repported', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['is_accepted' => 1]);

    $response = $this->actingAs($user)->post(route('recipe.status'), [
        'is_favorite' => 0,
        'is_reported' => 1,
        'recipeid'    => $recipe->id
    ]);

    $response->assertStatus(302)
             ->assertSessionHas('statusSuccess');
    // Find the recipe opinion of the user and check if it is favorite
    expect($recipe->opinions()->where('user_id', $user->id)->first()->is_reported)->toBe(1);
});
