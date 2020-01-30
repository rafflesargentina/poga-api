<?php

namespace Raffles\Modules\Poga\Models;

use Raffles\Models\FeaturedPhoto;

use Illuminate\Database\Eloquent\Model;

class EstadoInmueble extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enum_categoria',
	'enum_estado',
	'nombre',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'estados_inmueble';
}
