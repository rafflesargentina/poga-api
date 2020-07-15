<?php

namespace Raffles\Modules\Poga\UseCases;

use GuzzleHttp\Client;
use Exception;

class BoletaNoEncontrada extends Exception {}

class TraerBoletaPago
{
    /**
     * The Client.
     *
     * @var Client
     */
    protected $client;

    /**
     * El id de la boleta.
     *
     * @var array
     */
    protected $id;

    /**
     * Create a new job instance.
     *
     * @param  int  $id El id de la boleta.
     *
     * @return void
     */
    public function __construct($id)
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

	try {
	    $response = $this->client->get($url);
	} catch (\GuzzleHttp\Exception\ClientException $e) {
	    switch ($e->getCode()) {
                case 404:
	            throw new BoletaNoEncontrada('La boleta con docId '.$this->id.' se encuentra anulada o no pudo ser encontrada.');
            }
	}

        return json_decode($response->getBody(), true, JSON_PRETTY_PRINT);
    }
}
