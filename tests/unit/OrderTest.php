<?php


use App\Concert;
use App\Order;
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
    function converts_order_to_an_array()
    {
        // ARRANGE
        // Concert with tickets
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(5);

        // An order
        $order = $concert->orderTickets('jane@example.com', 5);

        // ACT
        // Convert the order to an array
        $result = $order->toArray();

        // ASSERT
        // The order has been converted to an array
        $this->assertEquals([
            'email'           => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount'          => 6000
        ], $result);
    }

    /** @test */
    function tickets_are_released_when_an_order_is_cancelled()
    {
        // ARRANGE
        // Concert with tickets
        $concert = factory(Concert::class)->create()->addTickets(10);

        // An order
        $order = $concert->orderTickets('jane@example.com', 5);

        // Interim order existence check
        $this->assertEquals(5, $concert->ticketsRemaining());

        // ACT
        // Cancel the order
        $order->cancel();

        // ASSERT
        // Back to 10 available tickets
        $this->assertEquals(10, $concert->ticketsRemaining());

        $this->notSeeInDatabase('orders', [
            'id' => $order->id
        ]);


    }
}