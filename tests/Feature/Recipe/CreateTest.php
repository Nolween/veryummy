<?php

use App\Models\User;

it('denies access to create recipe page if user is not authentificated', function () {
    $response = $this->get(route('my-recipes.create'));

    $response->assertStatus(403);
});

it('denies access to create recipe if user is banned', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    $response = $this->actingAs($user)->get(route('my-recipes.create'));

    $response->assertStatus(403);
});

it('shows create recipe page', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('my-recipes.create'));

    $response->assertViewIs('recipenew')->assertOk();
});
