<?php

namespace Tests\Feature;

use App\Models\RecipeOpinion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Faker\Factory as Faker;

class AccountTest extends TestCase
{
    /**
     * Undocumented function
     *
     * @param boolean $banned
     * @param boolean $admin
     * @return User
     */
    private function initialize_user(bool $banned = false, bool $admin = false): User
    {
        $faker = Faker::create();
        $newName = $faker->firstName() . ' ' . $faker->lastName();
        $mail = $faker->email();
        if ($admin == true) {
            // Création d'un rôle, nécessaire pour la création d'un utilisateur
            $role = Role::where('name', 'Administrateur')->first();
            if (!$role) {
                $role = Role::factory()->create(['name' => 'Administrateur']);
            }
        } else {
            // Création d'un rôle, nécessaire pour la création d'un utilisateur
            $role = Role::where('name', 'Utilisateur')->first();
            if (!$role) {
                $role = Role::factory()->create(['name' => 'Utilisateur']);
            }
        }
        // Création d'un utilisateur
        $user = User::factory()->create(['name' => $newName, 'email' => $mail, 'password' => bcrypt('123456'), 'role_id' => $role->id, 'is_banned' => $banned, 'email_verified_at' => now()]);

        return $user;
    }

    /**
     * Vérifier si le rôle d'admin existe
     *
     * @return Role $adminRole
     */
    private function check_admin_role_exists(): Role
    {

        // Le rôle d'administrateur existe t-il?
        $adminRole = Role::where('name', 'Administrateur')->first();
        // Si pas de rôle administrateur existant
        if (!$adminRole) {
            // Création d'un rôle d'admin
            $adminRole = Role::factory()->create(['name' => 'Administrateur']);
        }
        return $adminRole;
    }


    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_access_account_page()
    {
        //? Accès avec un compte valide
        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->inRandomOrder()->first();
        dump("Connexion avec l'utilisateur $user->name");
        $response = $this->actingAs($user)->get('/my-account');
        $response->assertStatus(200);
        // Ya t-il un utilisateur banni dans la DB?
        $userToDelete = false;
        $user = User::where('is_banned', true)->inRandomOrder()->first();
        // Si pas d'utilisateur banni
        if (!$user) {
            // Initialisation d'un compte banni
            $user = $this->initialize_user(true, false);
            $userToDelete = true;
        }
        //? Accès avec un compte banni
        dump("Connexion avec un compte utilisateur banni: $user->name");
        $response = $this->actingAs($user)->get('/my-account');
        $response->assertStatus(302)->assertSessionHasErrors('badUser');
        // Si un utilisateur a été créé pour l'occasion
        if ($userToDelete == true) {
            User::destroy($user->id);
        }
    }

