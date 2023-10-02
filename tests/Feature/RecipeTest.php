<?php

use App\Enums\RecipeTypes;
use App\Enums\Units;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeOpinion;
use App\Models\RecipeType;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

test('access recipe card', function () {
    //? Connexion sans utilisateur connecté

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        $randomRecipe = Recipe::factory()->create(['is_accepted' => true]);
    }
    $response = $this->get('/recipe/show/'.$randomRecipe->id);
    $response->assertStatus(200);

    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $response = $this->actingAs($user)->get("/recipe/show/$randomRecipe->id");
    $response->assertStatus(200);
});

test('access non existing recipe', function () {
    //? Accès à une recette inexistante

    $response = $this->get('/recipe/show/9999999999');
    $response->assertStatus(404);
});

test('change status recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        $randomRecipe = Recipe::factory()->create(['is_accepted' => true]);
    }
    $isFavorite      = rand(0, 1);
    $typeInformation = match ($isFavorite) {
        0 => 'Ingrédient signalé',
        1 => 'Ingrédient mis en favori'
    };
    $dataToSend = [
        'is_favorite' => $isFavorite,
        'is_reported' => ! $isFavorite,
    ];

    $response = $this->actingAs($user)->post("/recipe/status/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('statusSuccess');
});

test('change status non existing recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    //? Modification d'une recette qui n'existe pas

    $isFavorite      = rand(0, 1);
    $typeInformation = match ($isFavorite) {
        0 => 'Ingrédient signalé',
        1 => 'Ingrédient mis en favori'
    };

    $recipeid = Recipe::orderBy('id', 'DESC')->first()->id + 1;

    $dataToSend = [
        'is_favorite' => $isFavorite,
        'is_reported' => ! $isFavorite,
        'recipeid'    => $recipeid,
    ];

    $response = $this->actingAs($user)->post("/recipe/status/$recipeid", $dataToSend);
    $response->assertStatus(404);
});

test('change status recipe with bad request', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    //? Requete non valide

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        expect(false)->toBeTrue();

        return false;
    }
    $dataToSend = [
        'is_favorite' => 'qsdsqdsqd',
        'is_reported' => 'sdfqdfsdfc',
    ];

    $response = $this->actingAs($user)->post("/recipe/status/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors();
});

test('change status recipe with guest', function () {
    //? Pas d'utilisateur connecté

    // Déconnexion de l'utilisateur connecté
    Auth::logout();
    $isFavorite      = rand(0, 1);
    $typeInformation = match ($isFavorite) {
        0 => 'Ingrédient signalé',
        1 => 'Ingrédient mis en favori'
    };

    $dataToSend = [
        'is_favorite' => $isFavorite,
        'is_reported' => ! $isFavorite,
    ];

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        expect(false)->toBeTrue();

        return false;
    }
    $response = $this->post("/recipe/status/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('badUser');
});

test('comment recipe', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        $randomRecipe = Recipe::factory()->create(['is_accepted' => true]);
    }

    $dataToSend = [
        'score'   => rand(1, 5),
        'comment' => fake()->sentence(6),
    ];

    $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('success');
});

test('comment recipe with guest', function () {
    //? Pas d'utilisateur connecté

    // Déconnexion de l'utilisateur connecté
    Auth::logout();

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        expect(false)->toBeTrue();

        return false;
    }

    // Création d'une phrase au hasard
    $faker      = Faker::create();
    $comment    = $faker->sentence(6);
    $dataToSend = [
        'score'   => rand(1, 5),
        'comment' => $comment,
    ];

    $response = $this->post("/recipe/comment/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors()->assertSessionMissing('success');
});

test('comment non existing recipe', function () {
    //? Recette inexistante

    // Création d'une phrase au hasard
    $faker      = Faker::create();
    $comment    = $faker->sentence(6);
    $dataToSend = [
        'score'   => rand(1, 5),
        'comment' => $comment,
    ];

    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $recipeId = Recipe::orderBy('id', 'DESC')->first()->id + 1;
    $response = $this->actingAs($user)->post("/recipe/comment/$recipeId", $dataToSend);
    $response->assertStatus(404);
});

