<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPagare extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'descripcion',
        'enum_estado',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tipos_pagare';

    /**
     * Get the pagares for the tipo pagare.
     */
    public function pagares()
    {
        return $this->hasMany(Pagare::class, 'id_tipo_pagare');
    }
}
