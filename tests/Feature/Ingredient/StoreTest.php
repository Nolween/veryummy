<?php

use App\Models\Ingredient;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('denies access to ingredients store if not authorized user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    actingAs($user)->post(route('new-ingredient.store'))
                   ->assertStatus(403);
});

it('denies access to ingredient store if not respecting rules', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = actingAs($user)->post(route('new-ingredient.store'), [
        'name' => 'test',
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['ingredient', 'rulescheck']);
});

it('store ingredient', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = actingAs($user)->post(route('new-ingredient.store'), [
        'ingredient' => 'test',
        'rulescheck' => true,
    ]);

    // On s'attend à une redirection vers mes recettes
    $response->assertRedirect(route('my-recipes.list'));
    // On s'attend à avoir un message de succès
    $response->assertSessionHas('ingredientProposeSuccess');

// On s'attend à avoir un ingrédient en base de données
    expect(Ingredient::where('name', 'test')->first())->not()->toBeNull();
});
