<?php

namespace Raffles\Modules\Poga\UseCases;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
     *
     */
    public function handle()
    {
        $response = $this->client->put(
            'http://poga.base97.com/api/v1/debts/'.$this->id, [
            'properties' => $this->data
            ]
        );
    
        return json_decode($response->getBody(), true);
    }
}
