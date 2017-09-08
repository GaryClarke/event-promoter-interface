<?php

namespace Tests\Unit;

use App\HashidsTicketCodeGenerator;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HashidsTicketCodeGeneratorTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function ticket_codes_are_at_least_6_characters_long()
    {
        // ARRANGE
        $ticketCodeGenerator = new HashidsTicketCodeGenerator();

        // ACT
        // Generate the ticket code
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        // ASSERT
        // Code is min 6 chars
        $this->assertTrue(strlen($code) >= 6);
    }

    /** @test */
    function ticket_codes_can_only_contain_uppercase_letters()
    {
        // ARRANGE
        $ticketCodeGenerator = new HashidsTicketCodeGenerator();

        // ACT
        // Generate the ticket code
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        // ASSERT
        // Code is min 6 chars
        $this->assertRegExp('/^[A-Z]+$/', $code);
    }

    /** @test */
    function ticket_codes_for_the_same_ticket_id_are_the_same()
    {
        // ARRANGE
        $ticketCodeGenerator = new HashidsTicketCodeGenerator();

        // ACT
        // Generate the ticket code
        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        // ASSERT
        // Code is min 6 chars
        $this->assertEquals($code1, $code2);
    }

    /** @test */
    function ticket_codes_for_different_ticket_ids_are_different()
    {
        // ARRANGE
        $ticketCodeGenerator = new HashidsTicketCodeGenerator();

        // ACT
        // Generate the ticket code
        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        // ASSERT
        // Code is min 6 chars
        $this->assertNotEquals($code1, $code2);
    }

    /** @test */
    function ticket_codes_generated_with_different_salts_are_different()
    {
        // ARRANGE
        $ticketCodeGenerator1 = new HashidsTicketCodeGenerator('testsalt1');
        $ticketCodeGenerator2 = new HashidsTicketCodeGenerator('testsalt2');

        // ACT
        // Generate the ticket code
        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));


        // ASSERT
        // Code is min 6 chars
        $this->assertNotEquals($code1, $code2);
    }
}