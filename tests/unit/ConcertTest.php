<?php

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function can_get_formatted_date()
    {
        // Arrange
        // Create a concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        // Assert
        // Assert that the date is formatted as expected
        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00')
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    function can_get_ticket_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    function concerts_with_a_published_at_date_are_published()
    {
        // ARRANGE
        // 3 concerts
        $publishedConcertA = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week')
        ]);

        $publishedConcertB = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week')
        ]);

        $unpublishedConcert = factory(Concert::class)->create([
            'published_at' => null
        ]);

        // ACT
        // Query the published concerts
        $publishedConcerts = Concert::published()->get();

        // ASSERT
        // Only the published concerts are returned
        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }


    /** @test */
    function can_add_tickets()
    {
        // ARRANGE
        // Create a concert
        $concert = factory(Concert::class)->create();

        // ACT
        // Call add tickets method on concert
        $concert->addTickets(50);

        // ASSERT
        $this->assertEquals(50, $concert->ticketsRemaining());
    }


    /** @test */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        // ARRANGE
        // Create a concert with 50 available tickets
        $concert = factory(Concert::class)->create();

        $concert->tickets()->saveMany(factory(Ticket::class, 30)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 20)->create(['order_id' => null]));

        // ASSERT
        // 20 tickets should remain
        $this->assertEquals(20, $concert->ticketsRemaining());
    }


    /** @test */
    function trying_to_reserve_more_tickets_than_remain_throws_an_exception()
    {
        // ARRANGE
        // Create a concert with 10 available tickets
        $concert = factory(Concert::class)->create()->addTickets(10);

        // ACT
        // Try to order more than 10 tickets
        try
        {
            $concert->reserveTickets(11, 'jane@example.com');

        } catch (NotEnoughTicketsException $notEnoughTicketsException)
        {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());

            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }


    /** @test */
    function can_reserve_available_tickets()
    {
        // Create a concert
        $concert = factory(Concert::class)->create()->addTickets(3);

        // Interim tickets remaining check
        $this->assertEquals(3, $concert->ticketsRemaining());

        // ACT
        // Reserve 2 tickets
        $reservation = $concert->reserveTickets(2, 'john@example.com');

        // ASSERT
        // 2 tickets reserved
        $this->assertCount(2, $reservation->tickets());

        // Reserver email can be retrieved
        $this->assertEquals('john@example.com', $reservation->email());

        // 1 ticket remains
        $this->assertEquals(1, $concert->ticketsRemaining());
    }


    /** @test */
    function cannot_reserve_tickets_that_have_already_been_purchsed()
    {
        // ARRANGE
        // Create a concert with 3 tickets
        $concert = factory(Concert::class)->create()->addTickets(3);

        $order = factory(Order::class)->create();

        $order->tickets()->saveMany($concert->tickets->take(2));

        // ACT
        try {

            // Try to reserve 2 tickets
            $concert->reserveTickets(2, 'john@example.com');

        } catch (NotEnoughTicketsException $notEnoughTicketsException)
        {
            $this->assertEquals(1, $concert->ticketsRemaining());

            // Return early to bypass the fail call
            return;
        }

        $this->fail('Reserving tickets succeeded even though the tickets were already sold');
    }


    /** @test */
    function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        // ARRANGE
        // Create a concert with 3 tickets
        $concert = factory(Concert::class)->create()->addTickets(3);

        // Order 2 of the tickets
        $concert->reserveTickets(2, 'john@example.com');

        // ACT
        try {

            // Try to reserve 2 tickets
            $concert->reserveTickets(2, 'jane@example.com');

        } catch (NotEnoughTicketsException $notEnoughTicketsException)
        {
            $this->assertEquals(1, $concert->ticketsRemaining());

            // Return early to bypass the fail call
            return;
        }

        $this->fail('Reserving tickets succeeded even though the tickets were already reserved');
    }
}