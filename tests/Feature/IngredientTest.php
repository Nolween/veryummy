<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IngredientTest extends TestCase
{

    /**
     * Prendre un ingrédient au hasard pour réinitialiser sa modération
     *
     * @return Ingredient $randomIngredientToModerate
     */
    public function setIngredientToModerate()
    {
        $randomIngredientToModerate = Ingredient::inRandomOrder()->first();
        $randomIngredientToModerate->is_accepted = null;
        $randomIngredientToModerate->save();
        return $randomIngredientToModerate;
    }

    /**
     * Accéder aux listes des ingrédients en tant qu'admin
     *
     * @return void
     */
    public function test_access_handling_ingredients_list()
    {
        // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
        $adminUser = User::where('is_banned', false)->where('role_id', 1)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $adminUser->name");
        // Sélection d'un type d'ingrédient au hasard (en cours de modération / refusés / acceptés)
        $typeList = rand(0, 2);
        $typeInformation = match ($typeList) {
            0 => 'Liste ingrédients en cours de modération',
            1 => 'Liste ingrédients acceptés',
            2 => 'Liste ingrédients refusés'
        };
        dump($typeInformation);
        $response = $this->actingAs($adminUser)->get("/admin/ingredients/list/$typeList");

        $response->assertStatus(200);
    }

    /**
     * Accéder aux listes des ingrédients en tant qu'utilisateur simple
     *
     * @return void
     */
    public function test_access_handling_ingredients_list_as_simple_user()
    {
        // On sélectionne un utilisateur non admin au hasard qui n'est pas banni
        $simpleUser = User::where('is_banned', false)->where('role_id', 2)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $simpleUser->name");
        // Sélection d'un type d'ingrédient au hasard (en cours de modération / refusés / acceptés)
        $typeList = rand(0, 2);
        $typeInformation = match ($typeList) {
            0 => 'Liste ingrédients en cours de modération',
            1 => 'Liste ingrédients acceptés',
            2 => 'Liste ingrédients refusés'
        };
        dump($typeInformation);
        $response = $this->actingAs($simpleUser)->get("/admin/ingredients/list/$typeList");

        $response->assertStatus(302);
    }



    /**
     * Test d'autorisation d'un ingrédient de la liste en cours de modération
     *
     * @return void
     */
    public function test_allowing_ingredient()
    {
        // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
        $adminUser = User::where('is_banned', false)->where('role_id', 1)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $adminUser->name");

        // Préparation des données à envoyer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Modération de l'ingrédient $randomHandlingIngredient->name");

        $dataToSend = [
            'ingredientid' => $randomHandlingIngredient->id,
            'allow' => 1,
            'finalname' => $randomHandlingIngredient->name,
            'typeList' => 0,
            'vegetarian' => rand(0, 1),
            'vegan' => rand(0, 1),
            'glutenfree' => rand(0, 1),
            'halal' => rand(0, 1),
            'kosher' => rand(0, 1),
        ];

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);

        $response->assertStatus(302)->assertSessionHas('ingredientAllowSuccess');
    }

    /**
     * Test d'autorisation d'un ingrédient de la liste en cours de modération avec des données erronnées
     *
     * @return void
     */
    public function test_allowing_ingredient_with_wrong_datas()
    {
        // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
        $adminUser = User::where('is_banned', false)->where('role_id', 1)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $adminUser->name");

        //? Tentative avec un ingrédient non existant
        dump("Tentative avec un ingrédient non existant");
        // Assertion d'un ingrédient non existant
        $dataToSend = [
            'ingredientid' => 999999,
            'allow' => 1,
            'finalname' => "Nouvel ingrédient",
            'typeList' => 0,
            'vegetarian' => rand(0, 1),
            'vegan' => rand(0, 1),
            'glutenfree' => rand(0, 1),
            'halal' => rand(0, 1),
            'kosher' => rand(0, 1),
        ];
        $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
        $response->assertStatus(302)->assertSessionMissing('ingredientAllowSuccess');

        //? Tentative avec le champ allow à 0
        // Choix d'un ingrédient à modérer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Tentative avec le champ allow à 0");
        $dataToSend['allow'] = 0;
        $dataToSend['ingredientid'] = $randomHandlingIngredient->id;

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
        $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

        //? Tentative avec un nom d'ingrédient trop long
        // Choix d'un ingrédient à modérer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Tentative avec un nom d'ingrédient trop long");
        $dataToSend['finalname'] = "Lorem ipsum, dolor sit amet consectetur adipisicing elit. Beatae, minus. Minima eos beatae nulla nesciunt quidem? Perspiciatis possimus eaque dolorem, aliquid porro omnis est praesentium. Nobis magni nam incidunt odio.Lorem ipsum dolor sit amet consectetur adipisicing elit. Amet velit commodi eum? Nisi quo sit cumque pariatur, eveniet vero dolor temporibus voluptas, veritatis, culpa a fuga odit. Blanditiis, eveniet possimus." ;

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
        $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');


        //? Tentative une compatibilité incorrecte
        // Choix d'un ingrédient à modérer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Tentative une compatibilité incorrecte");
        $dataToSend['finalname'] = $randomHandlingIngredient->name;
        $dataToSend['vegan'] = 'qsdspkkl,qsdk,nqds';

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
        $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

    }


    /**
     * Test de refus d'un ingrédient de la liste en cours de modération
     *
     * @return void
     */
    public function test_denying_ingredient()
    {
        // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
        $adminUser = User::where('is_banned', false)->where('role_id', 1)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $adminUser->name");

        // Préparation des données à envoyer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Modération de l'ingrédient $randomHandlingIngredient->name");

        $dataToSend = [
            'ingredientid' => $randomHandlingIngredient->id,
            'deny' => 1,
            'denymessage' => "Refusé parce que c'est un test",
            'typeList' => 0
        ];

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/deny', $dataToSend);

        $response->assertStatus(302)->assertSessionHas('ingredientAllowSuccess');
    }



    /**
     * Test de refus d'un ingrédient de la liste en cours de modération avec des données erronnées
     *
     * @return void
     */
    public function test_denying_ingredient_with_wrong_datas()
    {
        // On sélectionne un utilisateur ayant le rôle d'admin au hasard qui n'est pas banni
        $adminUser = User::where('is_banned', false)->where('role_id', 1)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $adminUser->name");

        //? Tentative avec un ingrédient non existant
        dump("Tentative avec un ingrédient non existant");
        // Assertion d'un ingrédient non existant
        $dataToSend = [
            'ingredientid' => 999999,
            'deny' => 1,
            'denymessage' => "Refus du nouvel ingrédient",
            'typeList' => 0
        ];
        $response = $this->actingAs($adminUser)->post('/admin/ingredients/allow', $dataToSend);
        $response->assertStatus(302)->assertSessionMissing('ingredientAllowSuccess');

        //? Tentative avec le champ deny à 0
        // Choix d'un ingrédient à modérer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Tentative avec le champ deny à 0");
        $dataToSend['deny'] = 0;
        $dataToSend['ingredientid'] = $randomHandlingIngredient->id;

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/deny', $dataToSend);
        $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

        //? Tentative avec un message de refus trop court
        // Choix d'un ingrédient à modérer
        $randomHandlingIngredient = Ingredient::where('is_accepted', null)->inRandomOrder()->first();
        // Si on a pas d'ingrédient en cours de modération
        if (!$randomHandlingIngredient) {
            $randomHandlingIngredient = $this->setIngredientToModerate();
        }
        dump("Tentative avec un message de refus trop court");
        $dataToSend['denymessage'] = null;

        $response = $this->actingAs($adminUser)->post('/admin/ingredients/deny', $dataToSend);
        $response->assertSessionHasErrors()->assertSessionMissing('ingredientAllowSuccess');

    }


    /**
     * Accéder à l'interface de proposition d'un nouvel ingrédient
     *
     * @return void
     */
    public function test_access_ingredient_proposition() {
        
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $simpleUser = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $simpleUser->name");

        $response = $this->actingAs($simpleUser)->get("/ingredients/new");
        
        $response->assertStatus(200);
    }



    /**
     * Accéder à l'interface de proposition d'un nouvel ingrédient avec un utilisateur banni
     *
     * @return void
     */
    public function test_access_ingredient_proposition_with_banned_user() {
        
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $simpleUser = User::where('is_banned', true)->inRandomOrder()->first();
        dump("Tentative de connexion avec l'utilisateur banni $simpleUser->name");

        $response = $this->actingAs($simpleUser)->get("/ingredients/new");
        
        $response->assertStatus(302);
    }


    /**
     * Test de proposition d'un ingrédient pour la modération
     *
     * @return void
     */
    public function test_proposing_ingredient()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");


        $dataToSend = [
            'ingredient' => 'Ingrédient test',
            'rulescheck' => 'true'
        ];

        $response = $this->actingAs($user)->post('/ingredients/propose', $dataToSend);

        $response->assertStatus(302)->assertSessionHas('ingredientProposeSuccess')->assertSessionHasNoErrors();
    }

    /**
     * Test de proposition d'un ingrédient pour la modération
     *
     * @return void
     */
    public function test_proposing_ingredient_with_false_datas()
    {
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");

        //? Tentative avec un ingredient trop long
        dump("Tentative avec un ingredient trop long");
        $dataToSend = [
            'ingredient' => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Sit in deleniti a, reiciendis, fugiat consectetur fuga sequi qui unde minima quos? Quod excepturi adipisci eius voluptatum dignissimos iusto autem porro. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Non dolor quaerat nesciunt veniam! Distinctio corrupti soluta culpa architecto ipsa, eligendi facere! Assumenda ex quisquam, nostrum modi eius sit vitae temporibus!",
            'rulescheck' => 'true'
        ];

        $response = $this->actingAs($user)->post('/ingredients/propose', $dataToSend);

        $response->assertSessionMissing('ingredientProposeSuccess')->assertSessionHasErrors();
       
        //? Tentative sans l'acceptation des règles
        dump("Tentative sans l'acceptation des règles");
        $dataToSend = [
            'ingredient' => "Nouvel Ingrédient",
            'rulescheck' => 'false'
        ];

        $response = $this->actingAs($user)->post('/ingredients/propose', $dataToSend);

        $response->assertSessionMissing('ingredientProposeSuccess')->assertSessionHasErrors();
    }

}