test('comment recipe with bad scores', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    //? Score trop bas

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        expect(false)->toBeTrue();

        return false;
    }

    // Création d'une phrase au hasard
    $faker      = Faker::create();
    $comment    = $faker->sentence(6);
    $dataToSend = [
        'score'   => 0,
        'comment' => $comment,
    ];

    $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('score')->assertSessionMissing('success');

    //? Score trop haut
    $dataToSend['score'] = 6;

    $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('score')->assertSessionMissing('success');
});

test('empty comment recipe', function () {
    //? Commentaire vide
    $dataToSend = [
        'score'   => 0,
        'comment' => '',
    ];

    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette au hasard
    $randomRecipe = Recipe::where('is_accepted', true)->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipe) {
        expect(false)->toBeTrue();

        return false;
    }

    $response = $this->actingAs($user)->post("/recipe/comment/$randomRecipe->id", $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('comment')->assertSessionMissing('success');
});

test('empty opinion', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une opinion au hasard de l'utilisateur sur une recette
    $randomRecipeOpinion = RecipeOpinion::where('user_id', $user->id)->with('recipe')->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipeOpinion) {
        $randomRecipeOpinion = RecipeOpinion::factory()->create(['user_id' => $user->id]);
    }

    $response = $this->actingAs($user)->patch("/recipe/opinion/empty/$randomRecipeOpinion->recipe_id");
    $response->assertStatus(302)->assertSessionHas('success');
});

test('empty opinion with guest', function () {
    //? Utilisateur non connecté
    Auth::logout();

    // On prend une opinion au hasard de l'utilisateur sur une recette
    $randomRecipeOpinion = RecipeOpinion::with('recipe')->inRandomOrder()->first();

    // Si pas de recette trouvée
    if (! $randomRecipeOpinion) {
        $randomRecipeOpinion = RecipeOpinion::factory()->create();
    }

    $response = $this->patch("/recipe/opinion/empty/$randomRecipeOpinion->recipe_id");
    $response->assertStatus(302)->assertSessionHasErrors('badUser');
});

test('empty opinion non existing recipe', function () {
    //? Recette inexistante

    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $response = $this->actingAs($user)->patch('/recipe/opinion/empty/9999999999');
    $response->assertStatus(404);
});

test('empty non existing opinion', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    //? Pas d'opinion pour cette recette et cet utilisateur

    // Il suffit de prendre une recette de l'utilisateur car il ne peut pas avoir d'opinion dessus
    $recipe   = Recipe::factory()->create(['user_id' => $user->id]);
    $response = $this->actingAs($user)->patch("/recipe/opinion/empty/$recipe->id");
    $response->assertStatus(404);
});

test('access my recipes', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $name   = '';
    $type = fake()->randomElement(RecipeTypes::allValues());
    $diet   = rand(1, 5);

    $response = $this->actingAs($user)->get("/my-recipes?name=$name&type=$type&diet=$diet");

    $response->assertStatus(200);
});

test('access my recipes with false type', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $type   = fake()->word;
    $diet     = rand(1, 5);
    $response = $this->actingAs($user)->get("/my-recipes?diet=$diet&type=$type");

    $response->assertSessionHasErrors('type');
});

test('access my recipes with false diet', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $type   = fake()->randomElement(RecipeTypes::allValues());
    $diet     = rand(7, 99);
    $response = $this->actingAs($user)->get("/my-recipes?diet=$diet&type=$type");
    $response->assertSessionHasErrors('diet');
});

test('access my notebook', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $name   = '';
    $type = fake()->randomElement(RecipeTypes::allValues());
    $diet   = rand(1, 5);

    $response = $this->actingAs($user)->get("/my-notebook?name=$name&type=$type&diet=$diet");

    $response->assertStatus(200);
});

test('access my notebook with false type', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $type   = fake()->word;
    $diet     = rand(1, 5);
    $response = $this->actingAs($user)->get("/my-notebook?diet=$diet&type=$type");

    // $response->dumpSession();
    $response->assertSessionHasErrors('type');
});

test('access my notebook with false diet', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $type   = fake()->randomElement(RecipeTypes::allValues());
    $diet     = rand(7, 99);
    $response = $this->actingAs($user)->get("/my-notebook?diet=$diet&type=$type");
    $response->assertSessionHasErrors('diet');
});

