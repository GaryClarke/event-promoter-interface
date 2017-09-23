<?php

namespace Tests\Feature\Backstage;

use Auth;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoterLoginTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    function logging_in_with_valid_credentials()
    {
        $this->disableExceptionHandling();

        // ARRANGE
        // A user
        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password')
        ]);

        // ACT
        // Login
        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'super-secret-password'
        ]);

        // User is redirected to the correct endpoint
        $response->assertRedirect('/backstage/concerts/new');

        // ASSERT
        // There is an auth user
        $this->assertTrue(Auth::check());

        // Auth user is the same as the login creds user
        $this->assertTrue(Auth::user()->is($user));
    }


    /** @test */
    function logging_in_with_invalid_credentials()
    {
        $this->disableExceptionHandling();

        // ARRANGE
        // A user
        $user = factory(User::class)->create([
            'email'    => 'jane@example.com',
            'password' => bcrypt('super-secret-password')
        ]);

        // ACT
        // Login
        $response = $this->post('/login', [
            'email'    => 'jane@example.com',
            'password' => 'not-the-right-password'
        ]);

        // User is redirected to the correct endpoint
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(Auth::check());
    }


    /** @test */
    function logging_out_the_current_user()
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }


    /** @test */
    function logging_in_with_an_account_that_does_not_exist()
    {
        $this->disableExceptionHandling();

        // ACT
        // Attempt Login
        $response = $this->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'not-the-right-password'
        ]);

        // User is redirected to the correct endpoint
        $response->assertRedirect('/login');

        // Session errors
        $response->assertSessionHasErrors('email');

        // ASSERT
        // There is an auth user
        $this->assertFalse(Auth::check());
    }
}