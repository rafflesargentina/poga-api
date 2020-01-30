<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\{ Persona, User };
use Raffles\Modules\Poga\Notifications\InvitacionCreada;
use Raffles\Modules\Poga\Repositories\{ PersonaRepository, RoleRepository, UserRepository };

use Illuminate\Foundation\Bus\DispatchesJobs;

class CrearPersona
{
    use DispatchesJobs;

    /**
     * The form data and the User model.
     *
     * @var array
     * @var User
     */
    protected $data, $user;

    /**
     * Create a new job instance.
     *
     * @param array $data The form data.
     * @param User  $user The User model.
     *
     * @return void
     */
    public function __construct($data, User $user)
    {
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param PersonaRepository $repository The PersonaRepository object.
     * @param UserRepository    $rUser      The UserRepository object.
     * @param RoleRepository    $rRole      The RoleRepository object.
     *
     * @return void
     */
    public function handle(PersonaRepository $repository, UserRepository $rUser, RoleRepository $rRol)
    {
        $persona = $this->crearPersona($repository);
        $user = $this->crearUsuario($persona, $rUser);

        if ($user) {
            $this->adjuntarRoles($user, $rRol);
        }

        return $persona;
    }

    /**
     * Adjuntar roles.
     *
     * @param RoleRepository $rRol The RolRepository object.
     * @param User           $user The User model.
     *
     * @return User
     */
    protected function adjuntarRoles(User $user, RoleRepository $repository)
    {
        $role = $repository->findBy('slug', strtolower($this->data['enum_rol']));

        $user->roles()->attach($role);
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }

    /**
     * Crear Persona.
     *
     * @param PersonaRepository $repository The PersonaRepository object.
     */
    protected function crearPersona(PersonaRepository $repository)
    {
        return $repository->create(
            array_merge(
                $this->data,
                [
                'enum_estado' => 'ACTIVO',
                'id_usuario_creador' => $this->user->id,
                ]
            )
        )[1];
    }

    /**
     * Crear Usuario.
     *
     * @param Persona        $persona    The Persona model.
     * @param UserRepository $repository The UserRepository object.
     *
     * @return User
     */
    protected function crearUsuario(Persona $persona, UserRepository $repository)
    {
        if ($this->data['invitar'] && !$persona->user) {
            $user = $repository->create(
                [
                'codigo_validacion' => str_random(),
                'email' => $persona->mail,
                'first_name' => $persona->nombre,
                'id_persona' => $persona->id,
                'last_name' => $persona->apellido,
                ]
            )[1];

            $user->notify(new InvitacionCreada($user->idPersona));

            return $user;
        }
    }
}
