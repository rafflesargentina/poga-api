<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'abbr',
        'enum_estado',
        'moneda',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monedas';
}
