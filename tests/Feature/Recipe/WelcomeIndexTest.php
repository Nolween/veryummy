<?php

use App\Models\Recipe;

it('show welcome page with recipes', function () {

    // Create some recipes
    $recipes = Recipe::factory()->count(10)->create(['is_accepted' => 1]);

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertViewIs('welcome');
    $response->assertViewHas(['popularRecipes', 'recentRecipes', 'counts']);

    expect($response['popularRecipes'])->count(4);
    expect($response['recentRecipes'])->count(4);
});
