<?php

use App\Models\Ingredient;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('denies access to ingredients deny in admin page if not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    actingAs($user)->post(route('admin-ingredients.deny'))
                   ->assertStatus(403);
});


it('denies access to deny ingredient if not existing ingredient, deny message or typelist', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

    actingAs($user)->post(route('admin-ingredients.deny'))
                   ->assertStatus(302)
                   ->assertSessionHasErrors(['ingredientid', 'denymessage', 'typeList', 'deny']);
});

it('deny ingredient', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $ingredient = Ingredient::factory()->create(['is_accepted' => null]);

    $response = actingAs($user)->post(route('admin-ingredients.deny'), [
        'ingredientid' => $ingredient->id,
        'denymessage'  => 'test',
        'typeList'     => 1,
        'deny'         => true,
    ]);

    // L'ingrédient est désorais refusé
    expect(Ingredient::find($ingredient->id)->is_accepted)->toBe(0);
    // On s'attend à une redirection vers la liste des ingrédients
    $response->assertRedirect(route('admin-ingredients.index', ['type' => 1]));
    // On s'attend à avoir un message de succès
    $response->assertSessionHas('ingredientAllowSuccess');
});
