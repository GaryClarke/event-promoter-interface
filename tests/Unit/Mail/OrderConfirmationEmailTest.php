<?php

namespace Tests\Unit\Mail;

use App\Order;
use App\Mail\OrderConfirmationEmail;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase {

    /** @test */
    function email_contains_a_link_to_the_order_confirmation_page()
    {
        // ARRANGE
        // An order
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        // An order confirmation email
        $email = new OrderConfirmationEmail($order);

        // ACT
        // Get the rendered email
        $rendered = $this->render($email);

        // In laravel 5.5
//        $email->render();

        // ASSERT
        // The email contains the link
        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }


    /** @test */
    function email_has_a_subject()
    {
        // ARRANGE
        // An order
        $order = factory(Order::class)->make();

        // An order confirmation email
        $email = new OrderConfirmationEmail($order);

        // ASSERT
        // The email has a subject
        $this->assertEquals('Your Ticketbeast Order', $email->build()->subject);
    }

    private function render($mailable)
    {
        $mailable->build();

        return view($mailable->view, $mailable->buildViewData())->render();
    }
}