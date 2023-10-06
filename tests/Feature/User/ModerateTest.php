<?php

use App\Models\Ingredient;
use App\Models\OpinionReport;
use App\Models\Recipe;
use App\Models\RecipeIngredients;
use App\Models\RecipeOpinion;
use App\Models\User;

it('denies user from moderating if he is not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->delete(route('admin-users.moderate'));

    $response->assertStatus(403);
});

it('denies user from moderating if rules are not respected', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $response = $this->actingAs($user)->delete(route('admin-users.moderate'), []);
    $response->assertStatus(302)
             ->assertSessionHasErrors(['opinionid', 'typelist']);
});

it('moderates opinion', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $userToModerate = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    // Create a recipe
    $recipe = Recipe::factory()->create(['user_id' => $userToModerate->id]);

    // Create an opinion that will be moderated
    $opinion = RecipeOpinion::factory()->create(['recipe_id' => $recipe->id, 'user_id' => $userToModerate->id]);

    $opinionReport = OpinionReport::factory()->create(['user_id' => $userToModerate->id, 'opinion_id' => $opinion->id]);


    $response = $this->actingAs($user)->delete(route('admin-users.moderate'), [
        'opinionid' => $opinion->id,
        'typelist'  => 1,
        'destroy'   => 1
    ]);

    $response->assertStatus(302)
             ->assertSessionHas('deletionSuccess');
    // Verify that the opinion has been deleted
    expect(RecipeOpinion::find($opinion->id))->toBeNull();
});

it('moderates and delete opinion repport', function(){

    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $userToModerate = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    // Create a recipe
    $recipe = Recipe::factory()->create(['user_id' => $userToModerate->id]);

    // Create an opinion that will be moderated
    $opinion = RecipeOpinion::factory()->create(['recipe_id' => $recipe->id, 'user_id' => $userToModerate->id]);

    $opinionReport = OpinionReport::factory()->create(['user_id' => $userToModerate->id, 'opinion_id' => $opinion->id]);

    $response = $this->actingAs($user)->delete(route('admin-users.moderate'), [
        'opinionid' => $opinion->id,
        'typelist'  => 1,
        'destroy'   => 0
    ]);

    $response->assertStatus(302)
             ->assertSessionHas('deletionSuccess');
    // Verify that the opinion report has been deleted
    expect(OpinionReport::find($opinionReport->id))->toBeNull();

});
