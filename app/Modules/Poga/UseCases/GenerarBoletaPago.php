<?php

namespace Raffles\Modules\Poga\UseCases;

use GuzzleHttp\Client;

class GenerarBoletaPago
{
    /**
     * The Client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new DebtController instance.
     *
     * @return void
     */    
    public function __construct(array $data)
    {
        $this->client = new Client(
            [
            'headers' => [
                    'apiKey' => env('DEBTS_API_KEY'),
                    'Content-Type' => 'application/json'
            ]
            ]
        );
    
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (\App::environment('local')) {
            $url = env('DEBTS_TESTING_URL').'/debts';
        } else {
            $url = env('DEBTS_URL').'/debts';
	}

	\Log::info(array_merge($this->data, ['uiTheme' => ['name' => 'poga']]));

        $response = $this->client->post(
            $url, [
            'json' => [
                'debt' => array_merge($this->data, ['uiTheme' => ['name' => 'poga']])
            ]
            ]
        );
    
        return json_decode($response->getBody(), true);
    }
}
