<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Support\Collection;

class Order extends Model {

    protected $guarded = [];


    /**
     * Create an order from a Collection of tickets
     *
     * @param Collection $tickets
     * @param $email
     * @param $charge
     * @return mixed
     */
    public static function forTickets($tickets, $email, $charge)
    {
        // Create and return order
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email'               => $email,
            'amount'              => $charge->amount(),
            'card_last_four'      => $charge->cardLastFour()
        ]);

        $tickets->each->claimFor($order);

        return $order;
    }


    /**************************************** RELATIONS ****************************************/

    /**
     * An order can be for multiple tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    /**
     * An order belongs to a concert
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }


    /**
     * Number of tickets in the order
     *
     * @return mixed
     */
    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }


    /**
     * Convert the order to an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'confirmation_number' => $this->confirmation_number,
            'email'               => $this->email,
            'amount'              => $this->amount,
            'tickets'             => $this->tickets->map(function($ticket) {

                return ['code' => $ticket->code];
            })->all(),
        ];
    }


    public static function findByConfirmationNumber($confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }
}
