<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function getPriceAttribute($value)
    {
        if (!$value) return 0;
        return showBalance($value);
    }

    public function getVatPercentAttribute($value)
    {
        return $value ?? 0;
    }

    public function getPriceWithVatAttribute($value)
    {
        if (!$value) return 0;
        return showBalance($value);
    }

    public function getImageAttribute($value)
    {
        return $value ? asset($value) : assetPortal('media/blank-image.svg');
    }

    public function audioSource():HasMany
    {
        return $this->hasMany(AudioSource::class, "book_id", "id");
    }

    public function audioSourceIds()
    {
        $audioSource = $this->audioSource;
        $data = [];
        foreach ($audioSource as $item) {
            $data[] = "$item->id";
        }
        return $data;
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, "audio_sources", "book_id", "playlist_id")->withPivot("id", "name")->whereNull('audio_sources.deleted_at');
    }

}
