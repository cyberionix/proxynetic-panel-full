<?php

namespace App\View\Components\Admin\FormElements;

use App\Services\LocaltonetService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AuthTokenSelect extends Component
{
    public $options;
    public function __construct($options = [])
    {
        $service = new LocaltonetService();
        $tokens = $service->getAuthTokens();

        $options = [];
        foreach (($tokens['result'] ?? []) as $token) {
            $status = $token["clientIsOnline"] ? "Active" : "Passive";
            $activeTunnelCount = $token["activeTunnelCount"] ?? 0;
            $totalTunnelCount = $token["totalTunnelCount"] ?? 0;
            if ($activeTunnelCount == 0 && $totalTunnelCount == 0){
                $tunnelInfo = "-";
            }else{
                $tunnelInfo = "{$activeTunnelCount}/{$totalTunnelCount}";
            }

            $options[] = [
                "label" => $token["name"] . " ({$status}) ({$tunnelInfo})",
                "value" => $token["token"]
            ];
        }
        $this->options = $options;
    }
    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.auth-token-select');
    }
}
