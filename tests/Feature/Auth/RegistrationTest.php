<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Faker\Factory as Faker;

test('registration screen can be rendered', function () {
    $response = $this->get('/registration');

    $response->assertStatus(200);
});

test('new users can register', function () {
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
});
