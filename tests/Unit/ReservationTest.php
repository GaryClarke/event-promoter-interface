<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function calculating_the_total_cost()
    {
        // ARRANGE
        // Collection of tickets
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);


        // ACT
        // Create a ticket reservation
        $reservation = new Reservation($tickets, 'john@example.com');

        // ASSERT
        // reservation->totalCost() method returns correct cost of 3 tickets
        $this->assertEquals(3600, $reservation->totalCost());
    }


    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        // ARRANGE
        // Create an collection of tickets - use spies
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class)
        ]);

        // A reservation made up of the tickets
        $reservation = new Reservation($tickets, 'john@example.com');

        // ACT
        // Cancel the reservation
        $reservation->cancel();

        // ASSERT
        // The release method was called by each ticket
        foreach ($tickets as $ticket)
        {
            $ticket->shouldHaveReceived('release');
        }
    }


    /** @test */
    function retrieving_the_reservations_tickets()
    {
        // ARRANGE
        // Collection of tickets
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);


        // ACT
        // Create a ticket reservation
        $reservation = new Reservation($tickets, 'john@example.com');

        // ASSERT
        // Tickets can be retrieved off the reservation
        $this->assertEquals($tickets, $reservation->tickets());
    }


    /** @test */
    function retrieving_the_reservers_email()
    {
        // ARRANGE
        // Create a ticket reservation
        $reservation = new Reservation(collect(), 'john@example.com');

        // ASSERT
        // Reservers email can be retrieved from the reservation
        $this->assertEquals('john@example.com', $reservation->email());
    }


    /** @test */
    function completing_a_reservation()
    {
        // ARRANGE
        // Create a reservation
        $concert = factory(Concert::class)->create(['ticket_price' => 1200]);

        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $paymentGateway = new FakePaymentGateway;

        // ACT
        // Complete the reservation
        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        // ASSERT
        // Order contains customer email
        $this->assertEquals('john@example.com', $order->email);

        // Order contains 3 tickets
        $this->assertEquals(3, $order->ticketQuantity());

        // Order contains the correct amount
        $this->assertEquals(3600, $order->amount);

        $this->assertEquals(3600, $paymentGateway->totalCharges());

        $this->assertEquals(3600, $paymentGateway->totalChargesFor('test_acct_1234'));
    }
}