<?php


use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\RecipeStep;
use App\Models\User;

it('denies access to recipe page if recipe is not accepted and user is not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $recipe = Recipe::factory()->create(['is_accepted' => false]);

    $response = $this->actingAs($user)->get(route('recipe.show', ['id' => $recipe->id]));

    $response->assertStatus(403);
});

it('denies access to recipe page if recipe does not exist', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('recipe.show', ['id' => 1]));

    $response->assertStatus(404);
});


it('shows recipe page if recipe is accepted', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $recipe = Recipe::factory()->create(['is_accepted' => true]);

    // create 5 ingredients
    $ingredients = Ingredient::factory()->count(5)->create(['is_accepted' => true]);

    foreach ($ingredients as $ingredient) {
        $recipeIngredient = RecipeIngredients::factory()->create(['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id]);
    }

    // create some steps
    for ($i = 0; $i < 5; $i++) {
        $step = RecipeStep::factory()->create(['recipe_id' => $recipe->id]);
    }
    // Create some opinions
    for($i = 0; $i < 5; $i++) {
        $userOpinion = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
        $opinion = RecipeOpinion::factory()->create(['recipe_id' => $recipe->id, 'user_id' => $userOpinion->id]);
    }

    $response = $this->actingAs($user)->get(route('recipe.show', ['id' => $recipe->id]));

    $response->assertOk()->assertViewIs('recipeshow')->assertViewHas(['recipe', 'ingredients', 'steps', 'comments', 'userId', 'opinion', 'type']);
});
