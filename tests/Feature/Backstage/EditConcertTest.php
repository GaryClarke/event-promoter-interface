<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase {

    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        TestResponse::macro('data', function ($key)
        {

            return $this->original->getData()[$key];
        });
    }

    /** @test */
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);

        $this->assertTrue($response->data('concert')->is($concert));
    }


    /** @test */
    function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }


    /** @test */
    function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user = factory(User::class)->create();

        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }


    /** @test */
    function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }


    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert()
    {
        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }


    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }


    /** @test */
    function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'Old title',
            'subtitle'               => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date'                   => Carbon::parse('2017-01-01 5.00pm'),
            'venue'                  => 'Old venue',
            'venue_address'          => 'Old address',
            'city'                   => 'Old city',
            'state'                  => 'Old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional information',
            'date'                   => '2018-12-12',
            'time'                   => '8.00pm',
            'venue'                  => 'New venue',
            'venue_address'          => 'New address',
            'city'                   => 'New city',
            'state'                  => 'New state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertRedirect("/backstage/concerts");

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-12 8.00pm'), $concert->date);
            $this->assertEquals('New venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
        });
    }
}