<?php

namespace Tests\Feature;

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

}
