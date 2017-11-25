<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use ConcertFactory;
use Tests\TestCase;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase {

    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        Collection::macro('assertContains', function ($value)
        {

            Assert::assertTrue($this->contains($value), 'Failed asserting that the collection contained the specified value');
        });

        Collection::macro('assertNotContains', function ($value)
        {

            Assert::assertFalse($this->contains($value), 'Failed asserting that the collection did not contain the specified value');
        });

        Collection::macro('assertEquals', function($items) {

            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }


    /** @test */
    function guests_cannot_view_a_promoters_concert_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }


    /** @test */
    function promoters_can_only_view_a_list_of_their_own_concerts()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();


        // Published concerts
        $publishedConcertA = ConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = ConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = ConcertFactory::createPublished(['user_id' => $user->id]);

        // Unpublished concerts
        $unpublishedConcertA = ConcertFactory::createUnpublished(['user_id' => $user->id]);
        $unpublishedConcertB = ConcertFactory::createUnpublished(['user_id' => $otherUser->id]);
        $unpublishedConcertC = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertC
        ]);

        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertC
        ]);
    }
}