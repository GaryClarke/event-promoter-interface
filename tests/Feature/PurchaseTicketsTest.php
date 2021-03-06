<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;
use App\Facades\TicketCode;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Support\Facades\Mail;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class PurchaseTicketsTest extends TestCase {

    use DatabaseMigrations;

    protected $paymentGateway;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;

        $this->app->instance(PaymentGateway::class, $this->paymentGateway);

        Mail::fake();
    }

    /**
     * Standard json call used in each test
     *
     * @param $concert
     * @param $params
     */
    private function orderTickets($concert, $params)
    {
        $savedRequest = $this->app['request'];

        $this->response = $this->json('POST', "/concerts/{$concert->id}/orders", $params);

        $this->app['request'] = $savedRequest;
    }


    private function assertResponseStatus($status)
    {
        $this->response->assertStatus($status);
    }


    private function seeJsonSubset($data)
    {
        $this->response->assertJson($data);
    }


    private function decodeResponseJson()
    {
        return $this->response->decodeResponseJson();
    }


    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);

        // Validation requires email
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }


    /** @test */
    function customer_can_purchase_tickets_to_a_published_concert()
    {
        $this->disableExceptionHandling();

        Mail::fake();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');

        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

        // ARRANGE
        // Create a concert
        $concert = \ConcertFactory::createPublished([
            'ticket_price'    => 3250,
            'ticket_quantity' => 3,
        ]);


        // ACT
        // Purchase tickets
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Interim created concert check
        $this->assertResponseStatus(201);

        $this->seeJsonSubset([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'john@example.com',
            'amount'              => 9750,
            'tickets'             => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ]);

        // ASSERT
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that the order exists for the customer
        $this->assertTrue($concert->hasOrderFor('john@example.com'));

        $order = $concert->ordersFor('john@example.com')->first();

        // And that the order ticket-count is 3
        $this->assertEquals(3, $order->ticketQuantity());

        // An order confirmation email was sent
        Mail::assertSent(OrderConfirmationEmail::class, function ($mail) use ($order)
        {
            return $mail->hasTo('john@example.com')
            && $mail->order->id == $order->id;
        });
    }


    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
//        $this->disableExceptionHandling();

        // ARRANGE
        // An unpublished concert
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

        // ACT
        // Purchase tickets
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // ASSERT
        // 404 - endoints to unpublished concerts should not exist
        $this->assertResponseStatus(404);

        // No orders have been created for the concert
        $this->assertFalse($concert->hasOrderFor('john@example.com'));

        // The customer has not been charged
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }


    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ])->addTickets(3);

        // ACT
        // Purchase tickets
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token'
        ]);

        // ASSERT
        // 422 response status
        $this->assertResponseStatus(422);

        // An order has not been created
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }


    /** @test */
    function cannot_purchase_more_tickets_than_remain()
    {
        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        // ACT
        // Order more tickets than available
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // ASSERT
        // 422 status - request cannot be processed
        $this->assertResponseStatus(422);

        // An order has not been created
        $this->assertFalse($concert->hasOrderFor('john@example.com'));

        // Customer not charged
        $this->assertEquals(0, $this->paymentGateway->totalCharges());

        // 50 tickets remain for the concert
        $this->assertEquals(50, $concert->ticketsRemaining());
    }


    /** @test */
    function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
    {
        $this->disableExceptionHandling();

        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 1200
        ])->addTickets(3);

        // Make a prior order request
        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert)
        {

            $this->orderTickets($concert, [
                'email'           => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token'   => $paymentGateway->getValidTestToken()
            ]);

            // ASSERT
            // 422 status - request cannot be processed
            $this->assertResponseStatus(422);

            // An order has not been created
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));

            // Customer not charged
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        // ACT
        // Order more tickets than available
        $this->orderTickets($concert, [
            'email'           => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // ASSERT
        // Make sure the customer was charged the correct amount
        $this->assertEquals(3600, $this->paymentGateway->totalCharges());

        // Make sure that the order exists for the customer
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));

        // And that the order ticket-count is 3
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }


    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        // Arrange
        // Concert
        $concert = factory(Concert::class)->states('published')->create();


        // Act
        // Visist the order tickets route without posting the email
        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('email');
    }


    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        // Arrange
        // Concert
        $concert = factory(Concert::class)->states('published')->create();

        // Act
        // Visist the order tickets route with an invalid email address
        $this->orderTickets($concert, [
            'email'           => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('email');
    }


    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        // Arrange
        // Concert
        $concert = factory(Concert::class)->states('published')->create();

        // Act
        // Visist the order tickets route without ticket quantity
        $this->orderTickets($concert, [
            'email'         => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('ticket_quantity');
    }


    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        // Arrange
        // Concert
        $concert = factory(Concert::class)->states('published')->create();

        // Act
        // Visist the order tickets route with a 0 ticket quantity
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        $this->assertValidationError('ticket_quantity');
    }


    /** @test */
    function payment_token_is_required()
    {
        // Arrange
        // Concert
        $concert = factory(Concert::class)->states('published')->create();

        // Act
        // Visist the order tickets route with a 0 ticket quantity
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 0
        ]);

        // Assert
        $this->assertValidationError('payment_token');
    }
}