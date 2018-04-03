<?php

namespace Tests\Feature;

use App\User;
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

}
