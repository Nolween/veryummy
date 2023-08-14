<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\RecipeType;
use App\Models\Unit;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    /**
     * Test d'accès à une fiche de recette
     *
     * @return void
     */
    public function test_access_recipe_card()
    {

        //? Connexion sans utilisateur connecté
        dump('Connexion sans utilisateur connecté');
        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valable trouvée');
            $this->assertTrue(false);

            return false;
        }
        $response = $this->get("/recipe/show/$randomRecipe->id");
        $response->assertStatus(200);

        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $response = $this->actingAs($user)->get("/recipe/show/$randomRecipe->id");
        $response->assertStatus(200);
    }

    /**
     * Test d'accès à une fiche de recette
     *
     * @return void
     */
    public function test_access_non_existing_recipe()
    {
        //? Accès à une recette inexistante
        dump('Accès à une recette inexistante');

        $response = $this->get('/recipe/show/sdfdsfdsf');
        $response->assertStatus(404);
    }

    /**
     * Mise en favori / signalement une recette
     *
     * @return void
     */
    public function test_change_status_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valable trouvée');
            $this->assertTrue(false);

            return false;
        }
        $isFavorite = rand(0, 1);
        $typeInformation = match ($isFavorite) {
            0 => 'Ingrédient signalé',
            1 => 'Ingrédient mis en favori'
        };
        dump($typeInformation);
        $dataToSend = [
            'is_favorite' => $isFavorite,
            'is_reported' => ! $isFavorite,
        ];

        $response = $this->actingAs($user)->post("/recipe/status/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('statusSuccess');
    }

    /**
     * Mise en favori / signalement une recette avec une recette qui n'existe pas
     *
     * @return void
     */
    public function test_change_status_non_existing_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        //? Modification d'une recette qui n'existe pas
        dump("Modification d'une recette qui n'existe pas");

        $isFavorite = rand(0, 1);
        $typeInformation = match ($isFavorite) {
            0 => 'Ingrédient signalé',
            1 => 'Ingrédient mis en favori'
        };
        dump($typeInformation);
        $dataToSend = [
            'is_favorite' => $isFavorite,
            'is_reported' => ! $isFavorite,
        ];

        $recipeId = Recipe::orderBy('id', 'DESC')->first()->id + 1;
        $response = $this->actingAs($user)->post("/recipe/status/$recipeId", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('recipeError');
    }

    /**
     * Mise en favori / signalement une recette avec de fausses données
     *
     * @return void
     */
    public function test_change_status_recipe_with_bad_request()
    {

        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        //? Requete non valide
        dump('Requete non valide');
        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valable trouvée');
            $this->assertTrue(false);

            return false;
        }
        $dataToSend = [
            'is_favorite' => 'qsdsqdsqd',
            'is_reported' => 'sdfqdfsdfc',
        ];

        $response = $this->actingAs($user)->post("/recipe/status/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors();
    }

    /**
     * Mise en favori / signalement une recette avec un utilisateur non connecté
     *
     * @return void
     */
    public function test_change_status_recipe_with_guest()
    {
        //? Pas d'utilisateur connecté
        dump("Pas d'utilisateur connecté");
        // Déconnexion de l'utilisateur connecté
        Auth::logout();
        $isFavorite = rand(0, 1);
        $typeInformation = match ($isFavorite) {
            0 => 'Ingrédient signalé',
            1 => 'Ingrédient mis en favori'
        };
        dump($typeInformation);

        $dataToSend = [
            'is_favorite' => $isFavorite,
            'is_reported' => ! $isFavorite,
        ];
        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valable trouvée');
            $this->assertTrue(false);

            return false;
        }
        $response = $this->post("/recipe/status/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('badUser');
    }

    /**
     * Ecriture d'un commentaire pour une recette
     *
     * @return void
     */
    public function test_comment_recipe()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valide trouvée');
            $this->assertTrue(false);

            return false;
        }
        dump("Recette $randomRecipe->name sélectionnée");

        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        $dataToSend = [
            'score' => rand(1, 5),
            'comment' => $comment,
        ];

        $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('success');
    }

    /**
     * Ecriture d'un commentaire pour une recette avec e fausses données
     *
     * @return void
     */
    public function test_comment_recipe_with_guest()
    {
        //? Pas d'utilisateur connecté
        dump("Pas d'utilisateur connecté");
        // Déconnexion de l'utilisateur connecté
        Auth::logout();
        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valide trouvée');
            $this->assertTrue(false);

            return false;
        }
        dump("Recette $randomRecipe->name sélectionnée");

        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        $dataToSend = [
            'score' => rand(1, 5),
            'comment' => $comment,
        ];

        $response = $this->post("/recipe/comment/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors()->assertSessionMissing('success');
    }

    /**
     * Ecriture d'un commentaire pour une recette avec e fausses données
     *
     * @return void
     */
    public function test_comment_non_existing_recipe()
    {
        //? Recette inexistante
        dump('Recette inexistante');
        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        $dataToSend = [
            'score' => rand(1, 5),
            'comment' => $comment,
        ];
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $recipeId = Recipe::orderBy('id', 'DESC')->first()->id + 1;
        $response = $this->actingAs($user)->post("/recipe/comment/$recipeId", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('recipeError')->assertSessionMissing('success');
    }

    /**
     * Ecriture d'un commentaire pour une recette avec e fausses données
     *
     * @return void
     */
    public function test_comment_recipe_with_bad_scores()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        //? Score trop bas
        dump('Score trop bas');
        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valide trouvée');
            $this->assertTrue(false);

            return false;
        }
        dump("Recette $randomRecipe->name sélectionnée");

        // Création d'une phrase au hasard
        $faker = Faker::create();
        $comment = $faker->sentence(6);
        $dataToSend = [
            'score' => 0,
            'comment' => $comment,
        ];

        $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('score')->assertSessionMissing('success');

        //? Score trop haut
        dump('Score trop haut');
        $dataToSend['score'] = 6;

        $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('score')->assertSessionMissing('success');
    }

    /**
     * Ecriture d'un commentaire pour une recette avec e fausses données
     *
     * @return void
     */
    public function test_empty_comment_recipe()
    {
        //? Commentaire vide
        dump('Commentaire vide');
        $dataToSend = [
            'score' => 0,
            'comment' => '',
        ];
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette au hasard
        $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipe) {
            dump('Pas de recette valide trouvée');
            $this->assertTrue(false);

            return false;
        }

        $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('comment')->assertSessionMissing('success');
    }

    /**
     * Supprimer le commentaire et la note attribuée à une recette
     *
     * @return void
     */
    public function test_empty_opinion()
    {

        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une opinion au hasard de l'utilisateur sur une recette
        $randomRecipeOpinion = RecipeOpinion::where('user_id', $user->id)->with('recipe')->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipeOpinion) {
            dump("Pas de commentaire valide trouvé chez l'utilisateur");
            $this->assertTrue(false);

            return false;
        }
        dump('Recette '.$randomRecipeOpinion->recipe->name.' sélectionnée');

        $response = $this->actingAs($user)->patch("/recipe/opinion/empty/$randomRecipeOpinion->recipe_id");
        $response->assertStatus(302)->assertSessionHas('success');
    }

    /**
     * Supprimer le commentaire et la note attribuée à un utilisateur non connecté
     *
     * @return void
     */
    public function test_empty_opinion_with_guest()
    {
        //? Utilisateur non connecté
        dump('Utilisateur non connecté');
        Auth::logout();
        // On prend une opinion au hasard de l'utilisateur sur une recette
        $randomRecipeOpinion = RecipeOpinion::with('recipe')->inRandomOrder()->first();
        // Si pas de recette trouvée
        if (! $randomRecipeOpinion) {
            dump("Pas de commentaire valide trouvé chez l'utilisateur");
            $this->assertTrue(false);

            return false;
        }
        dump('Recette '.$randomRecipeOpinion->recipe->name.' sélectionnée');

        $response = $this->patch("/recipe/opinion/empty/$randomRecipeOpinion->recipe_id");
        $response->assertStatus(302)->assertSessionHasErrors('badUser');
    }

    /**
     * Supprimer le commentaire et la note attribuée à une recette avec des erreurs
     *
     * @return void
     */
    public function test_empty_opinion_non_existing_recipe()
    {
        //? Recette inexistante
        dump('Recette inexistante');
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $response = $this->actingAs($user)->patch('/recipe/opinion/empty/dflkndqfsdsqdqs');
        $response->assertStatus(404);
    }

    /**
     * Supprimer le commentaire et la note attribuée à une recette avec des erreurs
     *
     * @return void
     */
    public function test_empty_non_existing_opinion()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");
        //? Pas d'opinion pour cette recette et cet utilisateur
        dump("Pas d'opinion pour cette recette et cet utilisateur");
        // Il suffit de prendre une recette de l'utilisateur car il ne peut pas avoir d'opinion dessus
        $recipe = Recipe::where('user_id', $user->id)->first();
        if (! $recipe) {
            dump("Pas de recette chez l'utilisateur");
            $this->assertTrue(false);

            return false;
        }
        $response = $this->actingAs($user)->patch("/recipe/opinion/empty/$recipe->id");
        $response->assertStatus(404);
    }

    /**
     * Accès aux recettes de l'utilisateur
     *
     * @return void
     */
    public function test_access_my_recipes()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $name = '';
        $typeId = RecipeType::inRandomOrder()->first()->id;
        $diet = rand(1, 5);

        $response = $this->actingAs($user)->get("/my-recipes?name=$name&typeId=$typeId&diet=$diet");
        // dump($response['search']);
        $response->assertStatus(200);
    }

    /**
     * Accès aux recettes de l'utilisateur avec un mauvais type de plat
     *
     * @return void
     */
    public function test_access_my_recipes_with_false_type()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $typeId = rand(RecipeType::orderBy('id', 'DESC')->first()->id + 1, 999);
        $diet = rand(1, 5);
        $response = $this->actingAs($user)->get("/my-recipes?diet=$diet&typeId=$typeId");
        // dump($response['typeId']);
        // $response->dumpSession();
        $response->assertSessionHasErrors('typeId');
    }

    /**
     * Accès aux recettes de l'utilisateur avec un mauvais type régime
     *
     * @return void
     */
    public function test_access_my_recipes_with_false_diet()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $typeId = RecipeType::inRandomOrder()->first()->id;
        $diet = rand(7, 99);
        $response = $this->actingAs($user)->get("/my-recipes?diet=$diet&typeId=$typeId");
        $response->assertSessionHasErrors('diet');
    }

    /**
     * Accès aux recettes favories de l'utilisateur
     *
     * @return void
     */
    public function test_access_my_notebook()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $name = '';
        $typeId = RecipeType::inRandomOrder()->first()->id;
        $diet = rand(1, 5);

        $response = $this->actingAs($user)->get("/my-notebook?name=$name&typeId=$typeId&diet=$diet");
        // dump($response['search']);
        $response->assertStatus(200);
    }

    /**
     * Accès aux recettes favories de l'utilisateur avec un mauvais type de plat
     *
     * @return void
     */
    public function test_access_my_notebook_with_false_type()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $typeId = rand(RecipeType::orderBy('id', 'DESC')->first()->id + 1, 999);
        $diet = rand(1, 5);
        $response = $this->actingAs($user)->get("/my-notebook?diet=$diet&typeId=$typeId");
        // dump($response['typeId']);
        // $response->dumpSession();
        $response->assertSessionHasErrors('typeId');
    }

    /**
     * Accès aux recettes favories de l'utilisateur avec un mauvais type régime
     *
     * @return void
     */
    public function test_access_my_notebook_with_false_diet()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $typeId = RecipeType::inRandomOrder()->first()->id;
        $diet = rand(7, 99);
        $response = $this->actingAs($user)->get("/my-notebook?diet=$diet&typeId=$typeId");
        $response->assertSessionHasErrors('diet');
    }

    /**
     * Accès à la page de création d'une nouvelle recette
     *
     * @return void
     */
    public function test_access_new_recipe()
    {

        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $response = $this->actingAs($user)->get('/recipe/new');
        $response->assertStatus(200);
    }

    /**
     * Accès à la page de création d'une nouvelle recette
     *
     * @return void
     */
    public function test_access_new_recipe_with_guest()
    {

        //? Utilisateur non connecté
        dump('Utilisateur non connecté');
        Auth::logout();

        $response = $this->get('/recipe/new');
        $response->assertStatus(302)->assertSessionHasErrors('badUser');
    }

    /**
     * Création d'une recette
     *
     * @return void
     */
    public function test_create_recipe()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $faker = Faker::create();
        $newName = $faker->sentence(6);

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        $dataToSend = [
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // dump($dataToSend);
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('newSuccess');
    }

    /**
     * Création d'une recette avec un type de recette non existant
     *
     * @return void
     */
    public function test_create_recipe_with_non_existing_type()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        $dataToSend = [
            'nom' => 'Test Recette 1',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => rand(RecipeType::orderBy('id', 'DESC')->first()->id + 1, 999),
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('type')->assertSessionMissing('newSuccess');
    }

    /**
     * Création d'une recette avec un ingrédient non existant
     *
     * @return void
     */
    public function test_create_recipe_with_non_existing_ingredient()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        $dataToSend = [
            'nom' => 'Test Recette 1',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout d'un ingrédient faussé
        $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId' => Ingredient::orderBy('id', 'DESC')->first()->id + 1,
            'ingredientName' => $randomIngredient->name,
            'ingredientUnit' => Unit::inRandomOrder()->first()->id,
            'ingredientQuantity' => rand(1, 20),
        ];
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('ingredientError')->assertSessionMissing('newSuccess');
    }

    /**
     * Création d'une recette avec une unité non existante
     *
     * @return void
     */
    public function test_create_recipe_with_non_existing_unit()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        $dataToSend = [
            'nom' => 'Test Recette 1',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout d'un ingrédient avec une fausse unité
        $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId' => Ingredient::inRandomOrder()->first()->id,
            'ingredientName' => $randomIngredient->name,
            'ingredientUnit' => Unit::orderBy('id', 'DESC')->first()->id + 1,
            'ingredientQuantity' => rand(1, 20),
        ];
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('unitError')->assertSessionMissing('newSuccess');
    }

    /**
     * Création d'une recette avec une unité non existante
     *
     * @return void
     */
    public function test_create_recipe_with_bad_times()
    {

        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préapation négatif
        $dataToSend = [
            'nom' => 'Test Recette 1',
            'preparation' => -5,
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('preparation')->assertSessionMissing('newSuccess');

        $dataToSend['preparation'] = 2000;
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('preparation')->assertSessionMissing('newSuccess');

        //? Temps de cuisson négatif
        $dataToSend['preparation'] = rand(1, 60);
        $dataToSend['cuisson'] = -10;
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('newSuccess');

        //? Temps de cuisson trop élevé
        $dataToSend['cuisson'] = 5000;
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('newSuccess');
    }

    /**
     * Création d'une recette avec un nombre de personnes erroné
     *
     * @return void
     */
    public function test_create_recipe_with_bad_servings()
    {

        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préapation négatif
        $dataToSend = [
            'nom' => 'Test Recette 1',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => -5,
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('parts')->assertSessionMissing('newSuccess');

        //? Nombre de personnes exessif
        $dataToSend['parts'] = 2000;
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('parts')->assertSessionMissing('newSuccess');
    }

    /**
     * Création d'une recette avec un nom trop court
     *
     * @return void
     */
    public function test_create_recipe_with_short_name()
    {

        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préapation négatif
        $dataToSend = [
            'nom' => 'A',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(5, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('nom')->assertSessionMissing('newSuccess');

    }

    /**
     * Création d'une recette avec un fichier inadapté
     *
     * @return void
     */
    public function test_create_recipe_with_bad_file()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        Storage::fake('avatars');

        $file = UploadedFile::fake()->create(
            'document.pdf', 500, 'application/pdf'
        );

        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préapation négatif
        $dataToSend = [
            'nom' => 'A',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(5, 20),
            'photoInput' => $file,
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];
        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('photoInput')->assertSessionMissing('newSuccess');
    }

    /**
     * Mise à jour d'une recette
     *
     * @return void
     */
    public function test_update_recipe()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // dump($dataToSend);
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        // $response->dumpSession();
        $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('updateSuccess');
    }

    /**
     * Mise à jour d'une recette qui n'appartient pas à l'utilisateur
     *
     * @return void
     */
    public function test_update_recipe_with_bad_owner()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette de l'utilisateur
        $recipe = Recipe::where('user_id', '!=', $user)->inRandomOrder()->first();

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // dump($dataToSend);
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        // $response->dumpSession();
        $response->assertStatus(302)->assertSessionHasErrors('editError')->assertSessionMissing('updateSuccess');
    }

    /**
     * Mise à jour d'une recette avec de mauvais temps de cuisson / préparation
     *
     * @return void
     */
    public function test_update_recipe_with_bad_times()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => -5,
            'cuisson' => rand(5, 60),
            'parts' => rand(1, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('preparation')->assertSessionMissing('updateSuccess');

        //? Temps de préparation exessif
        $dataToSend['preparation'] = 2000;
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('preparation')->assertSessionMissing('updateSuccess');
        //? Temps de cuisson négatif
        $dataToSend['preparation'] = rand(1, 60);
        $dataToSend['cuisson'] = -5;
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('updateSuccess');
        //? Temps de cuisson exessif
        $dataToSend['cuisson'] = 2000;
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('updateSuccess');
    }

    /**
     * Mise à jour d'une recette avec un nombre de personne faux
     *
     * @return void
     */
    public function test_update_recipe_with_bad_servings()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => -5,
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('parts')->assertSessionMissing('updateSuccess');

        //? Personnes exessif
        $dataToSend['parts'] = 2000;
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('parts')->assertSessionMissing('updateSuccess');
    }

    /**
     * Mise à jour d'une recette avec une recette inexistante
     *
     * @return void
     */
    public function test_update_recipe_with_non_existing_recipe()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => Recipe::orderBy('id', 'DESC')->first()->id + 1,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => -5,
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('recipeid')->assertSessionMissing('updateSuccess');

    }

    /**
     * Mise à jour d'une recette avec un nom trop court
     *
     * @return void
     */
    public function test_update_recipe_with_short_name()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => 'A',
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => -5,
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('nom')->assertSessionMissing('updateSuccess');

    }

    /**
     * Mise à jour d'une recette avec un mauvais fichier
     *
     * @return void
     */
    public function test_update_recipe_with_bad_file()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);
        // Insertion d'un mauvais fichier
        Storage::fake('avatars');

        $file = UploadedFile::fake()->create(
            'document.pdf', 500, 'application/pdf'
        );

        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'photoInput' => $file,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => -5,
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('photoInput')->assertSessionMissing('updateSuccess');

    }

    /**
     * Mise à jour d'une recette avec un mauvais type de recette
     *
     * @return void
     */
    public function test_update_recipe_with_bad_unit()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);

        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => -5,
            'stepCount' => $stepCount,
            'type' => rand(RecipeType::orderBy('id', 'DESC')->first()->id + 1, 999),
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }
        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('type')->assertSessionMissing('updateSuccess');

    }

    /**
     * Mise à jour d'une recette avec un ingrédient inexistant
     *
     * @return void
     */
    public function test_update_recipe_with_non_existing_ingredient()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);

        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(5, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout d'un ingrédient faussé
        $dataToSend['ingredients'][] = [
            'ingredientId' => Ingredient::orderBy('id', 'DESC')->first()->id + 1,
            'ingredientName' => 'Mauvais ingrédient',
            'ingredientUnit' => Unit::inRandomOrder()->first()->id,
            'ingredientQuantity' => rand(1, 20),
        ];
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }

        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('ingredientError')->assertSessionMissing('updateSuccess');

    }

    /**
     * Mise à jour d'une recette avec une unité de mesure inexistante
     *
     * @return void
     */
    public function test_update_recipe_with_non_existing_unit()
    {
        //? Connexion avec utilisateur
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        $faker = Faker::create();
        $newName = $faker->sentence(6);
        // On prend une recette de l'utilisateur
        $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
        $ingredientCount = rand(1, 10);
        $stepCount = rand(1, 10);

        //? Temps de préparation négatif
        $dataToSend = [
            'recipeid' => $recipe->id,
            'nom' => $newName,
            'preparation' => rand(5, 60),
            'cuisson' => rand(5, 60),
            'parts' => rand(5, 20),
            'stepCount' => $stepCount,
            'type' => RecipeType::inRandomOrder()->first()->id,
            'ingredientCount' => $ingredientCount,
        ];

        // Ajout des ingrédients
        for ($i = 0; $i < $ingredientCount; $i++) {
            $randomIngredient = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
            $dataToSend['ingredients'][] = [
                'ingredientId' => $randomIngredient->id,
                'ingredientName' => $randomIngredient->name,
                'ingredientUnit' => Unit::inRandomOrder()->first()->id,
                'ingredientQuantity' => rand(1, 20),
            ];
        }
        // Ajout d'un ingrédient faussé
        $dataToSend['ingredients'][] = [
            'ingredientId' => Ingredient::inRandomOrder()->first()->id,
            'ingredientName' => 'Mauvaise unité',
            'ingredientUnit' => Unit::orderBy('id', 'DESC')->first()->id + 1,
            'ingredientQuantity' => rand(1, 20),
        ];
        // Ajout des étapes
        for ($i = 0; $i < $stepCount; $i++) {
            $faker = Faker::create();
            $step = $faker->sentence(6);
            $dataToSend['steps'][] = [
                'stepDescription' => $step,
            ];
        }

        // Envoi vers la route
        $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('unitError')->assertSessionMissing('updateSuccess');

    }
}
