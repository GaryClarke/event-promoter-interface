<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Mockery;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

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


    /**
     * Set a from url
     *
     * @param $url
     * @return $this
     */
    protected function from($url)
    {
        session()->setPreviousUrl(url($url));

        return $this;
    }
}
