<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find invitation by code
     *
     * @param $code
     * @return mixed
     */
    public static function findByCode($code)
    {
        return self::where('code', $code)->firstOrFail();
    }


    /**
     * Check whether the invitation has been used
     *
     * @return bool
     */
    public function hasBeenUsed()
    {
        return $this->user_id !== null;
    }
}
