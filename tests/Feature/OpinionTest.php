<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Faker\Factory as Faker;
use Tests\TestCase;

class OpinionTest extends TestCase
{
    /**
     * Tentative de modification de statut d'une recette avec un utilisateur et une recette au hasard
     *
     * @return void
     */
    public function test_opinion_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $randUser = User::where('is_banned', false)->inRandomOrder()->first();

        dump("Connection avec l'utilisateur $randUser->name");
        // Sélection d'une recette existante qui ne lui appartient pas
        $randRecipe = Recipe::where('user_id', '!=', $randUser->id)->where('is_accepted', true)->inRandomOrder()->first();

        // Envoi d'une modification de statut (favori ou signalement) au controller
        $favorite = rand(0, 1);
        $typeInformation = match ($favorite) {
            0 => 'Ingrédient signalé',
            1 => 'Ingrédient mis en favori'
        };
        dump($typeInformation);
        $response = $this->actingAs($randUser)->post('/recipe/status', ['is_favorite' => $favorite, 'is_reported' => ! $favorite, 'recipeid' => $randRecipe->id]);

        // Vérification de la redirection et qu'il n'y a pas d'erreur
        $response->assertStatus(302)->assertSessionMissing('statusError');
    }

    /**
     * Tentative de modification de statut d'une recette inexistante
     *
     * @return void
     */
    public function test_opinion_non_existing_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $randUser = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connection avec l'utilisateur $randUser->name");
        // Envoi d'une modification de statut (favori ou signalement) au controller
        $favorite = rand(0, 1);
        $typeInformation = match ($favorite) {
            0 => 'Ingrédient signalé',
            1 => 'Ingrédient mis en favori'
        };
        $response = $this->actingAs($randUser)->post('/recipe/status', ['is_favorite' => $favorite, 'is_reported' => ! $favorite, 'recipeid' => Recipe::orderBy('id', 'DESC')->first()->id + 1]);

        // Vérification de la redirection et qu'il n'y a pas de succès
        $response->assertStatus(302)->assertSessionHasErrors('recipeid');
    }

    /**
     * Tentative de création / modification de commentaire de recette avec un utilisateur et une recette au hasard
     *
     * @return void
     */
    public function test_comment_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $randUser = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connection avec l'utilisateur $randUser->name");
        // Sélection d'une recette existante qui ne lui appartient pas
        $randRecipe = Recipe::where('user_id', '!=', $randUser->id)->where('is_accepted', true)->inRandomOrder()->first();
        dump("Nom de la recette: $randRecipe->name");

        // Envoi d'une modification de statut (favori ou signalement) au controller
        $score = rand(1, 5);
        dump("Note attribuée: $score");
        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        $response = $this->actingAs($randUser)->post('/recipe/comment/'.$randRecipe->id, ['score' => $score, 'comment' => $comment]);

        // Vérification de la redirection et qu'il n'y a pas d'erreur
        $response->assertStatus(302)->assertSessionMissing('error');
    }

    /**
     * Tentative de création / modification de commentaire de recette avec une recette inexistante
     *
     * @return void
     */
    public function test_comment_non_existing_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $randUser = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connection avec l'utilisateur $randUser->name");

        // Envoi d'une modification de statut (favori ou signalement) au controller
        $score = rand(1, 5);
        dump("Note attribuée: $score");
        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        $recipeId = Recipe::orderBy('id', 'DESC')->first()->id + 1;
        $response = $this->actingAs($randUser)->post("/recipe/comment/$recipeId", ['score' => $score, 'comment' => $comment]);

        // Vérification de la redirection et qu'il n'y a pas d'erreur
        $response->assertStatus(302)->assertSessionHasErrors('recipeError');
    }

    /**
     * Tentative de création / modification de commentaire de recette avec une note trop élevée
     *
     * @return void
     */
    public function test_score_overscored_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $randUser = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connection avec l'utilisateur $randUser->name");

        // Sélection d'une recette existante qui ne lui appartient pas
        $randRecipe = Recipe::where('user_id', '!=', $randUser->id)->where('is_accepted', true)->inRandomOrder()->first();
        dump("Nom de la recette: $randRecipe->name");
        // Attribution d'une note trop élevée
        $score = rand(6, 100);
        dump("Note attribuée: $score");
        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        // Envoi d'une modification de statut (favori ou signalement) au controller
        $response = $this->actingAs($randUser)->post('/recipe/comment/'.$randRecipe->id, ['score' => $score, 'comment' => $comment]);

        // Vérification si on a bien une erreur au niveau du score
        $response->assertStatus(302)->assertSessionHasErrors('score');
    }
}
