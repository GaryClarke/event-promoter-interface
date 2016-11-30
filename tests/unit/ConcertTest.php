<?php

use App\Concert;
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
}