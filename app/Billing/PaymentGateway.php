<?php

namespace App\Billing;

interface PaymentGateway {

    public function charge($amount, $token, $destinationAccountId);

    public function getValidTestToken();
}