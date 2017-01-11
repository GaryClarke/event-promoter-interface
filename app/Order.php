<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $guarded = [];


    public static function forTickets($tickets, $email, $amount)
    {
        // Create and return order
        $order = self::create([
            'email'      => $email,
            'amount'     => $amount
        ]);

        foreach ($tickets as $ticket)
        {
            $order->tickets()->save($ticket);
        }

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
            'email'           => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount'          => $this->amount
        ];
    }
}
