<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    /**
     * An order can be for multiple tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->update(['order_id' => null]);
        }

        $this->delete();
    }
}
