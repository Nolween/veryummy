<?php

use App\Models\RecipeType;

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
    $name = 'aut';
    $typeId = null;
    $diet = null;

    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");

    $response->assertStatus(200);
});

test('access exploration with type', function () {
    //? Type valide valide
    $name = null;
    $typeId = RecipeType::inRandomOrder()->first()->id;
    $diet = null;
    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
    $response->assertStatus(200);

    //? Type inexistant
    $typeId = RecipeType::orderBy('id', 'DESC')->first()->id + 1;
    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
    $response->assertSessionHasErrors('typeId')->assertStatus(302);

    //? Type invalide
    $typeId = 'dqsfsqdqsDDSQ';
    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
    $response->assertSessionHasErrors('typeId')->assertStatus(302);
});

test('access exploration with diet', function () {
    //? Régime valide valide
    $name = null;
    $typeId = null;
    $diet = rand(0, 5);
    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
    $response->assertStatus(200);

    //? Régime inexistant
    $diet = 50;
    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
    $response->assertSessionHasErrors('diet')->assertStatus(302);

    //? Régime invalide
    $diet = 'dqsfsqdqsDDSQ';
    $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
    $response->assertSessionHasErrors('diet')->assertStatus(302);
});
