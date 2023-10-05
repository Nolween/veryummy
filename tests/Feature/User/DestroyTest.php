<?php


use App\Models\User;

it('denies user destroy if user is not authentificated', function () {

    $response = $this->delete(route('my-account.destroy'));

    $response->assertStatus(403);
});


it('denies user destroy if user is banned', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 1]);

    $response = $this->actingAs($user)->delete(route('my-account.destroy'));

    $response->assertStatus(403);
});

it('destroy user', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);

    $response = $this->actingAs($user)->delete(route('my-account.destroy'));

    $response->assertRedirect(route('home'));
    expect(User::find($user->id))->toBeNull();
});

it('fails destroy if user does not exist', function () {
    $user = User::factory()->create(['role' => User::ROLE_USER, 'is_banned' => 0]);
    $user->delete();

    $response = $this->actingAs($user)->delete(route('my-account.destroy'));

    $response->assertStatus(404);
});
