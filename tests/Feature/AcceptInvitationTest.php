<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    function viewing_an_unused_invitation()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234'
        ]);

        // ACT
        // View the invitations code page
        $response = $this->get('/invitations/TESTCODE1234');

        // ASSERT
        $response->assertStatus(200);

        $response->assertViewIs('invitations.show');

        $this->assertTrue($response->data('invitation')->is($invitation));
    }


    /** @test */
    function viewing_a_used_invitation()
    {
        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code'    => 'TESTCODE1234',
        ]);

        // ACT
        // View the invitations test code page
        $response = $this->get('/invitations/TESTCODE1234');

        // ASSERT
        // 404 because invitation is used
        $response->assertStatus(404);
    }


    /** @test */
    function viewing_an_invitation_that_does_not_exist()
    {
        // ACT
        // Try to view an invitation that does not exist
        $response = $this->get('/invitations/TESTCODE1234');

        // ASSERT
        // 404 because invitation is used
        $response->assertStatus(404);
    }


    /** @test */
    function registering_with_a_valid_invitation_code()
    {
        $this->withoutExceptionHandling();

        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        // ACT
        // Register
        $response = $this->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // redirected to backstage
        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1, User::count());

        $user = User::first();

        $this->assertAuthenticatedAs($user);

        $this->assertEquals('john@example.com', $user->email);

        $this->assertTrue(Hash::check('secret', $user->password));

        $this->assertTrue($invitation->fresh()->user->is($user));
    }


    /** @test */
    function registering_with_a_used_invitation_code()
    {
        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code'    => 'TESTCODE1234',
        ]);

        // Interim check that there is only one user
        $this->assertEquals(1, User::count());

        // ACT
        // Register using a used invitation code
        $response = $this->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // 404 status
        $response->assertStatus(404);

        // Still only one user
        $this->assertEquals(1, User::count());
    }


    /** @test */
    function registering_with_a_non_existent_invitation_code()
    {
        // ACT
        // Register using a used invitation code
        $response = $this->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // 404 status
        $response->assertStatus(404);

        // Still only one user
        $this->assertEquals(0, User::count());
    }


    /** @test */
    function email_is_required()
    {
        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        // ACT
        // Register using a used invitation code
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => '',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // Redirect
        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('email');

        // No user created
        $this->assertEquals(0, User::count());
    }


    /** @test */
    function email_must_be_an_email()
    {
        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        // ACT
        // Register using a used invitation code
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => 'notanemail',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // Redirect
        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('email');

        // No user created
        $this->assertEquals(0, User::count());
    }


    /** @test */
    function email_must_be_unique()
    {
        $existingUser = factory(User::class)->create(['email' => 'john@example.com']);

        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        // Interim check that 1 user exists
        $this->assertEquals(1, User::count());

        // ACT
        // Register using a used invitation code
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => 'john@example.com',
            'password'        => 'secret',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // Redirect
        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('email');

        // No user created
        $this->assertEquals(1, User::count());
    }


    /** @test */
    function password_is_required()
    {
        // ARRANGE
        // An invitation
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code'    => 'TESTCODE1234',
        ]);

        // ACT
        // Register using a used invitation code
        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email'           => 'john@example.com',
            'password'        => '',
            'invitation_code' => 'TESTCODE1234'
        ]);

        // ASSERT
        // Redirect
        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('password');

        // No user created
        $this->assertEquals(0, User::count());
    }

}
