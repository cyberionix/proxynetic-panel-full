<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'entries' => 'json',
    ];

    public function getEntryCount(): int
    {
        return is_array($this->entries) ? count($this->entries) : 0;
    }

    public function getTotalIpCount(): int
    {
        if (! is_array($this->entries)) {
            return 0;
        }
        $total = 0;
        foreach ($this->entries as $entry) {
            $total += is_array($entry['ips'] ?? null) ? count($entry['ips']) : 0;
        }
        return $total;
    }
}
