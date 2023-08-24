<?php

use App\Models\Ingredient;
use App\Models\User;
use Faker\Factory as Faker;


/**
 * Prendre un ingrédient au hasard pour réinitialiser sa modération
 *
 * @return Ingredient $randomIngredientToModerate
 */
function setIngredientToModerate()
{
    $randomIngredientToModerate = Ingredient::inRandomOrder()->first();
    $randomIngredientToModerate->is_accepted = null;
    $randomIngredientToModerate->save();

    return $randomIngredientToModerate;
}

test('access handling ingredients list', function () {


    $simpleUser = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_banned' => false,
    ]);

    // Sélection d'un type d'ingrédient au hasard (en cours de modération / refusés / acceptés)
    $typeList = rand(0, 2);
    $typeInformation = match ($typeList) {
        0 => 'Liste ingrédients en cours de modération',
        1 => 'Liste ingrédients acceptés',
        2 => 'Liste ingrédients refusés'
    };
    $response = $this->actingAs($simpleUser)->get("/admin/ingredients/list/$typeList");

    $response->assertStatus(200);
});

test('access handling ingredients list as simple user', function () {
    // On sélectionne un utilisateur non admin au hasard qui n'est pas banni
    $simpleUser = User::where('is_banned', false)->where('role', User::ROLE_USER)->inRandomOrder()->first();

    // Sélection d'un type d'ingrédient au hasard (en cours de modération / refusés / acceptés)
    $typeList = rand(0, 2);
    $typeInformation = match ($typeList) {
        0 => 'Liste ingrédients en cours de modération',
        1 => 'Liste ingrédients acceptés',
        2 => 'Liste ingrédients refusés'
    };
    $response = $this->actingAs($simpleUser)->get("/admin/ingredients/list/$typeList");

    $response->assertStatus(302);
});

test('allowing ingredient', function () {
    // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
    $adminUser = User::where('is_banned', false)->where('role', User::ROLE_ADMIN)->inRandomOrder()->first();

    // Préparation des données à envoyer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }

    $dataToSend = [
        'ingredientid' => $randomHandlingIngredient->id,
        'allow'        => 1,
        'finalname'    => $randomHandlingIngredient->name,
        'typeList'     => 0,
        'vegetarian'   => rand(0, 1),
        'vegan'        => rand(0, 1),
        'glutenfree'   => rand(0, 1),
        'halal'        => rand(0, 1),
        'kosher'       => rand(0, 1),
    ];

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);

    $response->assertStatus(302)->assertSessionHas('ingredientAllowSuccess');
});

test('allowing ingredient with wrong datas', function () {
    // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
    $adminUser = User::where('is_banned', false)->where('role', User::ROLE_ADMIN)->inRandomOrder()->first();

    //? Tentative avec un ingrédient non existant

    // Assertion d'un ingrédient non existant
    $dataToSend = [
        'ingredientid' => 999999,
        'allow'        => 1,
        'finalname'    => 'Nouvel ingrédient',
        'typeList'     => 0,
        'vegetarian'   => rand(0, 1),
        'vegan'        => rand(0, 1),
        'glutenfree'   => rand(0, 1),
        'halal'        => rand(0, 1),
        'kosher'       => rand(0, 1),
    ];
    $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
    $response->assertStatus(302)->assertSessionMissing('ingredientAllowSuccess');

    //? Tentative avec le champ allow à 0
    // Choix d'un ingrédient à modérer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }
    $dataToSend['allow'] = 0;
    $dataToSend['ingredientid'] = $randomHandlingIngredient->id;

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
    $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

    //? Tentative avec un nom d'ingrédient trop long
    // Choix d'un ingrédient à modérer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }
    $dataToSend['finalname'] = 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Beatae, minus. Minima eos beatae nulla nesciunt quidem? Perspiciatis possimus eaque dolorem, aliquid porro omnis est praesentium. Nobis magni nam incidunt odio.Lorem ipsum dolor sit amet consectetur adipisicing elit. Amet velit commodi eum? Nisi quo sit cumque pariatur, eveniet vero dolor temporibus voluptas, veritatis, culpa a fuga odit. Blanditiis, eveniet possimus.';

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
    $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

    //? Tentative une compatibilité incorrecte
    // Choix d'un ingrédient à modérer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }
    $dataToSend['finalname'] = $randomHandlingIngredient->name;
    $dataToSend['vegan'] = 'qsdspkkl,qsdk,nqds';

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
    $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');
});

