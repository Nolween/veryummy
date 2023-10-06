<?php


use App\Models\User;

it('deniers user to ban if user is not admin', function() {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->patch(route('admin-users.ban'), [
        'typelist' => 1,
        'userid'   => $user->id,
    ]);

    $response->assertStatus(403);
});

it("denies user to ban if user doesn't respect rules", function() {
     $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

     $response = $this->actingAs($user)->patch(route('admin-users.ban'), [
          'typelist' => 'test',
          'userid'   => 'test',
     ]);

     $response->assertStatus(302)
                 ->assertSessionHasErrors(['typelist','userid']);
    });

it('bans user', function() {
    $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_banned' => 0]);

//    Create th user to ban
    $userToBan = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->patch(route('admin-users.ban'), [
        'typelist' => 1,
        'userid'   => $userToBan->id,
    ]);

    $response->assertStatus(302)
             ->assertSessionHas('deletionSuccess');

    expect(User::find($userToBan->id)->is_banned)->toBe(1);
});
