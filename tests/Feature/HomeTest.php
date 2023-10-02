<?php


use App\Enums\RecipeTypes;

test('access homepage', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('access exploration', function () {
    $response = $this->get('/exploration');

    $response->assertStatus(200);
});

test('access exploration with name', function () {
    //? Nom valide
    $name   = 'aut';
    $type = null;
    $diet   = null;

    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");

    $response->assertStatus(200);
});

test('access exploration with type', function () {
    //? Type valide valide
    $name     = null;
    $type   = fake()->randomElement(RecipeTypes::allValues());
    $diet     = null;
    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");
    $response->assertStatus(200);

    //? Type inexistant
    $type   = fake()->word;
    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");
    $response->assertSessionHasErrors('type')->assertStatus(302);

    //? Type invalide
    $type   = 'dqsfsqdqsDDSQ';
    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");
    $response->assertSessionHasErrors('type')->assertStatus(302);
});

test('access exploration with diet', function () {
    //? Régime valide valide
    $name     = null;
    $type   = null;
    $diet     = rand(0, 5);
    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");
    $response->assertStatus(200);

    //? Régime inexistant
    $diet     = 50;
    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");
    $response->assertSessionHasErrors('diet')->assertStatus(302);

    //? Régime invalide
    $diet     = 'dqsfsqdqsDDSQ';
    $response = $this->get("/exploration?name=$name&type=$type&diet=$diet");
    $response->assertSessionHasErrors('diet')->assertStatus(302);
});
