<?php

namespace Raffles\Models;

use Raffles\Models\Traits\PhotoTrait;

use Illuminate\Database\Eloquent\Model;
use Storage;

class Document extends Model
{
    use PhotoTrait;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'size',
        'thumbnail',
        'url',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'location',
        'name',
        'documentable_id',
        'documentable_type',
        'slug'
    ];

    /**
     * Get all of the owning documentable models.
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the document name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        if ($this->location && Storage::exists($this->location)) {
            $url = Storage::url($this->location);
            return  pathinfo($url, PATHINFO_FILENAME).'.'.pathinfo($url, PATHINFO_EXTENSION);
        }

        return $this->location;
    }

    /**
     * Get the document thumbnail.
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        if ($this->location && Storage::exists($this->location)) {
            $ext = pathinfo(Storage::url($this->location), PATHINFO_EXTENSION);
            switch ($ext) {
            case 'doc':
                return $this->attributes['url'] = '/img/doc.png';
                    break;
            case 'docx':
                return $this->attributes['url'] = '/img/doc.png';
                    break;
            case 'pdf':
                return $this->attributes['url'] = '/img/pdf.png';
                    break;
            case 'xls':
                return $this->attributes['url'] = '/img/xls.png';
                    break;
            case 'xlsx':
                return $this->attributes['url'] = '/img/xls.png';
            }
        } else {
            return $this->attributes['url'] = $this->location;
        }
    }
}
