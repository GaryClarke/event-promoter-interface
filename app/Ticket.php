<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {

    protected $guarded = [];

    /**************************************** QUERY SCOPES ****************************************/

    /**
     * Available tickets have a null order_id
     *
     * @param $query
     * @return mixed
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }


    /**************************************** RELATIONS ****************************************/


    /**
     * A ticket belongs to a concert
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }


    /**************************************** MODEL HELPERS ****************************************/

    /**
     * Reserve a ticket by setting its reserved_at column
     */
    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }


    /**
     * Release a ticket, making it available again
     */
    public function release()
    {
        $this->update(['order_id' => null]);
    }



    /**
     * Price of a ticket
     *
     * @return mixed
     */
    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }
}