    /**
     * Edition des informations de compte utilisateur
     *
     * @return void
     */
    public function test_edit_account()
    {
        //? Avec un compte valide
        // On crée un utilisateur
        $user = $this->initialize_user(false, false);
        dump("Connexion avec l'utilisateur $user->name");
        // Données à envoyer
        $dataToSend = [
            'email' => $user->email,
            'name' => $user->name,
            'current-password' => '123456',
            'password' => '12345678',
            'confirmation' => '12345678',
        ];
        // Accès à la route
        $response = $this->actingAs($user)->put('/my-account/edit', $dataToSend);
        $response->assertStatus(302)->assertSessionHasNoErrors()->assertSessionHas('userUpdateSuccess');
        // Destruction de l'utilisateur créé pour l'occasion
        User::destroy($user->id);

        //? Avec un compte banni
        // On crée un utilisateur
        $user = $this->initialize_user(true, false);

        dump("Connexion avec l'utilisateur banni $user->name");
        // Données à envoyer
        $dataToSend = [
            'email' => $user->email,
            'name' => $user->name,
            'current-password' => '123456',
            'password' => '12345678',
            'confirmation' => '12345678',
        ];
        // Accès à la route
        $response = $this->actingAs($user)->put('/my-account/edit', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('badUser')->assertSessionMissing('userUpdateSuccess');
        // Destruction de l'utilisateur
        User::destroy($user->id);


        //? Avec un mot de passe actuel erroné
        // On crée un utilisateur
        $user = $this->initialize_user(false, false);
        // Données à envoyer
        $dataToSend = [
            'email' => $user->email,
            'name' => $user->name,
            'current-password' => 'zefrdsfeazdsqf',
            'password' => '12345678',
            'confirmation' => '12345678',
        ];
        // Accès à la route
        $response = $this->actingAs($user)->put('/my-account/edit', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('current-password')->assertSessionMissing('userUpdateSuccess');
        // Destruction de l'utilisateur
        User::destroy($user->id);

        //? Avec un mot de passe et une confirmation différente
        // On crée un utilisateur
        $user = $this->initialize_user(false, false);
        // Données à envoyer
        $dataToSend = [
            'email' => $user->email,
            'name' => $user->name,
            'current-password' => '123456',
            'password' => 'rzfgdsqfd',
            'confirmation' => '12345678',
        ];
        // Accès à la route
        $response = $this->actingAs($user)->put('/my-account/edit', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('password')->assertSessionMissing('userUpdateSuccess');
        // Destruction de l'utilisateur
        User::destroy($user->id);


        //? Avec un nom d'utilisateur déjà existant
        // Utilisateur déjà existant
        $existingUser = $this->initialize_user(false, false);
        // On crée un utilisateur
        $user = $this->initialize_user(false, false);
        // Données à envoyer
        $dataToSend = [
            'email' => $existingUser->email,
            'name' => $existingUser->name,
            'current-password' => '123456',
            'password' => '12345678',
            'confirmation' => '12345678',
        ];
        // Accès à la route
        $response = $this->actingAs($user)->put('/my-account/edit', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors(['email', 'name'])->assertSessionMissing('userUpdateSuccess');
        // Destruction de l'utilisateur
        User::destroy($user->id);
        User::destroy($existingUser->id);
    }

    /**
     * Suppression de l'utilisateur via sa page
     *
     * @return void
     */
    public function test_deleting_account()
    {

        //? Mot de passe valide
        // On crée un utilisateur
        $user = $this->initialize_user(false, false);
        // Données à envoyer
        $dataToSend = [
            'delete-account-password' => '123456'
        ];
        // Accès à la route
        $response = $this->actingAs($user)->delete('/my-account/delete', $dataToSend);
        $response->assertStatus(302)->assertSessionHas('userDeletionSuccess')->assertSessionMissing('transactionError');

        //? Utilisateur banni
        // On crée un utilisateur
        $user = $this->initialize_user(true, false);
        // Données à envoyer
        $dataToSend = [
            'delete-account-password' => '123456'
        ];
        // Accès à la route
        $response = $this->actingAs($user)->delete('/my-account/delete', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('badUser')->assertSessionMissing('userDeletionSuccess');
        User::destroy($user->id);


        //? Mot de passe invalide
        // On crée un utilisateur
        $user = $this->initialize_user(false, false);
        // Données à envoyer
        $dataToSend = [
            'delete-account-password' => 'gtgrsfgedezq'
        ];
        // Accès à la route
        $response = $this->actingAs($user)->delete('/my-account/delete', $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('delete-account-password')->assertSessionMissing('userDeletionSuccess');
        User::destroy($user->id);
    }

    /**
     * Accès à la liste des utilisateurs
     *
     * @return void
     */
    public function test_access_admin_users_list()
    {
        //? Administrateur valide
        // Le rôle d'administrateur existe t-il?
        $adminRole = $this->check_admin_role_exists();

        // On sélectionne un utilisateur au hasard qui n'est pas banni
        $user = User::where('is_banned', false)->where('role_id', $adminRole->id)->inRandomOrder()->first();

        // Si pas d'utilisateur
        if (!$user) {
            // Création d'un admin
            $faker = Faker::create();
            $newName = $faker->firstName() . ' ' . $faker->lastName();
            $mail = $faker->email();
            $user = User::factory()->create(['name' => $newName, 'email' => $mail, 'password' => bcrypt('123456'), 'role_id' => $adminRole->id, 'is_banned' => false, 'email_verified_at' => now()]);
        }

        $typeList = rand(0, 1);
        $response = $this->actingAs($user)->get("/admin/users/list/$typeList");
        $response->assertStatus(200);

        //? Utilisateur non admin
        $nonAdminUser = $this->initialize_user(false, false);
        $typeList = rand(0, 1);
        $response = $this->actingAs($nonAdminUser)->get("/admin/users/list/$typeList");
        $response->assertStatus(302)->assertSessionHasErrors('badUser');

        //? Utilisateur banni
        $nonAdminUser->role_id = Role::where('name', 'Administrateur')->first()->id;
        $nonAdminUser->is_banned = true;
        $nonAdminUser->save();
        $response = $this->actingAs($nonAdminUser)->get("/admin/users/list/$typeList");
        $response->assertStatus(302)->assertSessionHasErrors('badUser');

    }

    
    /**
     * Accès à la liste des utilisateurs avec un mauvais type de liste
     *
     * @return void
     */
    public function test_access_admin_users_list_with_bad_type()
    {
        
        $user = $this->initialize_user(false, true);
        $typeList = 500;
        $response = $this->actingAs($user)->get("/admin/users/list/$typeList");
        $response->assertStatus(302)->assertSessionHasErrors('badType');
    }

    /**
     * Bannir un utilisateur
     *
     * @return void
     */
    public function test_ban_user_in_list() {

        //? Ban Valide
        $userToBan = $this->initialize_user(false, false);
        $adminUser = $this->initialize_user(false, true);

        $dataToSend = [
            'typelist' => rand(0,1),
            'userid' => $userToBan->id,
        ];
        $response = $this->actingAs($adminUser)->delete("/admin/users/ban", $dataToSend);
        $response->assertStatus(302)->assertSessionHas('deletionSuccess');

    }

    /**
     * Bannir un utilisateur inexistant
     *
     * @return void
     */
    public function test_ban_inexisting_user_in_list() {
        
        //? Ban Valide
        $adminUser = $this->initialize_user(false, true);

        $dataToSend = [
            'typelist' => rand(0,1),
            'userid' => User::orderBy('id', 'DESC')->first()->id + 1,
        ];
        $response = $this->actingAs($adminUser)->delete("/admin/users/ban", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('userid');

    }

    /**
     * Bannir un admin
     *
     * @return void
     */
    public function test_ban_admin_in_list() {
        
        //? Ban Valide
        $adminUser = $this->initialize_user(false, true);
        $adminToBan = $this->initialize_user(false, true);

        $dataToSend = [
            'typelist' => rand(0,1),
            'userid' => $adminToBan->id,
        ];
        $response = $this->actingAs($adminUser)->delete("/admin/users/ban", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors('deletionError');

    }


    public function test_moderate_opinion() {
        
        //? Ban Valide
        $adminUser = $this->initialize_user(false, true);
        $faker = Faker::create();
        $newName = $faker->firstName() . ' ' . $faker->lastName();
        $mail = $faker->email();

        // Création d'un utilisateur avec des opinions sur des recettes
        $userOpinionToModerate = User::factory()->hasOpinions(3)->create(['name' => $newName, 'email' => $mail, 'password' => bcrypt('123456'), 'role_id' => Role::where('name', 'Utilisateur')->first()->id, 'is_banned' => false, 'email_verified_at' => now()]);


        $dataToSend = [
            'typelist' => rand(0,1),
            'opinionid' => RecipeOpinion::whereBelongsTo($userOpinionToModerate)->inRandomOrder()->first()->id,
            'destroy' => rand(0, 1)
        ];
        $response = $this->actingAs($adminUser)->delete("/admin/users/moderate", $dataToSend);
        $response->assertStatus(302)->assertSessionHas('deletionSuccess');

    }


    public function test_moderate_opinion_with_bad_data() {
        
        //? Ban Valide
        $adminUser = $this->initialize_user(false, true);
        $faker = Faker::create();
        $newName = $faker->firstName() . ' ' . $faker->lastName();
        $mail = $faker->email();

        // Création d'un utilisateur avec des opinions sur des recettes
        $userOpinionToModerate = User::factory()->hasOpinions(3)->create(['name' => $newName, 'email' => $mail, 'password' => bcrypt('123456'), 'role_id' => Role::where('name', 'Utilisateur')->first()->id, 'is_banned' => false, 'email_verified_at' => now()]);


        $dataToSend = [
            'typelist' => 50,
            'opinionid' => RecipeOpinion::orderBy('id', 'DESC')->first()->id + 1,
            'destroy' => 'qsdsd'
        ];
        $response = $this->actingAs($adminUser)->delete("/admin/users/moderate", $dataToSend);
        $response->assertStatus(302)->assertSessionHasErrors(['typelist', 'opinionid', 'destroy']);

    }

    

}
