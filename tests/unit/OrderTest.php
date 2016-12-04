<?php


use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function tickets_are_released_when_an_order_is_cancelled()
    {
        // ARRANGE
        // Concert with tickets
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        // An order
        $order = $concert->orderTickets('jane@example.com', 5);

        // Interim order existence check
        $this->assertEquals(5, $concert->ticketsRemaining());

        // ACT
        // Cancel the order
        $order->cancel();

        // ASSERT
        // Back to 10 available tickets
        $this->assertEquals(10, $concert->ticketsRemaining());

        $this->notSeeInDatabase('orders', [
            'id' => $order->id
        ]);


    }
}