test('access new recipe', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $response = $this->actingAs($user)->get('/recipe/new');
    $response->assertStatus(200);
});

test('access new recipe with guest', function () {
    //? Utilisateur non connecté
    Auth::logout();

    $response = $this->get('/recipe/new');
    $response->assertStatus(302)->assertSessionHasErrors('badUser');
});

test('create recipe', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $faker   = Faker::create();
    $newName = $faker->sentence(6);

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);
    $dataToSend      = [
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('newSuccess');
});

test('create recipe with non existing type', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);
    $dataToSend      = [
        'nom'             => 'Test Recette 1',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->word,
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('type')->assertSessionMissing('newSuccess');
});

test('create recipe with non existing ingredient', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);
    $dataToSend      = [
        'nom'             => 'Test Recette 1',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout d'un ingrédient faussé
    $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
    $dataToSend['ingredients'][] = [
        'ingredientId'       => Ingredient::orderBy('id', 'DESC')->first()->id + 1,
        'ingredientName'     => $randomIngredient->name,
        'ingredientUnit'     => fake()->randomElement(Units::allValues()),
        'ingredientQuantity' => rand(1, 20),
    ];

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('ingredientError')->assertSessionMissing('newSuccess');
});

test('create recipe with non existing unit', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);
    $dataToSend      = [
        'nom'             => 'Test Recette 1',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout d'un ingrédient avec une fausse unité
    $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
    $dataToSend['ingredients'][] = [
        'ingredientId'       => Ingredient::inRandomOrder()->first()->id,
        'ingredientName'     => $randomIngredient->name,
        'ingredientUnit'     => fake()->word,
        'ingredientQuantity' => rand(1, 20),
    ];

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('unitError')->assertSessionMissing('newSuccess');
});

test('create recipe with bad times', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préapation négatif
    $dataToSend = [
        'nom'             => 'Test Recette 1',
        'preparation'     => -5,
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
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
    $dataToSend['cuisson']     = -10;

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('newSuccess');

    //? Temps de cuisson trop élevé
    $dataToSend['cuisson'] = 5000;

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('newSuccess');
});

test('create recipe with bad servings', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préapation négatif
    $dataToSend = [
        'nom'             => 'Test Recette 1',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => -5,
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
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
});

test('create recipe with short name', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préapation négatif
    $dataToSend = [
        'nom'             => 'A',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(5, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('nom')->assertSessionMissing('newSuccess');
});

test('create recipe with bad file', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    Storage::fake('avatars');

    $file = UploadedFile::fake()->create(
        'document.pdf',
        500,
        'application/pdf'
    );

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préapation négatif
    $dataToSend = [
        'nom'             => 'A',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(5, 20),
        'photoInput'      => $file,
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->put('/recipe/create', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('photoInput')->assertSessionMissing('newSuccess');
});

test('update recipe', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }

    $faker           = Faker::create();
    $newName         = $faker->sentence(6);
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);
    $dataToSend      = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);

    $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('updateSuccess');
});

test('update recipe with bad owner', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette de l'utilisateur
    $recipe = Recipe::where('user_id', '!=', $user)->inRandomOrder()->first();

    $faker           = Faker::create();
    $newName         = $faker->sentence(6);
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);
    $dataToSend      = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);

    $response->assertStatus(302)->assertSessionHasErrors('editError')->assertSessionMissing('updateSuccess');
});

test('update recipe with bad times', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }

    $faker           = Faker::create();
    $newName         = $faker->sentence(6);
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => -5,
        'cuisson'         => rand(5, 60),
        'parts'           => rand(1, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('preparation')->assertSessionMissing('updateSuccess');

    //? Temps de préparation exessif
    $dataToSend['preparation'] = 2000;
    $response                  = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('preparation')->assertSessionMissing('updateSuccess');

    //? Temps de cuisson négatif
    $dataToSend['preparation'] = rand(1, 60);
    $dataToSend['cuisson']     = -5;
    $response                  = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('updateSuccess');

    //? Temps de cuisson exessif
    $dataToSend['cuisson'] = 2000;
    $response              = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('cuisson')->assertSessionMissing('updateSuccess');
});

