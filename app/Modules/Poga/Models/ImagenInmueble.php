<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenInmueble extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'descripcion',
        'enum_estado',
        'id_inmueble',
        'imagen',
        'principal',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imagenes_inmueble';
}
