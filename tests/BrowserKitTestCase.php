<?php

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class BrowserKitTestCase extends Laravel\BrowserKitTesting\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
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
