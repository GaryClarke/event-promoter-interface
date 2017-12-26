<?php

namespace Tests\Unit\Jobs;

use App\Mail\AttendeeMessageEmail;
use OrderFactory;
use ConcertFactory;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SendAttendeeMessageTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function it_sends_the_message_to_all_concert_attendees()
    {
        $this->disableExceptionHandling();

        Mail::fake();

        $concert = ConcertFactory::createPublished();
        $otherConcert = ConcertFactory::createPublished();

        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject'    => 'My subject',
            'message'    => 'My message'
        ]);

        $orderA = OrderFactory::createForConcert($concert, ['email' => 'alex@example.com']);
        $otherOrder = OrderFactory::createForConcert($otherConcert, ['email' => 'jane@example.com']);
        $orderA = OrderFactory::createForConcert($concert, ['email' => 'sam@example.com']);
        $orderA = OrderFactory::createForConcert($concert, ['email' => 'taylor@example.com']);

        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message)
        {
            return $mail->hasTo('alex@example.com')
            && $mail->attendeeMessage->is($message);
        });

        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message)
        {
            return $mail->hasTo('sam@example.com')
            && $mail->attendeeMessage->is($message);
        });

        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message)
        {
            return $mail->hasTo('taylor@example.com')
            && $mail->attendeeMessage->is($message);
        });

        Mail::assertNotSent(AttendeeMessage::class, function($mail) {

            return $mail->hasTo('jane@example.com');
        });
    }
}