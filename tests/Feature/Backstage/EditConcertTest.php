<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase {

    use DatabaseMigrations;


    /**
     * Test arrange attributes
     *
     * @param array $overrides
     * @return array
     */
    private function oldAttributes($overrides = [])
    {
        return array_merge([
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
            'ticket_quantity'        => 5,
        ], $overrides);
    }

    /**
     * Valid update parameters
     *
     * @param array $overrides
     * @return array
     */
    private function validParams($overrides = [])
    {
        return array_merge([
            'title'                  => 'Old title',
            'subtitle'               => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'Old venue',
            'venue_address'          => 'Old address',
            'city'                   => 'Old city',
            'state'                  => 'Old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
            'ticket_quantity'        => '10',
        ], $overrides);
    }

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
            'ticket_quantity'        => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'New venue',
            'venue_address'          => 'New address',
            'city'                   => 'New city',
            'state'                  => 'New state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
            'ticket_quantity'        => '10',
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
            $this->assertEquals(10, $concert->ticket_quantity);
        });
    }


    /** @test */
    function promoters_cannot_edit_other_unpublished_concerts()
    {
//        $this->disableExceptionHandling();

        $user = factory(User::class)->create();

        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id'                => $otherUser->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(404);

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $otherUser->id,
        ]), $concert->fresh()->getAttributes());
    }


    /** @test */
    function promoters_cannot_edit_published_concerts()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('published')->create($this->oldAttributes([
            'user_id'                => $user->id,
        ]));

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(403);

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $user->id,
        ]), $concert->fresh()->getAttributes());
    }


    /** @test */
    function guests_cannot_edit_concerts()
    {
        // ARRANGE
        // A user
        $user = factory(User::class)->create();

        // A concert
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id'                => $user->id,
        ]));

        // Interim concert status check
        $this->assertFalse($concert->isPublished());

        // ACT
        // Attempt concert update
        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        // ASSERT
        $response->assertRedirect('/login');

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $user->id,
        ]), $concert->fresh()->getAttributes());
    }


    /** @test */
    function title_is_required()
    {
        // ARRANGE
        // A user
        $user = factory(User::class)->create();

        // An unpublished concert
        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id'                => $user->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'title' => '',
            ]));

        // Redirected to the edit page
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $user->id,
        ]), $concert->fresh()->getAttributes());
    }


    /** @test */
    function subtitle_is_optional()
    {
        $this->disableExceptionHandling();

        // ARRANGE
        // A user
        $user = factory(User::class)->create();

        // A concert
        $concert = factory(Concert::class)->create([
            'user_id'  => $user->id,
            'subtitle' => 'Old subtitle',
        ]);

        // Interim published check
        $this->assertFalse($concert->isPublished());

        // ACT
        // Attempt empty subtitle string
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'subtitle' => '',
        ]));

        // ASSERT
        // Redirect to concerts index page
        $response->assertRedirect("/backstage/concerts");

        // Null has been entered
        tap($concert->fresh(), function ($concert)
        {
            $this->assertNull($concert->subtitle);
        });
    }


    /** @test */
    function additional_information_is_optional()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'additional_information' => 'Old additional information',
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        // Attempt empty additional info string
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'additional_information' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts");

        // Additional info is null
        tap($concert->fresh(), function ($concert)
        {
            $this->assertNull($concert->additional_information);
        });
    }


    /** @test */
    function date_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date'    => Carbon::parse('2018-01-01 8:00pm'),
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        // Attempt updating to empty concert date
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'date' => '',
        ]));

        // ASSERT
        // Redirected to concert edit page
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        // Session has date error
        $response->assertSessionHasErrors('date');

        // Date remains unchanged
        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }


    /** @test */
    function date_must_be_a_valid_date()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date'    => Carbon::parse('2018-01-01 8:00pm'),
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        // Attempt inserting non valid value into date field
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'date' => 'not a date',
        ]));

        // ASSERT
        // Redirect to concert edit page
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        // Session has date error
        $response->assertSessionHasErrors('date');

        // Date remains unchanged
        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }


    /** @test */
    function time_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date'    => Carbon::parse('2018-01-01 8:00pm'),
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        // Attempt empty time string
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'time' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");


        $response->assertSessionHasErrors('time');


        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }


    /** @test */
    function time_must_be_a_valid_time()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date'    => Carbon::parse('2018-01-01 8:00pm'),
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'time' => 'not-a-time',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('time');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }


    /** @test */
    function venue_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'venue'   => 'Old venue',
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'venue' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('venue');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals('Old venue', $concert->venue);
        });
    }


    /** @test */
    function venue_address_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'       => $user->id,
            'venue_address' => 'Old address',
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'venue_address' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('venue_address');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals('Old address', $concert->venue_address);
        });
    }


    /** @test */
    function city_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'city'    => 'Old city',
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'city' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('city');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals('Old city', $concert->city);
        });
    }


    /** @test */
    function state_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'state'   => 'Old state',
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'state' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('state');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals('Old state', $concert->state);
        });
    }


    /** @test */
    function zip_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'zip'     => 'Old zip',
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'zip' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('zip');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals('Old zip', $concert->zip);
        });
    }


    /** @test */
    function ticket_price_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'      => $user->id,
            'ticket_price' => 5250,
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_price' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('ticket_price');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(5250, $concert->ticket_price);
        });
    }


    /** @test */
    function ticket_price_must_be_numeric()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'      => $user->id,
            'ticket_price' => 5250,
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_price' => 'not a price',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('ticket_price');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(5250, $concert->ticket_price);
        });
    }


    /** @test */
    function ticket_price_must_be_at_least_5()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'      => $user->id,
            'ticket_price' => 5250,
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_price' => '4.99',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('ticket_price');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(5250, $concert->ticket_price);
        });
    }


    /** @test */
    function ticket_quantity_is_required()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'         => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_quantity' => '',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }


    /** @test */
    function ticket_quantity_must_be_an_integer()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'         => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_quantity' => '7.8',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }


    /** @test */
    function ticket_quantity_must_be_at_least_one()
    {
        // ARRANGE
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'         => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        // ACT
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_quantity' => '0',
        ]));

        // ASSERT
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");

        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function ($concert)
        {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }
}