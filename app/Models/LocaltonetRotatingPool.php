<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocaltonetRotatingPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['api_key'];

    protected $casts = [
        'tunnel_ids' => 'json',
    ];

    public function getTunnelCount(): int
    {
        return is_array($this->tunnel_ids) ? count($this->tunnel_ids) : 0;
    }

    public function getTypeLabel(): string
    {
        return $this->type === 'unlimited' ? 'Sınırsız' : 'Kotalı';
    }
}
