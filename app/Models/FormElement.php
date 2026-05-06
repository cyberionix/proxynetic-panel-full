<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormElement extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ["name", "type", "label", "label_info", "attrs", "options", "form_type"];
    protected $casts = [
        "options" => "json"
    ];
}
