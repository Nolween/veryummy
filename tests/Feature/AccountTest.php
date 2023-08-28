<?php

use App\Models\RecipeOpinion;
use App\Models\User;
use Faker\Factory as Faker;



test('access account page', function () {
    //? Accès avec un compte valide
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->inRandomOrder()->first();
    $response = $this->actingAs($user)->get('/my-account');
    $response->assertStatus(200);

    // Ya t-il un utilisateur banni dans la DB?
    $userToDelete = false;
    $user = User::where('is_banned', true)->inRandomOrder()->first();

    // Si pas d'utilisateur banni
    if (!$user) {
        // Initialisation d'un compte banni
        $user = initialize_user(true, false);
        $userToDelete = true;
    }

    //? Accès avec un compte banni
    $response = $this->actingAs($user)->get('/my-account');
    $response->assertStatus(302)->assertSessionHasErrors('badUser');

    // Si un utilisateur a été créé pour l'occasion
    if ($userToDelete == true) {
        User::destroy($user->id);
    }
});

test('edit account', function () {
    //? Avec un compte valide
    // On crée un utilisateur
    $user = initialize_user();

    // Données à envoyer
    $dataToSend = [
        'email'            => $user->email,
        'name'             => $user->name,
        'current-password' => '123456',
        'password'         => '12345678',
        'confirmation'     => '12345678',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->put('/my-account/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('userUpdateSuccess');

    // Destruction de l'utilisateur créé pour l'occasion
    User::destroy($user->id);

    //? Avec un compte banni
    // On crée un utilisateur
    $user = initialize_user(true, false);

    // Données à envoyer
    $dataToSend = [
        'email'            => $user->email,
        'name'             => $user->name,
        'current-password' => '123456',
        'password'         => '12345678',
        'confirmation'     => '12345678',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->put('/my-account/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('badUser')->assertSessionMissing('userUpdateSuccess');

    // Destruction de l'utilisateur
    User::destroy($user->id);

    //? Avec un mot de passe actuel erroné
    // On crée un utilisateur
    $user = initialize_user(false, false);

    // Données à envoyer
    $dataToSend = [
        'email'            => $user->email,
        'name'             => $user->name,
        'current-password' => 'zefrdsfeazdsqf',
        'password'         => '12345678',
        'confirmation'     => '12345678',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->put('/my-account/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('current-password')->assertSessionMissing('userUpdateSuccess');

    // Destruction de l'utilisateur
    User::destroy($user->id);

    //? Avec un mot de passe et une confirmation différente
    // On crée un utilisateur
    $user = initialize_user(false, false);

    // Données à envoyer
    $dataToSend = [
        'email'            => $user->email,
        'name'             => $user->name,
        'current-password' => '123456',
        'password'         => 'rzfgdsqfd',
        'confirmation'     => '12345678',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->put('/my-account/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('password')->assertSessionMissing('userUpdateSuccess');

    // Destruction de l'utilisateur
    User::destroy($user->id);

    //? Avec un nom d'utilisateur déjà existant
    // Utilisateur déjà existant
    $existingUser = initialize_user(false, false);

    // On crée un utilisateur
    $user = initialize_user(false, false);

    // Données à envoyer
    $dataToSend = [
        'email'            => $existingUser->email,
        'name'             => $existingUser->name,
        'current-password' => '123456',
        'password'         => '12345678',
        'confirmation'     => '12345678',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->put('/my-account/update', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors(['email', 'name'])->assertSessionMissing('userUpdateSuccess');

    // Destruction de l'utilisateur
    User::destroy($user->id);
    User::destroy($existingUser->id);
});

test('deleting account', function () {
    //? Mot de passe valide
    // On crée un utilisateur
    $user = initialize_user(false, false);

    // Données à envoyer
    $dataToSend = [
        'delete-account-password' => '123456',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->delete('/my-account/destroy', $dataToSend);
    $response->assertStatus(302)->assertSessionMissing('transactionError');

    //? Utilisateur banni
    // On crée un utilisateur
    $user = initialize_user(true, false);

    // Données à envoyer
    $dataToSend = [
        'delete-account-password' => '123456',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->delete('/my-account/destroy', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('badUser')->assertSessionMissing('userDeletionSuccess');
    User::destroy($user->id);

    //? Mot de passe invalide
    // On crée un utilisateur
    $user = initialize_user(false, false);

    // Données à envoyer
    $dataToSend = [
        'delete-account-password' => 'gtgrsfgedezq',
    ];

    // Accès à la route
    $response = $this->actingAs($user)->delete('/my-account/destroy', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('delete-account-password')->assertSessionMissing(
        'userDeletionSuccess'
    );
    User::destroy($user->id);
});

test('access admin users list', function () {
    // On sélectionne un utilisateur au hasard qui n'est pas banni
    $user = User::where('is_banned', false)->where('role', User::ROLE_ADMIN)->inRandomOrder()->first();

    // Si pas d'utilisateur
    if (!$user) {
        // Création d'un admin
        $faker = Faker::create();
        $newName = $faker->firstName() . ' ' . $faker->lastName();
        $mail = $faker->email();
        $user = User::factory()->create(
            [
                'name'              => $newName,
                'email'             => $mail,
                'password'          => bcrypt('123456'),
                'role'              => User::ROLE_ADMIN,
                'is_banned'         => false,
                'email_verified_at' => now()
            ]
        );
    }

    $typeList = rand(0, 1);
    $response = $this->actingAs($user)->get("/admin/users/index/$typeList");
    $response->assertStatus(200);

    //? Utilisateur non admin
    $nonAdminUser = initialize_user(false, false);
    $typeList = rand(0, 1);
    $response = $this->actingAs($nonAdminUser)->get("/admin/users/index/$typeList");
    $response->assertStatus(302)->assertSessionHasErrors('badUser');

    //? Utilisateur banni
    $nonAdminUser->role = User::ROLE_ADMIN;
    $nonAdminUser->is_banned = true;
    $nonAdminUser->save();
    $response = $this->actingAs($nonAdminUser)->get("/admin/users/index/$typeList");
    $response->assertStatus(302)->assertSessionHasErrors('badUser');
});

test('access admin users list with bad type', function () {
    $user = initialize_user(false, true);
    $typeList = 500;
    $response = $this->actingAs($user)->get("/admin/users/index/$typeList");
    $response->assertStatus(302)->assertSessionHasErrors('badType');
});

test('ban user in list', function () {
    //? Ban Valide
    $userToBan = initialize_user(false, false);
    $adminUser = initialize_user(false, true);

    $dataToSend = [
        'typelist' => rand(0, 1),
        'userid'   => $userToBan->id,
    ];
    $response = $this->actingAs($adminUser)->delete('/admin/users/ban', $dataToSend);
    $response->assertStatus(302)->assertSessionHas('deletionSuccess');
});

test('ban inexisting user in list', function () {
    //? Ban Valide
    $adminUser = initialize_user(false, true);

    $dataToSend = [
        'typelist' => rand(0, 1),
        'userid'   => User::orderBy('id', 'DESC')->first()->id + 1,
    ];
    $response = $this->actingAs($adminUser)->delete('/admin/users/ban', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('userid');
});

test('ban admin in list', function () {
    //? Ban Valide
    $adminUser = initialize_user(false, true);
    $adminToBan = initialize_user(false, true);

    $dataToSend = [
        'typelist' => rand(0, 1),
        'userid'   => $adminToBan->id,
    ];
    $response = $this->actingAs($adminUser)->delete('/admin/users/ban', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors('deletionError');
});

test('moderate opinion', function () {
    //? Ban Valide
    $adminUser = initialize_user(false, true);
    $faker = Faker::create();
    $newName = $faker->firstName() . ' ' . $faker->lastName();
    $mail = $faker->email();

    // Création d'un utilisateur avec des opinions sur des recettes
    $userOpinionToModerate = User::factory()->hasOpinions(3)->create(
        [
            'name'              => $newName,
            'email'             => $mail,
            'password'          => bcrypt('123456'),
            'role'              => User::ROLE_USER,
            'is_banned'         => false,
            'email_verified_at' => now()
        ]
    );

    $dataToSend = [
        'typelist'  => rand(0, 1),
        'opinionid' => RecipeOpinion::whereBelongsTo($userOpinionToModerate)->inRandomOrder()->first()->id,
        'destroy'   => rand(0, 1),
    ];
    $response = $this->actingAs($adminUser)->delete('/admin/users/moderate', $dataToSend);
    $response->assertStatus(302)->assertSessionHas('deletionSuccess');
});

test('moderate opinion with bad data', function () {
    //? Ban Valide
    $adminUser = initialize_user(false, true);
    $faker = Faker::create();
    $newName = $faker->firstName() . ' ' . $faker->lastName();
    $mail = $faker->email();

    // Création d'un utilisateur avec des opinions sur des recettes
    $userOpinionToModerate = User::factory()->hasOpinions(3)->create(
        [
            'name'              => $newName,
            'email'             => $mail,
            'password'          => bcrypt('123456'),
            'role'              => User::ROLE_USER,
            'is_banned'         => false,
            'email_verified_at' => now()
        ]
    );

    $dataToSend = [
        'typelist'  => 50,
        'opinionid' => RecipeOpinion::orderBy('id', 'DESC')->first()->id + 1,
        'destroy'   => 'qsdsd',
    ];
    $response = $this->actingAs($adminUser)->delete('/admin/users/moderate', $dataToSend);
    $response->assertStatus(302)->assertSessionHasErrors(['typelist', 'opinionid', 'destroy']);
});
