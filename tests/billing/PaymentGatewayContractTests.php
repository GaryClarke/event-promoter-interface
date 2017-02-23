<?php

trait PaymentGatewayContractTests {

    abstract protected function getPaymentGateway();

    /** @test */
    function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {

            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000, 4000], $newCharges->all());
    }


    /** @test */
    function charges_with_a_valid_payment_token_are_successful()
    {
        // ARRANGE
        // Create a new StripePayementGateway
        $paymentGateway = $this->getPaymentGateway();

        // ACT
        // Create a new charge for some amount using a valid token
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {

            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        // ASSERT
        // Only one charge created
        $this->assertCount(1, $newCharges);

        // Verify that the charge was completed successfully
        $this->assertEquals(2500, $newCharges->sum());
    }
}