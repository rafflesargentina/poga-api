<?php

namespace Raffles\Modules\Poga\Http\Controllers\Cuenta;

use Raffles\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class TokenParaPagosController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * Create a new TokenParaPagosController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        Passport::tokensExpireIn(now()->addHour());    
	    
	$user = $request->user('api');
        $token = $user->createToken(env('APP_NAME'));
        $accessToken = $token->accessToken;

        $data = [
            'token' => $accessToken,
        ];

        return $this->validSuccessJsonResponse('Success', $data);
    }
}
