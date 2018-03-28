<?php

use App\Concert;

class ConcertFactory {

    /**
     * Create a published Concert
     *
     * @param $overrides
     * @return mixed
     */
    public static function createPublished($overrides = [])
    {
        $concert = factory(Concert::class)->create($overrides);

        $concert->publish();

        return $concert;
    }


    /**
     * Create an unpublished Concert
     *
     * @param array $overrides
     * @return mixed
     */
    public static function createUnpublished($overrides = [])
    {
        return factory(Concert::class)->states('unpublished')->create($overrides);
    }
}