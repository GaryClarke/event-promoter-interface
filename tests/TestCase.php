<?php

namespace Tests;

use Mockery;
use Exception;
use App\Exceptions\Handler;
use PHPUnit\Framework\Assert;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';


    /**
     * Test setup
     */
    protected function setUp()
    {
        parent::setUp();

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function ($key)
        {
            return $this->original->getData()[$key];
        });

        TestResponse::macro('assertViewIs', function ($name)
        {
            Assert::assertEquals($name, $this->original->name());
        });

        EloquentCollection::macro('assertContains', function ($value)
        {

            Assert::assertTrue($this->contains($value), 'Failed asserting that the collection contained the specified value');
        });

        EloquentCollection::macro('assertNotContains', function ($value)
        {

            Assert::assertFalse($this->contains($value), 'Failed asserting that the collection did not contain the specified value');
        });

        EloquentCollection::macro('assertEquals', function($items) {

            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }


    /**
     * Disable exception handling for testing purposes
     */
    public function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {

            public function __construct() {}

            public function report(Exception $exception) {}

            public function render($request, Exception $exception)
            {
                throw $exception;
            }
        });
    }
}
