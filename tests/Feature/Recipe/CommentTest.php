<?php

use App\Models\Recipe;
use App\Models\User;

it('denies edit recipe page if banned user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('recipe.comment', ['recipe' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies edit recipe page if not authentificated', function () {
    $recipe = Recipe::factory()->create();

    $response = $this->post(route('recipe.comment', ['recipe' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies comment if rules are not respected', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipeUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['user_id' => $recipeUser->id]);

    $response = $this->actingAs($user)->post(route('recipe.comment', ['recipe' => $recipe->id]), [
        'comment' => 'a',
        'score'   => 6
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors([
                                          'comment',
                                          'score'
                                      ]);
});

it('comments recipes', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipeUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['user_id' => $recipeUser->id]);

    $response = $this->actingAs($user)->post(route('recipe.comment', ['recipe' => $recipe->id]), [
        'comment' => 'test',
        'score'   => 5
    ]);

    $response->assertStatus(302)
             ->assertSessionHasNoErrors()
             ->assertSessionHas('success', 'Commentaire effectué');
//    Ensure that comment is in database
    $this->assertDatabaseHas('recipe_opinions', [
        'comment'   => 'test',
        'score'     => 5,
        'recipe_id' => $recipe->id,
        'user_id'   => $user->id
    ]);
});
