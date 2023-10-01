<?php

use App\Models\Recipe;
use App\Models\User;
use Faker\Factory as Faker;

test('opinion recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $randUser = User::where('is_banned', false)->inRandomOrder()->first();

    // Sélection d'une recette existante qui ne lui appartient pas
    $randRecipe = Recipe::where('user_id', '!=', $randUser->id)->where('is_accepted', true)->inRandomOrder()->first();

    // Envoi d'une modification de statut (favori ou signalement) au controller
    $favorite        = rand(0, 1);
    $typeInformation = match ($favorite) {
        0 => 'Ingrédient signalé',
        1 => 'Ingrédient mis en favori'
    };
    $response = $this->actingAs($randUser)->post('/recipe/status', ['is_favorite' => $favorite, 'is_reported' => ! $favorite, 'recipeid' => $randRecipe->id]);

    // Vérification de la redirection et qu'il n'y a pas d'erreur
    $response->assertStatus(302)->assertSessionMissing('statusError');
});

test('opinion non existing recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $randUser = User::where('is_banned', false)->inRandomOrder()->first();

    // Envoi d'une modification de statut (favori ou signalement) au controller
    $favorite        = rand(0, 1);
    $typeInformation = match ($favorite) {
        0 => 'Ingrédient signalé',
        1 => 'Ingrédient mis en favori'
    };
    $response = $this->actingAs($randUser)->post('/recipe/status', ['is_favorite' => $favorite, 'is_reported' => ! $favorite, 'recipeid' => Recipe::orderBy('id', 'DESC')->first()->id + 1]);

    // Vérification de la redirection et qu'il n'y a pas de succès
    $response->assertStatus(302)->assertSessionHasErrors('recipeid');
});

test('comment recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $randUser = User::where('is_banned', false)->inRandomOrder()->first();

    // Sélection d'une recette existante qui ne lui appartient pas
    $randRecipe = Recipe::where('user_id', '!=', $randUser->id)->where('is_accepted', true)->inRandomOrder()->first();

    // Envoi d'une modification de statut (favori ou signalement) au controller
    $score = rand(1, 5);

    // Création d'une phrase au hasard
    $faker    = Faker::create();
    $comment  = $faker->sentence(6);
    $response = $this->actingAs($randUser)->post('/recipe/comment/'.$randRecipe->id, ['score' => $score, 'comment' => $comment]);

    // Vérification de la redirection et qu'il n'y a pas d'erreur
    $response->assertStatus(302)->assertSessionMissing('error');
});

test('comment non existing recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $randUser = User::where('is_banned', false)->inRandomOrder()->first();

    // Envoi d'une modification de statut (favori ou signalement) au controller
    $score = rand(1, 5);

    // Création d'une phrase au hasard
    $faker    = Faker::create();
    $comment  = $faker->sentence(6);
    $recipeId = Recipe::orderBy('id', 'DESC')->first()->id + 1;
    $response = $this->actingAs($randUser)->post("/recipe/comment/$recipeId", ['score' => $score, 'comment' => $comment]);

    // Vérification de la redirection et qu'il n'y a pas d'erreur
    $response->assertStatus(404);
});

test('score overscored recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $randUser = User::where('is_banned', false)->inRandomOrder()->first();

    // Sélection d'une recette existante qui ne lui appartient pas
    $randRecipe = Recipe::where('user_id', '!=', $randUser->id)->where('is_accepted', true)->inRandomOrder()->first();

    // Attribution d'une note trop élevée
    $score = rand(6, 100);

    // Création d'une phrase au hasard
    $faker   = Faker::create();
    $comment = $faker->sentence(6);

    // Envoi d'une modification de statut (favori ou signalement) au controller
    $response = $this->actingAs($randUser)->post('/recipe/comment/'.$randRecipe->id, ['score' => $score, 'comment' => $comment]);

    // Vérification si on a bien une erreur au niveau du score
    $response->assertStatus(302)->assertSessionHasErrors('score');
});
