<?php

use App\Concert;
use App\Facades\TicketCode;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
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
        // Create a reserved ticket
        $ticket = factory(Ticket::class)->states('reserved')->create();

        // Interim reserved status check
        $this->assertNotNull($ticket->reserved_at);

        // ACT
        // Release the ticket
        $ticket->release();

        // ASSERT
        // Ticket released i.e. reserved_at set to null
        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    function a_ticket_can_be_claimed_for_an_order()
    {
        // ARRANGE
        // An order
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create(['code' => null]);

        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        // A ticket

        // ACT
        // Claim a ticket
        $ticket->claimFor($order);

        // ASSERT
        // Ticket saved to the order
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));

        // Ticket had expected ticket code generated
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}