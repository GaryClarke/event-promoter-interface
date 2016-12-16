<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends TestCase {

    /** @test */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }


    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        try
        {

            // ARRANGE
            // Fake payment gateway
            $paymentGateway = new FakePaymentGateway;

            // ACT
            // Charge
            $paymentGateway->charge(2500, 'invalid-payment-token');

        } catch (PaymentFailedException $exception)
        {

            return;
        }

        // ASSERT
        // If a paymentFailedException is not caught the test fails
        $this->fail('A PaymentFailedException was expected to be thrown / caught');
    }


    /** @test */
    function running_a_hook_before_the_first_charge()
    {
        // ARRANGE
        // Payment gateway
        $paymentGateway = new FakePaymentGateway;

        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan)
        {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        // ACT
        // Charge 2500
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        // ASSERT
        // Callback ran
        $this->assertEquals(1, $timesCallbackRan);

        // 2500 charged
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }

}