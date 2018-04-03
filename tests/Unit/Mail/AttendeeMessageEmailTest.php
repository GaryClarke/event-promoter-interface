<?php

namespace Tests\Unit\Mail;

use Tests\TestCase;
use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;

class AttendeeMessageEmailTest extends TestCase {

    /** @test */
    function email_has_correct_subject_and_message()
    {
        $this->withoutExceptionHandling();

        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => 'My message'
        ]);

        $email = new AttendeeMessageEmail($message);

        $this->assertEquals('My subject', $email->build()->subject);
        $this->assertEquals('My message', trim($this->render($email)));
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function render($mailable)
    {
        $mailable->build();

        return view($mailable->textView, $mailable->buildViewData())->render();
    }
}