<?php

namespace Raffles\Modules\Poga\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Storage;

class EstadoInmuebleRenta extends Pivot
{
    /**
     * The table associated with the pivot.
     *
     * @var string
     */
    protected $table = 'estado_inmueble_renta';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'size',
	'thumbnail',
	'url',
    ];

    /**
     * Get the foto size.
     *
     * @return string
     */
    public function getSizeAttribute()
    {
        if ($this->foto && Storage::exists($this->foto)) {
            return Storage::size($this->foto);
        }

        return 0;
    }

    /**
     * Get the photo url.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->foto && Storage::exists($this->foto)) {
            return $this->attributes['url'] = Storage::url($this->foto);
        } else {
            return $this->attributes['url'] = $this->foto;
        }
    }

    /**
     * Get the foto thumbnail.
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        return $this->foto;
    }
}
