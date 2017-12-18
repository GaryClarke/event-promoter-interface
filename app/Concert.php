<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
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
     * A concert belongs to a user
     *
     * In this instance the concert promoter / publisher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function attendeeMessages()
    {
        return $this->hasMany(AttendeeMessage::class);
    }


    /**
     * Concert orders
     *
     * @return Order
     */
    public function orders()
    {
        return Order::whereIn('id', $this->tickets()->pluck('order_id'));
    }


    /**
     * Check that an order exists for a customer
     *
     * @param $email
     * @return mixed
     */
    public function hasOrderFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->exists();
    }


    /**
     * Get orders for a particular user
     *
     * @param $email
     * @return mixed
     */
    public function ordersFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->get();
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


    /**
     * Get the number of tickets sold
     *
     * @return mixed
     */
    public function ticketsSold()
    {
        return $this->tickets()->sold()->count();
    }


    /**
     * Return the total number of tickets
     *
     * @return int
     */
    public function totalTickets()
    {
        return $this->tickets()->count();
    }


    /**
     * Percentage sold out
     *
     * @return string
     */
    public function percentSoldOut()
    {
//        return $this->ticketsSold() / $this->totalTickets();
        return number_format(($this->ticketsSold() / $this->totalTickets()) * 100, 2);
    }


    public function revenueInDollars()
    {
        return $this->orders()->sum('amount') / 100;
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


    /**
     * Check whether a concert has been published
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->published_at !== null;
    }


    /**
     * Publish a concert
     */
    public function publish()
    {
        $this->update(['published_at' => $this->freshTimestamp()]);

        $this->addTickets($this->ticket_quantity);
    }
}
