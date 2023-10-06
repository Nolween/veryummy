<?php

use App\Models\User;

it('denies user to users index if not admin', function() {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('admin-users.index', ['type' => 1]));

    $response->assertStatus(403);
});

it('access to users index if admin', function() {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    // Create 10 users to see it in the list
    User::factory()->count(10)->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('admin-users.index', ['type' => 1]));

    $response->assertStatus(200)->assertViewIs('adminuserslist');

    // Check if the list contains 10 users
    expect($response->viewData('users')->count())->toBe(10);
});

it('access to users index with search', function() {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);
    // Create 10 users to see it in the list
    for ($i = 0; $i < 10; $i++) {
        User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0, 'name' => "test$i"]);
    }

    $response = $this->actingAs($user)->get(route('admin-users.index', ['type' => 1, 'search' => 'test']));

    $response->assertStatus(200)->assertViewIs('adminuserslist');

    // Check if the list contains 10 users
    expect($response->viewData('users')->count())->toBe(10);
});

it("denies users index if user doesn't respect rules", function() {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

    $response = $this->actingAs($user)->get(route('admin-users.index', ['type' => 'test']));

    $response->assertStatus(500);
});

