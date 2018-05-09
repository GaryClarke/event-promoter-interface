<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StripeRedirectTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function test_that_route_is_hit_correctly()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/stripe-connect/authorize');

        $response->assertRedirect('https://connect.stripe.com/oauth/authorize');
    }
}