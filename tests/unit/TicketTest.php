<?php

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TicketTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function a_ticket_can_be_reserved()
    {
        // ARRANGE
        // Create a ticket
        $ticket = factory(Ticket::class)->create();

        // Interim ticket not reserved check
        $this->assertNull($ticket->reserved_at);

        // ACT
        // Reserve the ticket
        $ticket->reserve();

        // The ticket has a reserved_at value
        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    function a_ticket_can_be_released()
    {
        // ARRANGE
        // Concert with at least one ticket
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);

        // An order and a ticket
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();

        // Interim order / ticket relation test
        $this->assertEquals($order->id, $ticket->order_id);

        $this->seeInDatabase('tickets', [
            'order_id' => $order->id
        ]);


        // ACT
        // Release the ticket
        $ticket->release();

        // ASSERT
        // The ticket no longer belongs to an order
        $this->assertNull($ticket->fresh()->order_id);

        $this->notSeeInDatabase('tickets', [
            'order_id' => $order->id
        ]);

    }
}