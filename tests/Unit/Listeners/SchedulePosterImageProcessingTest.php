<?php

namespace Tests\Unit\Listeners;

use App\Jobs\ProcessPosterImage;
use Tests\TestCase;
use App\Events\ConcertAdded;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;


class SchedulePosterImageProcessingTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    function it_queues_a_job_to_process_a_poster_image_if_a_poster_image_is_present()
    {
        // ARRANGE
        // A concert
        $concert = \ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);

        // A fake Queue
        Queue::fake();

        // ACT
        // Dispatch a ConcertAdded event
        ConcertAdded::dispatch($concert);

        // ASSERT
        // ProcessPosterImage is dispatched
        Queue::assertPushed(ProcessPosterImage::class, function ($job) use ($concert) {

            return $job->concert->is($concert);
        });
    }


    /** @test */
    function a_job_is_not_queued_if_a_poster_is_not_present()
    {
        // ARRANGE
        // A concert
        $concert = \ConcertFactory::createUnpublished([
            'poster_image_path' => null,
        ]);

        // A fake Queue
        Queue::fake();

        // ACT
        // Dispatch a ConcertAdded event
        ConcertAdded::dispatch($concert);

        // ASSERT
        // ProcessPosterImage is dispatched
        Queue::assertNotPushed(ProcessPosterImage::class);
    }
}
