<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model {

    protected $guarded = [];

    protected $dates = ['date'];


    /**************************************** RELATIONSHIPS ****************************************/


    public function user()
    {
        return $this->belongsTo(User::class);
    }





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
        return $this->belongsToMany(Order::class, 'tickets');
    }


    /**
     * Check that an order exists for a customer
     *
     * @param $email
     * @return mixed
     */
    public function hasOrderFor($email)
    {
        return $this->orders()->where('email', $email)->exists();
    }


    /**
     * Get orders for a particular user
     *
     * @param $email
     * @return mixed
     */
    public function ordersFor($email)
    {
        return $this->orders()->where('email', $email)->get();
    }


    /**
     * A concert has many tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    /**
     * Reserve a number of tickets
     *
     * @param $quantity
     * @return mixed
     */
    public function reserveTickets($quantity, $email)
    {
        $tickets = $this->findTickets($quantity)->each(function($ticket) {

            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }


    /**
     * Find available concert tickets
     *
     * @param $quantity
     * @return mixed
     */
    public function findTickets($quantity)
    {
        // Find tickets
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity)
        {
            throw new NotEnoughTicketsException;
        }

        return $tickets;
    }


    /**
     * Add tickets to the concert
     *
     * @param $quantity
     * @return $this
     */
    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $item)
        {
            $this->tickets()->create([]);
        }

        return $this;
    }


    /**
     * Get the number of tickets remaining
     *
     * @return mixed
     */
    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
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
