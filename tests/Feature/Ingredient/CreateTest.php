<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

it('denies access to ingredients create if not authorized user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    actingAs($user)->get(route('new-ingredient.create'))
                   ->assertStatus(403);
});

it('access to ingredient create if authorized user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = actingAs($user)->get(route('new-ingredient.create'))
                   ->assertOk()->assertViewIs('newingredient');
});
