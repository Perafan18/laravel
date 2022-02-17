<?php

namespace Tests\Unit\Command;

use Http;
use Tests\TestCase;

class FakePostTest extends TestCase
{
    /**
     * @return void
     */

    public function test_the_request_failed_500()
    {
        Http::fake([
            'atomic.incfile.com/fakepost' => Http::response(['message' => ""], 500)
        ]);

        $this->artisan('request:fakepost')
            ->expectsOutput('Server error (500)')
            ->assertFailed();
    }

    /**
     * @return void
     */

    public function test_the_request_failed_418()
    {
        Http::fake([
            'atomic.incfile.com/fakepost' => Http::response(['message' => "I'm a teapot"], 418)
        ]);

        $this->artisan('request:fakepost')
            ->expectsOutput('Client error (418)')
            ->assertFailed();
    }

    /**
     * @return void
     */

    public function test_the_request_is_successful()
    {
        Http::fake([
            'atomic.incfile.com/fakepost' => Http::response(['message' => 'Hi!'], 200)
        ]);

        $this->artisan('request:fakepost')
            ->expectsOutput('Message: ')
            ->expectsOutput('---------------------------------------------')
            ->expectsOutput('Hi!')
            ->expectsOutput('---------------------------------------------')
            ->assertSuccessful();
    }
}
