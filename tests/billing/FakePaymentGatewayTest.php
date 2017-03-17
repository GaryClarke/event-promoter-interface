<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends TestCase {

    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
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
            $timesCallbackRan ++;
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