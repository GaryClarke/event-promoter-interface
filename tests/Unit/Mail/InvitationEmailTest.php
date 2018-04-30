<?php

namespace Tests\Unit\Mail;

use App\Invitation;
use Tests\TestCase;
use App\Mail\InvitationEmail;


class InvitationEmailTest extends TestCase {

    /** @test */
    function email_contains_a_link_to_accept_the_invitation()
    {
        $this->withExceptionHandling();

        $invitation = factory(Invitation::class)->make([
            'email' => 'john@example.com',
            'code'  => 'TESTCODE1234'
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertContains(url('/invitations/TESTCODE1234'), $email->render());
    }


    /** @test */
    function email_has_the_correct_subject()
    {
        $this->withExceptionHandling();

        $invitation = factory(Invitation::class)->make();

        $email = new InvitationEmail($invitation);

        $this->assertEquals("You're invited to join EPI!", $email->build()->subject);
    }
}
