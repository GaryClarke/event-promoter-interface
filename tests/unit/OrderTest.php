<?php


use App\Billing\Charge;
use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase {

    use DatabaseMigrations;


    /** @test */
    function creating_an_order_from_tickets_and_email_and_charge()
    {
        // ARRANGE
        // 3 tickets
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        // ACT
        // Create the ticket order
        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        // ASSERT
        // Order contains customer email
        $this->assertEquals('john@example.com', $order->email);

        // Order contains the correct amount
        $this->assertEquals(3600, $order->amount);

        // Order contains correct card last 4
        $this->assertEquals('1234', $order->card_last_four);

        // claimFor() is called on the ticket class
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }


    /** @test */
    function retrieving_an_order_by_confirmation_number()
    {
        // ARRANGE
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFORMATION1234'
        ]);

        // ACT
        // Call the find bu confirmation number method
        $foundOrder = Order::findByConfirmationNumber('ORDERCONFORMATION1234');

        // ASSERT
        $this->assertEquals($order->id, $foundOrder->id);
    }


    /** @test */
    function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        try
        {
            Order::findByConfirmationNumber('NONEXISTENETCONFIRMATIONNUMBER');

        } catch (ModelNotFoundException $modelNotFoundException)
        {
            return;
        }

        $this->fail('An exception was not thrown despite a matching order not being found');
    }


    /** @test */
    function converts_order_to_an_array()
    {
        // ARRANGE
        // An order for 5 tickets
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'jane@example.com',
            'amount'              => 6000
        ]);

        $order->tickets()->saveMany([
            factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
        ]);

        // ACT
        // Convert the order to an array
        $result = $order->toArray();

        // ASSERT
        // The order has been converted to an array
        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'jane@example.com',
            'amount'              => 6000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ], $result);
    }
}