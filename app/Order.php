<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Facades\OrderConfirmationNumber;

class Order extends Model {

    protected $guarded = [];


    public static function forTickets($tickets, $email, $charge)
    {
        // Create and return order
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email'               => $email,
            'amount'              => $charge->amount(),
            'card_last_four' => $charge->cardLastFour()
        ]);

        $order->tickets()->saveMany($tickets);

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
            'ticket_quantity'     => $this->ticketQuantity(),
            'amount'              => $this->amount
        ];
    }


    public static function findByConfirmationNumber($confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }
}
