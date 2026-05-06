<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThreeProxyPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function servers()
    {
        return $this->hasMany(ThreeProxyPoolServer::class, 'pool_id');
    }

    /**
     * Tüm serverlardaki IP'leri server bilgisiyle döndürür.
     * @return array<array{ip: string, server_id: int}>
     */
    public function getAllIpsWithServer(): array
    {
        $result = [];
        foreach ($this->servers as $server) {
            foreach ($server->getIpArray() as $ip) {
                $result[] = ['ip' => $ip, 'server_id' => $server->id];
            }
        }
        return $result;
    }

    /**
     * Eski uyumluluk - düz IP dizisi.
     */
    public function getIpArray(): array
    {
        if ($this->servers->count() > 0) {
            return array_column($this->getAllIpsWithServer(), 'ip');
        }

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

    public function getTotalIpCount(): int
    {
        $count = 0;
        foreach ($this->servers as $server) {
            $count += $server->getIpCount();
        }
        return $count;
    }

    public function getServerCount(): int
    {
        return $this->servers->count();
    }
}
