<?php

use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\User;

it('denies empty comment page if banned user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('recipe-opinion.empty', ['recipe' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies empty comment recipe page if not authentificated', function () {
    $recipe = Recipe::factory()->create();

    $response = $this->patch(route('recipe-opinion.empty', ['recipe' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies empty comment if recipe does not not exist', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->patch(route('recipe-opinion.empty', ['recipe' => 1]));

    $response->assertStatus(404);
});

it('empties opinion', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipeUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['user_id' => $recipeUser->id]);

    $recipeOpinion = RecipeOpinion::factory()->create(['recipe_id' => $recipe->id, 'user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('recipe-opinion.empty', ['recipe' => $recipe->id]));

    $response->assertStatus(302)
             ->assertSessionHasNoErrors()
             ->assertSessionHas('success', 'Commentaire supprimé');

    // Expect that recipe Opinion of user and recipe has score and comment set to null
    $this->assertDatabaseHas('recipe_opinions', [
        'id'        => $recipeOpinion->id,
        'score'     => null,
        'comment'   => null
    ]);

});
