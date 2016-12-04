<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model {

    protected $guarded = [];

    protected $dates = ['date'];


    /**************************************** GETTERS ****************************************/

    /**
     * Format the concert date
     *
     * @return mixed
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }


    /**
     * Format the concert start time
     *
     * @return mixed
     */
    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }


    /**
     * Format the ticket price as a decimal
     *
     * @return string
     */
    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    /**************************************** RELATIONS ****************************************/


    /**
     * A concert can have many ticket orders
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    /**
     * Order concert tickets
     *
     * @param $email
     * @param $ticketQuantity
     * @return Model
     */
    public function orderTickets($email, $ticketQuantity)
    {
        $order = $this->orders()->create([
            'email' => $email
        ]);

        $tickets = $this->tickets()->take($ticketQuantity)->get();

        foreach ($tickets as $ticket)
        {
            $order->tickets()->save($ticket);
        }

        return $order;
    }


    /**
     * Add tickets for the concert
     *
     * @param $quantity
     */
    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $item)
        {
            $this->tickets()->create([]);
        }
    }


    /**
     * Get the number of tickets remaining
     *
     * @return mixed
     */
    public function ticketsRemaining()
    {
        return $this->tickets()->whereNull('order_id')->count();
    }


    /**************************************** QUERY SCOPES ****************************************/


    /**
     * Published concerts scope
     *
     * @param $query
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
