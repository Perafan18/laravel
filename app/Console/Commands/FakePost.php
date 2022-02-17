<?php

namespace App\Console\Commands;

use GuzzleHttp\Promise\PromiseInterface;
use Http;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use App\Jobs\FakePost as FakePostJob;

class FakePost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:fakepost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '<---- Yeah, it\'s this';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $response = $this->makeRequest();

        $this->onError($response);

        if($response->failed()) {
            $this->retry();
            return 1;
        }

        $this->showMessage($response);
    }

    /**
     * @param PromiseInterface|Response $response
     * @return void
     */

    private function onError(PromiseInterface|Response $response)
    {
        $response->onError(function() use($response) {
            $errorType = 'Client';

            if($response->serverError()) {
                $errorType = 'Server';
            }

            $this->error($errorType . ' error (' . $response->status() . ')');
        });
    }

    /**
     * @param PromiseInterface|Response $response
     * @return void
     */

    private function showMessage(PromiseInterface|Response $response)
    {
        $this->info('Message: ');
        $this->info('---------------------------------------------');
        $this->info($response->collect()->get('message'));
        $this->info('---------------------------------------------');
    }

    /**
     * @return PromiseInterface|Response
     */

    private function makeRequest(): PromiseInterface|Response
    {
        return Http::acceptJson()
            ->post(config('app.fake_url'));
    }
    /**
     * @return void
     */

    private function retry()
    {
        FakePostJob::dispatch()
            ->delay($this->delay());
    }

    /**
     * @return \Illuminate\Support\Carbon
     */

    private function delay(): \Illuminate\Support\Carbon
    {
        return now()->addMinutes(10);
    }

}
