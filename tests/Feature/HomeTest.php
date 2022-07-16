<?php

namespace Tests\Feature;

use App\Models\RecipeType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeTest extends TestCase
{
    /**
     * Test d'accès à la page d'accueil
     *
     * @return void
     */
    public function test_access_homepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test d'accès à la page d'exploration des recettes
     *
     * @return void
     */
    public function test_access_exploration()
    {
        $response = $this->get('/exploration');

        $response->assertStatus(200);
    }

    /**
     * Test d'accès à la page d'accueil
     *
     * @return void
     */
    public function test_access_exploration_with_name()
    {
        //? Nom valide
        $name = 'aut';
        $typeId = null;
        $diet = null;

        $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");

        $response->assertStatus(200);

    }

    /**
     * Test d'accès à la page d'exploration avec un type
     *
     * @return void
     */
    public function test_access_exploration_with_type()
    {
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
        $typeId = "dqsfsqdqsDDSQ";
        $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
        $response->assertSessionHasErrors('typeId')->assertStatus(302);
    }

    /**
     * Test d'accès à la page d'exploration avec régime
     *
     * @return void
     */
    public function test_access_exploration_with_diet()
    {
        //? Régime valide valide
        $name = null;
        $typeId = null;
        $diet = rand(0,5);
        $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
        $response->assertStatus(200);


        //? Régime inexistant 
        $diet = 50;
        $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
        $response->assertSessionHasErrors('diet')->assertStatus(302);

        //? Régime invalide 
        $diet = "dqsfsqdqsDDSQ";
        $response = $this->get("/exploration?name=$name&typeId=$typeId&diet=$diet");
        $response->assertSessionHasErrors('diet')->assertStatus(302);
    }

}
