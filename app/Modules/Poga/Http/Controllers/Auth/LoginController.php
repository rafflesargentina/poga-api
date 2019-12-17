<?php

namespace Raffles\Modules\Poga\Http\Controllers\Auth;

use Raffles\Http\Controllers\Auth\LoginController as Controller;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class LoginController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        try {
            $user = $this->guard()->user();
            $user->load('permissions', 'roles');

            if ($user->roles->count() === 1) {
                $roleId = $user->roles->first()->id;
            } else {
                $roleId = null;
            }

            $user->update(['role_id' => $roleId]);

            $token = $user->createToken(env('APP_NAME'));
            $accessToken = $token->accessToken;
        } catch (\Exception $e) {
            return $this->validInternalServerErrorJsonResponse($e, $e->getMessage());
        }

        $data = [
            'token' => $accessToken,
            'user' => $user
        ];

        return $this->validSuccessJsonResponse('Success', $data);
    }
}
