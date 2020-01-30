<?php

namespace Raffles\Modules\Poga\UseCases;

use GuzzleHttp\Client;

class ActualizarBoletaPago
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
    protected $id, $data;

    /**
     * Create a new DebtController instance.
     *
     * @return void
     */    
    public function __construct($id, array $data)
    {
        $this->client = new Client(
            [
            'headers' => [
                    'apiKey' => env('DEBTS_API_KEY'),
                    'Content-Type' => 'application/json'
            ]
            ]
        );

        $this->id = $id;    
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
            $url = env('DEBTS_TESTING_URL').'/debts/'.$this->id;
        } else {
            $url = env('DEBTS_URL').'/debts/'.$this->id;
	}

        $response = $this->client->put(
            $url, [
            'json' => $this->data
            ]
        );
    
        return json_decode($response->getBody(), true);
    }
}
