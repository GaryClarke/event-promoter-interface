<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Reservation;
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
            // Find tickets
            $tickets = $concert->reserveTickets(request('ticket_quantity'));

            $reservation = new Reservation($tickets);

            // charge customer
            $this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));

            // Create an order
            $order = Order::forTickets($tickets, request('email'), $reservation->totalCost());

            // Return a response
            return response()->json($order, 201);

        } catch (PaymentFailedException $paymentFailedException)
        {
            return response()->json([], 422);

        } catch (NotEnoughTicketsException $notEnoughTicketsException)
        {
            return response()->json([], 422);
        }
    }
}
