<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests {

    abstract protected function getPaymentGateway();

    /** @test */
    function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {

            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 4000], $newCharges->map->amount()->all());
    }


    /** @test */
    function charges_with_a_valid_payment_token_are_successful()
    {
        // ARRANGE
        // Create a new StripePayementGateway
        $paymentGateway = $this->getPaymentGateway();

        // ACT
        // Create a new charge for some amount using a valid token
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {

            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        });

        // ASSERT
        // Only one charge created
        $this->assertCount(1, $newCharges);

        // Verify that the charge was completed successfully
        $this->assertEquals(2500, $newCharges->map->amount()->sum());
    }


    /** @test */
    function can_get_details_about_a_successful_charge()
    {
        $paymentGateway = $this->getPaymentGateway();

        $charge = $paymentGateway->charge(
            2500,
            $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER),
            'test_acct_1234'
        );

        $this->assertEquals(substr($paymentGateway::TEST_CARD_NUMBER, - 4), $charge->cardLastFour());
        $this->assertEquals('2500', $charge->amount());
        $this->assertEquals('test_acct_1234', $charge->destinationAccount());
    }


    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        // ARRANGE
        // Payment gateway
        $paymentGateway = $this->getPaymentGateway();

        // ACT
        // Charge
        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            try
            {
                $paymentGateway->charge(2500, 'invalid-payment-token', 'test_acct_1234');

            } catch (PaymentFailedException $exception)
            {
                return;
            }

            // If a paymentFailedException is not caught the test fails
            $this->fail('Charging with an invalid payment token did not throw a payment failed exception');
        });

        // ASSERT
        // No charges have been made
        $this->assertCount(0, $newCharges);
    }
}