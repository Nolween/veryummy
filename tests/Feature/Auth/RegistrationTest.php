<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Faker\Factory as Faker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/registration');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {

        $faker = Faker::create();
        $mail = $faker->email();
        $newName = $faker->firstName().' '.$faker->lastName();
        $newMail = User::where('email', $mail)->first();
        // Tant qu'on créé des mail qui existent déjà dans la BDD
        while ($newMail) {
            $mail = $faker->email();
            $newMail = User::where('email', $mail)->first();
        }

        $response = $this->post('/register', [
            'name' => $newName,
            'email' => $mail,
            'password' => 'AlsqdDpefdzkl82:',
            'password_confirmation' => 'AlsqdDpefdzkl82:',
            'test' => true,
        ]);
        // dump($response);
        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
