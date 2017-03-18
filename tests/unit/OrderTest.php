<?php


use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase {

    use DatabaseMigrations;


    /** @test */
    function creating_an_order_from_tickets_and_email_and_amount()
    {
        // ARRANGE
        // Concert with tickets
        $concert = factory(Concert::class)->create()->addTickets(5);

        // Interim check that 5 tickets remin before order
        $this->assertEquals(5, $concert->ticketsRemaining());

        // ACT
        // Create the ticket order
        $order = Order::forTickets($concert->findTickets(3), 'john@example.com', 3600);

        // ASSERT
        // Order contains customer email
        $this->assertEquals('john@example.com', $order->email);

        // Order contains 3 tickets
        $this->assertEquals(3, $order->ticketQuantity());

        // Order contains the correct amount
        $this->assertEquals(3600, $order->amount);

        // 2 tickets remain
        $this->assertEquals(2, $concert->ticketsRemaining());
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

        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());

        // ACT
        // Convert the order to an array
        $result = $order->toArray();

        // ASSERT
        // The order has been converted to an array
        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'jane@example.com',
            'ticket_quantity'     => 5,
            'amount'              => 6000
        ], $result);
    }
}