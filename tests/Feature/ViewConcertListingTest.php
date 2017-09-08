<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function user_can_view_a_published_concert_listing()
    {
        // Arrange
        // Create the concert
        $concert = factory(Concert::class)->states('published')->create([
            'title'                  => 'The Red Chord',
            'subtitle'               => 'with Animosity and Lethargy',
            'date'                   => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price'           => 3250,
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Example Lane',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555'
        ]);

        // Act
        // View the concert listing
        $response = $this->get('/concerts/' . $concert->id);

        // Assert
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON 17916');
        $response->assertSee('For tickets, call (555) 555-5555');
        $response->assertStatus(200);
    }


    /** @test */
    function user_cannot_view_unpublished_listings()
    {
        // Arrange
        $concert = factory(Concert::class)->states('unpublished')->create();

        // Act
        // View the concert listing
        $response = $this->get('/concerts/' . $concert->id);

        // Assert
        // 404 received - not viewable
        $response->assertStatus(404);
    }

}
