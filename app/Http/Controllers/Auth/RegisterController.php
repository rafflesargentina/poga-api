<?php

namespace Raffles\Http\Controllers\Auth;

use Raffles\Http\Controllers\Controller;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use FormatsValidJsonResponses, RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        try {
            $token = $user->createToken(env('APP_NAME'));
            $accessToken = $token->accessToken;
        } catch (\Exception $e) {
            return $this->validInternalServerErrorJsonResponse($e, $e->getMessage());
        }

        $data = [
            'token' => $accessToken,
            'user' => $user
        ];

        return $this->registered($request, $user)
            ?: $this->validSuccessJsonResponse('Success', $data, $this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data, [
            'accepted' => 'accepted',
            'email' => 'required|string|email|unique:users',
            'first_name' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function create(array $data)
    {
        $model = config('auth.providers.users.model');
        $model = new $model;

        return $model->create(
            [
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'password' => $data['password'],
            ]
        );
    }
}
