<?php

use App\Providers\RouteServiceProvider;

test('login screen can be rendered', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = initialize_user();

    // Accès à la connexion avec les infos d'authentification
    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => '123456',
    ]);

    // Test de l'authentification
    $this->assertAuthenticated();

    // Test de redirection vers la vue définie lorsque la connexion est réussie, ici /my-notebook
    $response->assertRedirect(RouteServiceProvider::HOME);
});

test('users can not authenticate with invalid password', function () {
    $user = initialize_user();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
