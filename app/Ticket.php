<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
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
        return $query->whereNull('order_id');
    }
}
