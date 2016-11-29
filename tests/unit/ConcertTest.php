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
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        // Act
        // Retrieve the formatted date
        $date = $concert->formatted_date;

        // Assert
        // Assert that the date is formatted as expected
        $this->assertEquals('December 1, 2016', $date);
    }
}