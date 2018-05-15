<?php

namespace Tests\Unit\Billing;

use Stripe\Charge;
use Stripe\Transfer;
use Tests\TestCase;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase {

    use DatabaseMigrations, PaymentGatewayContractTests;


    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    /** @test */
    function ninety_percent_of_the_payment_is_transferred_to_the_destination_account()
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));

        $lastStripeCharge = array_first(Charge::all(

            ['limit' => 1],
            ['api_key' => config('services.stripe.secret')]

        )['data']);

        $this->assertEquals(5000, $lastStripeCharge['amount']);

        $this->assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $lastStripeCharge['destination']);

        $transfer = Transfer::retrieve($lastStripeCharge['transfer'], ['api_key' => config('services.stripe.secret')]);

        $this->assertEquals(4500, $transfer['amount']);
    }
}