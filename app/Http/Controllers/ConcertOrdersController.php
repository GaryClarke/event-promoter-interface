<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller {

    private $paymentGateway;

    /**
     * ConcertOrdersController constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }


    /**
     * Order tickets
     *
     * @param $concertId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email'           => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token'   => 'required'
        ]);


        try
        {
            // Charge the customer
            $this->paymentGateway->charge(request('ticket_quantity')
                * $concert->ticket_price, request('payment_token'));

            // Create an order
            $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

            return response()->json([], 201);

        } catch (PaymentFailedException $paymentFailedException)
        {
            return response()->json([], 422);
        }
    }
}
