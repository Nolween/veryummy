<?php

use App\Models\Ingredient;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('denies access to ingredients allow in admin page if not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    actingAs($user)->post(route('admin-ingredients.allow'))
                   ->assertStatus(403);
});


it('denies access to allow ingredient if not existing ingredientid, allow finalname or typeList', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    actingAs($user)->post(route('admin-ingredients.allow'))
                   ->assertStatus(302)
                   ->assertSessionHasErrors(['ingredientid', 'allow', 'finalname', 'typeList']);
});

it('allows ingredient', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    $ingredient = Ingredient::factory()->create(['is_accepted' => null]);

    $response = actingAs($user)->post(route('admin-ingredients.allow'), [
        'ingredientid' => $ingredient->id,
        'allow'        => true,
        'finalname'    => 'test',
        'typeList'     => 1,
    ]);

    $response->assertStatus(302)
             ->assertSessionHas('ingredientAllowSuccess');

    // L'ingrédient est désormais accepté
    expect(Ingredient::find($ingredient->id)->is_accepted)->toBe(1);
});