test('denying ingredient', function () {
    // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
    $adminUser = User::where('is_banned', false)->where('role', User::ROLE_ADMIN)->inRandomOrder()->first();

    // Préparation des données à envoyer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }

    $dataToSend = [
        'ingredientid' => $randomHandlingIngredient->id,
        'deny'         => 1,
        'denymessage'  => "Refusé parce que c'est un test",
        'typeList'     => 0,
    ];

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/deny', $dataToSend);

    $response->assertStatus(302)->assertSessionHas('ingredientAllowSuccess');
});

test('denying ingredient with wrong datas', function () {
    // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
    $adminUser = User::where('is_banned', false)->where('role', User::ROLE_ADMIN)->inRandomOrder()->first();

    //? Tentative avec un ingrédient non existant

    // Assertion d'un ingrédient non existant
    $dataToSend = [
        'ingredientid' => 999999,
        'deny'         => 1,
        'denymessage'  => 'Refus du nouvel ingrédient',
        'typeList'     => 0,
    ];
    $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
    $response->assertStatus(302)->assertSessionMissing('ingredientAllowSuccess');

    //? Tentative avec le champ deny à 0
    // Choix d'un ingrédient à modérer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }
    $dataToSend['deny'] = 0;
    $dataToSend['ingredientid'] = $randomHandlingIngredient->id;

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/deny', $dataToSend);
    $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

    //? Tentative avec un message de refus trop court
    // Choix d'un ingrédient à modérer
    $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();

    // Si on a pas d'ingrédient en cours de modération
    if (!$randomHandlingIngredient) {
        $randomHandlingIngredient = setIngredientToModerate();
    }
    $dataToSend['denymessage'] = null;

    $response = $this->actingAs($adminUser)->post('/admin/ingredients/deny', $dataToSend);
    $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');
});

test('access ingredient proposition', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $simpleUser = User::where('is_banned', false)->inRandomOrder()->first();

    $response = $this->actingAs($simpleUser)->get('/ingredients/new');

    $response->assertStatus(200);
});

test('access ingredient proposition with banned user', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $simpleUser = User::where('is_banned', true)->inRandomOrder()->first();
    if (!$simpleUser) {
        $simpleUser = initialize_user(true, false);
    }

    $response = $this->actingAs($simpleUser)->get('/ingredients/new');

    $response->assertStatus(302);
});

test('proposing ingredient', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    $dataToSend = [
        'ingredient' => 'Ingrédient test',
        'rulescheck' => 'true',
    ];

    $response = $this->actingAs($user)->post('/ingredients/propose', $dataToSend);

    $response->assertStatus(302)->assertSessionHas('ingredientProposeSuccess')->assertSessionHasNoErrors();
});

test('proposing ingredient with false datas', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();

    //? Tentative avec un ingredient trop long
    $dataToSend = [
        'ingredient' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Sit in deleniti a, reiciendis, fugiat consectetur fuga sequi qui unde minima quos? Quod excepturi adipisci eius voluptatum dignissimos iusto autem porro. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Non dolor quaerat nesciunt veniam! Distinctio corrupti soluta culpa architecto ipsa, eligendi facere! Assumenda ex quisquam, nostrum modi eius sit vitae temporibus!',
        'rulescheck' => 'true',
    ];

    $response = $this->actingAs($user)->post('/ingredients/propose', $dataToSend);

    $response->assertSessionMissing('ingredientProposeSuccess')->assertSessionHasErrors();

    //? Tentative sans l'acceptation des règles
    $dataToSend = [
        'ingredient' => 'Nouvel Ingrédient',
        'rulescheck' => 'false',
    ];

    $response = $this->actingAs($user)->post('/ingredients/propose', $dataToSend);

    $response->assertSessionMissing('ingredientProposeSuccess')->assertSessionHasErrors();
});
