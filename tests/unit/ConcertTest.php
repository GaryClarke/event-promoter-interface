<?php

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
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
    function can_order_concert_tickets()
    {
        // Create a concert
        $concert = factory(Concert::class)->create();
        $concert->addTickets(3);

        // Order tickets
        $order = $concert->orderTickets('jane@example.com', 3);

        // Assert
        // An order has been created
        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
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

        $concert->addTickets(50);

        // ACT
        // Order 30 tickets
        $concert->orderTickets('jane@example.com', 30);

        // ASSERT
        // 20 tickets should remain
        $this->assertEquals(20, $concert->ticketsRemaining());
    }


    /** @test */
    function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        // ARRANGE
        // Create a concert with 10 available tickets
        $concert = factory(Concert::class)->create();

        $concert->addTickets(10);

        // ACT
        // Try to order more than 10 tickets
        try
        {
            $concert->orderTickets('jane@example.com', 11);

        } catch (NotEnoughTicketsException $notEnoughTicketsException)
        {
            $order = $concert->orders()->where('email', 'jane@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(10, $concert->ticketsRemaining());

            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }


    /** @test */
    function cannot_order_tickets_that_have_already_been_purchased()
    {
        // ARRANGE
        // Create a concert with 10 available tickets
        $concert = factory(Concert::class)->create();

        $concert->addTickets(10);

        // 8 tickets are purchased
        $concert->orderTickets('jane@example.com', 8);

        // ACT
        // Try to purchase more tickets than available
        try
        {
            $concert->orderTickets('john@example.com', 3);

        } catch (NotEnoughTicketsException $notEnoughTicketsException)
        {
            $johnsOrder = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($johnsOrder);
            $this->assertEquals(2, $concert->ticketsRemaining());

            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining');
    }
}