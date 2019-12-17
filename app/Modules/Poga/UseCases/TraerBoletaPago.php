<?php

namespace Raffles\Modules\Poga\UseCases;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TraerBoletaPago
{

    /**
     * The Client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The id.
     *
     * @var int
     */
    protected $id;

    /**
     * Create a new DebtController instance.
     *
     * @return void
     */    
    public function __construct($id)
    {
        $this->client = new Client(
            [
                'base_uri' => 'http://poga.base97.com/api/v1/',
            'headers' => [
            'apiKey' => env('DEBTS_API_KEY'),
                    'Content-Type' => 'application/json'
                ]
            ]
        );
    
        $this->id = $id;
    }

    /**
     *
     */
    public function handle()
    {
        $res = $this->client->get('debts/'.$this->id);

        return json_decode($res->getBody(), true);
    }
}
