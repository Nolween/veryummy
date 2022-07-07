<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Faker\Factory as Faker;

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
            0 => 'Ingrédient mis en favori',
            1 => 'Ingrédient signalé'
        };
        dump($typeInformation);
        $response = $this->actingAs($randUser)->post('/recipe/status', ['is_favorite' => $favorite, 'is_reported' => !$favorite, 'recipeid' => $randRecipe->id]);

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
            0 => 'Ingrédient mis en favori',
            1 => 'Ingrédient signalé'
        };
        dump($typeInformation);
        $response = $this->actingAs($randUser)->post('/recipe/status', ['is_favorite' => $favorite, 'is_reported' => !$favorite, 'recipeid' => 4152185421]);

        // $response->dumpSession();
        // Vérification de la redirection et qu'il n'y a pas de succès
        $response->assertStatus(302)->assertSessionHas('statusError', 'Recette introuvable');
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
        $response = $this->actingAs($randUser)->post('/recipe/comment/' . $randRecipe->id, ['score' => $score, 'comment' => $comment]);

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
        $response = $this->actingAs($randUser)->post('/recipe/comment/999999', ['score' => $score, 'comment' => $comment]);

        // Vérification de la redirection et qu'il n'y a pas d'erreur
        $response->assertStatus(302)->assertSessionHas('error', 'Recette introuvable');
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
        $response = $this->actingAs($randUser)->post('/recipe/comment/' . $randRecipe->id, ['score' => $score, 'comment' => $comment]);

        // Vérification si on a bien une erreur au niveau du score
        $response->assertStatus(302)->assertSessionHasErrors('score');
    }
}
