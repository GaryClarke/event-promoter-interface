<?php

namespace Tests\Unit\Http\Middleware;

use App\User;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Middleware\ForceStripeAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function users_without_a_stripe_account_are_forced_to_connect_with_stripe()
    {
        $this->be(factory(User::class)->create([
           'stripe_account_id' => null
        ]));

        $middleware = new ForceStripeAccount;

        // ACT
        // Handle the request
        $response = $middleware->handle(new Request, function() {

            $this->fail('Next middleware was called when it should not have been.');
        });

        // ASSERT
        // Redirect response
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }


    /** @test */
    function users_with_a_stripe_account_can_continue()
    {
        // ARRANGE
        // User / actor
        $this->be(factory(User::class)->create([
            'stripe_account_id' => 'stripe_test_account_1234'
        ]));

        $request = new Request;

        $next = new class {

            public $called = false;

            public function __invoke($request)
            {
                $this->called = true;
                return $request;
            }
        };

        $middleware = new ForceStripeAccount;

        $response = $middleware->handle($request, $next);

        $this->assertTrue($next->called);

        $this->assertSame($response, $request);
    }
}
