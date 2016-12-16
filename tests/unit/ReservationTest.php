<?php

use App\Reservation;

class ReservationTest extends TestCase {

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
        $reservation = new Reservation($tickets);

        // ASSERT
        // reservation->totalCost() method returns correct cost of 3 tickets
        $this->assertEquals(3600, $reservation->totalCost());
    }
}