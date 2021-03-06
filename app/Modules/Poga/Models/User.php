<?php

namespace Raffles\Modules\Poga\Models;

use Raffles\Models\FeaturedPhoto;
use Raffles\Modules\Poga\Models\Traits\UserTrait;

use Caffeinated\Shinobi\Models\Role;
use Caffeinated\Shinobi\Traits\ShinobiTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, ShinobiTrait, SoftDeletes, UserTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bloqueado',
        'codigo_validacion',
        'email',
        'first_name',
        'id_persona',
        'last_name',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['avatar', 'idPersona', 'idRol'];

    /**
     * Get the user's avatar.
     */
    public function avatar()
    {
        return $this->morphOne(FeaturedPhoto::class, 'photoable')->withDefault();
    }

    /**
     * Get the persona record associated with the user.
     */
    public function idPersona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    /**
     * Get the rol record associated with the user.
     */
    public function idRol()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the inmuebles for the usuario.
     */
    public function inmuebles()
    {
        return $this->hasMany(Inmueble::class, 'id_usuario_creador');
    }

    /**
     * Get the mantenimientos for the usuario.
     */
    public function mantenimientos()
    {
        return $this->hasManyThrough(Mantenimiento::class, ProveedorServicio::class, 'id_proveedor', 'id_proveedor_servicio');
    }

    /**
     * Get the nominaciones for the usuario.
     */
    public function nominaciones()
    {
        return $this->hasMany(Nominacion::class, 'usu_alta');
    }

    /**
     * Get the persona creadas for the usuario.
     */
    public function personas_creadas()
    {
        return $this->hasMany(Persona::class, 'id_usuario_creador');
    }

    /**
     * Get the proveedores servicio for the usuario.
     */
    public function proveedores_servicio()
    {
        return $this->hasManyThrough(ProveedorServicio::class, Persona::class, 'id_usuario_creador', 'id_proveedor');
    }
}
