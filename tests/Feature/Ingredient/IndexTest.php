<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

use function Pest\Laravel\actingAs;

it('denies access to ingredients list in admin page if not admin', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER]);

    actingAs($user)->get(route('admin-ingredients.index', ['type' => 1]))
                   ->assertStatus(403);
});

it('shows accepted ingredients list in admin page', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Ingredient::factory()->count(20)->create(['is_accepted' => true]);

   $response = actingAs($user)->get(route('admin-ingredients.index', ['type' => 1]))
                   ->assertOk();
   expect($response->viewData('ingredients'))->toBeInstanceOf(LengthAwarePaginator::class);
   expect($response->viewData('ingredients'))->toHaveCount(20);
});

it('shows accepted ingredients list in admin page with search', function () {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Ingredient::factory()->count(20)->create(['is_accepted' => true]);
    Ingredient::factory()->create(['is_accepted' => true, 'name' => 'test']);

   $response = actingAs($user)->get(route('admin-ingredients.index', ['type' => 1, 'search' => 'test']))
                   ->assertOk();
   expect($response->viewData('ingredients'))->toBeInstanceOf(LengthAwarePaginator::class);
   //Expect to have at least one ingredient with name 'test'
    expect(count($response->viewData('ingredients')))->toBeGreaterThanOrEqual(1);
});
