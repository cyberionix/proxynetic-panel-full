<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["deleted_at", "created_at", "updated_at", "id", "name"];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isPremium()
    {
        return $this->id < UserGroup::latest()->first()->id;
    }
}
