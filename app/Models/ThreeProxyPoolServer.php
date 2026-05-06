<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreeProxyPoolServer extends Model
{
    protected $guarded = [];

    public function pool()
    {
        return $this->belongsTo(ThreeProxyPool::class, 'pool_id');
    }

    public function getIpArray(): array
    {
        if (!$this->ip_list) return [];

        $lines = preg_split("/\r\n|\r|\n/", $this->ip_list);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, fn($line) => $line !== '');
        return array_values($lines);
    }

    public function getIpCount(): int
    {
        return count($this->getIpArray());
    }
}