test('update recipe with bad servings', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }

    $faker           = Faker::create();
    $newName         = $faker->sentence(6);
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => -5,
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('parts')->assertSessionMissing('updateSuccess');

    //? Personnes exessif
    $dataToSend['parts'] = 2000;
    $response            = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('parts')->assertSessionMissing('updateSuccess');
});

test('update recipe with non existing recipe', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $faker           = Faker::create();
    $newName         = $faker->sentence(6);
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => Recipe::orderBy('id', 'DESC')->first()->id + 1,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => -5,
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('recipeid')->assertSessionMissing('updateSuccess');
});

test('update recipe with short name', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => 'A',
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => -5,
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('nom')->assertSessionMissing('updateSuccess');
});

test('update recipe with bad file', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $faker   = Faker::create();
    $newName = $faker->sentence(6);

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    // Insertion d'un mauvais fichier
    Storage::fake('avatars');

    $file = UploadedFile::fake()->create(
        'document.pdf',
        500,
        'application/pdf'
    );

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'photoInput'      => $file,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => -5,
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('photoInput')->assertSessionMissing('updateSuccess');
});

test('update recipe with bad unit', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $faker   = Faker::create();
    $newName = $faker->sentence(6);

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => -5,
        'stepCount'       => $stepCount,
        'type'            => fake()->word,
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('type')->assertSessionMissing('updateSuccess');
});

test('update recipe with non existing ingredient', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $faker   = Faker::create();
    $newName = $faker->sentence(6);

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }
    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(5, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout d'un ingrédient faussé
    $dataToSend['ingredients'][] = [
        'ingredientId'       => Ingredient::orderBy('id', 'DESC')->first()->id + 1,
        'ingredientName'     => 'Mauvais ingrédient',
        'ingredientUnit'     => fake()->randomElement(Units::allValues()),
        'ingredientQuantity' => rand(1, 20),
    ];

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('ingredientError')->assertSessionMissing('updateSuccess');
});

test('update recipe with non existing unit', function () {
    //? Connexion avec utilisateur
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $faker   = Faker::create();
    $newName = $faker->sentence(6);

    // On prend une recette de l'utilisateur
    $recipe = Recipe::whereBelongsTo($user)->inRandomOrder()->first();
    if (! $recipe) {
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    }

    $ingredientCount = rand(1, 10);
    $stepCount       = rand(1, 10);

    //? Temps de préparation négatif
    $dataToSend = [
        'recipeid'        => $recipe->id,
        'nom'             => $newName,
        'preparation'     => rand(5, 60),
        'cuisson'         => rand(5, 60),
        'parts'           => rand(5, 20),
        'stepCount'       => $stepCount,
        'type'            => fake()->randomElement(RecipeTypes::allValues()),
        'ingredientCount' => $ingredientCount,
    ];

    // Ajout des ingrédients
    for ($i = 0; $i < $ingredientCount; $i++) {
        $randomIngredient            = Ingredient::where('is_accepted', true)->inRandomOrder()->first();
        $dataToSend['ingredients'][] = [
            'ingredientId'       => $randomIngredient->id,
            'ingredientName'     => $randomIngredient->name,
            'ingredientUnit'     => fake()->randomElement(Units::allValues()),
            'ingredientQuantity' => rand(1, 20),
        ];
    }

    // Ajout d'un ingrédient faussé
    $dataToSend['ingredients'][] = [
        'ingredientId'       => Ingredient::inRandomOrder()->first()->id,
        'ingredientName'     => 'Mauvaise unité',
        'ingredientUnit'     => fake()->word,
        'ingredientQuantity' => rand(1, 20),
    ];

    // Ajout des étapes
    for ($i = 0; $i < $stepCount; $i++) {
        $faker                 = Faker::create();
        $step                  = $faker->sentence(6);
        $dataToSend['steps'][] = [
            'stepDescription' => $step,
        ];
    }

    // Envoi vers la route
    $response = $this->actingAs($user)->post('/recipe/update', $dataToSend);
    $response->assertStatus(404);
});
