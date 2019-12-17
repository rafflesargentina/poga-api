<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bancos';

    /**
     * Get the personas for the banco.
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'id_banco');
    }
}
