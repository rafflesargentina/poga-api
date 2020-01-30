<?php

namespace Raffles\Modules\Poga\Http\Controllers\Auth;

use Raffles\Modules\Poga\Models\{ Persona, User };
use Raffles\Http\Controllers\Auth\RegisterController as Controller;
use Raffles\Modules\Poga\Mail\UsuarioCreadoParaAdminPoga;
use Raffles\Modules\Poga\Notifications\UsuarioRegistrado;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use Log;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class RegisterController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());
        $user->loadMissing('roles');

	try {
            $user->notify(new UsuarioRegistrado($user));
        } catch (\Exception $e) {
            Log::error('No se pudo enviar el email de notificación para el usuario registrado.');
	}

	try {
            $admin = User::where('email', env('MAIL_ADMIN_ADDRESS'))->first();
	    Mail::to(env('MAIL_ADMIN_ADDRESS'))->send(new UsuarioCreadoParaAdminPoga($user));
	} catch (\Exception $e) {
            Log::error('No se pudo enviar el mail de notificación de usuario registrado al Administrador de Poga.');
	}

        return $user;

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data, [
            'accepted' => 'required|accepted',        
            //'ciudades_cobertura' => 'required_if:role_id,1|array|min:1',
            'email' => 'required|email|unique:users,email',
            'id_persona.enum_tipo_persona' => 'sometimes|required',
            'id_persona.apellido' => 'required_if:id_persona.enum_tipo_persona,FISICA',
            'id_persona.fecha_nacimiento' => 'required|date',
            //'id_persona.id_pais' => 'sometimes|required_if:role_id,1',
            //'id_persona.id_pais_cobertura' => 'sometimes|required_if:role_id,1',
            'id_persona.nombre' => 'required',
            //'id_persona.ci' => 'sometimes|required',
            'password' => 'required|confirmed|min:6',
            //'plan' => 'sometimes|required_if:role_id,1',
            'role_id' => 'required',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $persona = Persona::create(array_merge(['enum_estado' => 'ACTIVO', 'mail' => $data['email']], $data['id_persona']));

        $user = User::create(
            [
            'email' => $data['email'],
            'first_name' => $data['id_persona']['nombre'],
            'id_persona' => $persona->id,
            'last_name' => $data['id_persona']['apellido'],
            'password' => $data['password'],
            'role_id' => $data['role_id']
            ]
        );

        //if (isset($data['ciudades_cobertura'])) {
            //foreach ($data['ciudades_cobertura'] as $ciudadId) {
                //$persona->ciudades_cobertura()->create(['enum_estado' => 'ACTIVO', 'id_ciudad' => $ciudadId, 'role_id' => $data['role_id']]);
            //}
        //}

        $role = $data['role_id'];
        $user->roles()->attach($role);

        return $user;
    }
}
