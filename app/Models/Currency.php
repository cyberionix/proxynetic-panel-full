<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    const DEFAULT_ID = 1; // TL
    const DEFAULT_SYMBOL = "₺"; // TL
}
