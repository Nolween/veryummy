<?php

use App\Models\Recipe;
use App\Models\User;

it('denies moderation if user is not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($user)->patch(route('admin.recipes.moderate'))
         ->assertStatus(403);
});

it('denies moderation if rules are not respected', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $this->actingAs($user)->patch(route('admin.recipes.moderate'), [
        'recipeid' => 'test',
        'allow'    => 'test',
        'typeList' => 'test'
    ])->assertStatus(302)
         ->assertSessionHasErrors(['recipeid', 'allow', 'typeList']);
});

it('moderates recipe and remove reports', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['is_accepted' => 0]);

    $reportUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    // Make some reports
    $recipe->opinions()->createMany([
                                        ['user_id' => $reportUser->id, 'is_reported' => 1],
                                        ['user_id' => $reportUser->id, 'is_reported' => 1],
                                        ['user_id' => $reportUser->id, 'is_reported' => 1],
                                    ]);


    $this->actingAs($user)->patch(route('admin.recipes.moderate'), [
        'recipeid' => $recipe->id,
        'allow'    => 1,
        'typeList' => fake()->numberBetween(0, 3)
    ])->assertStatus(302)
         ->assertSessionHas('recipeAllowSuccess');

//  Repports are not is reported anymore
    expect($recipe->opinions()->where('is_reported', 1)->count())->toBe(0);
});

it('moderates recipe and recipe is deleted', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $recipe = Recipe::factory()->create(['is_accepted' => 0]);

    $reportUser = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    // Make some reports
    $recipe->opinions()->createMany([
                                        ['user_id' => $reportUser->id, 'is_reported' => 1],
                                        ['user_id' => $reportUser->id, 'is_reported' => 1],
                                        ['user_id' => $reportUser->id, 'is_reported' => 1],
                                    ]);

    $this->actingAs($user)->patch(route('admin.recipes.moderate'), [
        'recipeid' => $recipe->id,
        'allow'    => 0,
        'typeList' => fake()->numberBetween(0, 3)
    ])->assertStatus(302)
         ->assertSessionHas('recipeAllowSuccess');

// Recipe deleted would not have opinions
    expect($recipe->opinions()->count())->toBe(0);
    expect(Recipe::find($recipe->id))->toBeNull();
});
