<?php

use App\Billing\PaymentFailedException;
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
}