<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
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
    }

    /**
     * Standard json call used in each test
     *
     * @param $concert
     * @param $params
     */
    private function orderTickets($concert, $params)
    {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
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
        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ]);

        $concert->addTickets(10);

        // ACT
        // Purchase tickets
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        // Interim created concert check
        $this->assertResponseStatus(201);

        // ASSERT
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that the order exists for the customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }


    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
//        $this->disableExceptionHandling();

        // ARRANGE
        // An unpublished concert
        $concert = factory(Concert::class)->states('unpublished')->create();
        $concert->addTickets(3);

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
        $this->assertEquals(0, $concert->orders()->count());

        // The customer has not been charged
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }


    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
//        $this->disableExceptionHandling();

        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ]);
        $concert->addTickets(3);

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
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNull($order);
    }


    /** @test */
    function cannot_purchase_more_tickets_than_remain()
    {
//        $this->disableExceptionHandling();

        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create();

        // Set the amount of available tickets
        $concert->addTickets(50);

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
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNull($order);

        // Customer not charged
        $this->assertEquals(0, $this->paymentGateway->totalCharges());

        // 50 tickets remain for the concert
        $this->assertEquals(50, $concert->ticketsRemaining());

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