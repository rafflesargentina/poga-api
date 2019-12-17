<?php

namespace Raffles\Modules\Poga\UseCases;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
     *
     */
    public function handle()
    {
        $response = $this->client->post(
            'https://poga.base97.com/api/v1/debts', [
            'json' => [
                'debt' => $this->data
            ]
            ]
        );
    
        return json_decode($response->getBody(), true);
    }
}
