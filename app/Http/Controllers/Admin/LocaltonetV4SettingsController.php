<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class LocaltonetV4SettingsController extends Controller
{
    public function edit()
    {
        $randomPortMin = (int) config('services.localtonet_v4.random_port_min', 20000);
        $randomPortMax = (int) config('services.localtonet_v4.random_port_max', 65000);
        $tunnelNetInterface = (string) config('services.localtonet_v4.tunnel_net_interface', 'Ethernet0');

        return view('admin.pages.localtonetV4.settings', compact('randomPortMin', 'randomPortMax', 'tunnelNetInterface'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'random_port_min' => 'required|integer|min:1024|max:65535',
            'random_port_max' => 'required|integer|min:1024|max:65535',
            'tunnel_net_interface' => 'required|string|max:64|regex:/^[A-Za-z0-9_.-]+$/',
        ], [
            'random_port_min.required' => 'Minimum port zorunludur.',
            'random_port_max.required' => 'Maksimum port zorunludur.',
            'tunnel_net_interface.required' => 'Tünel ağ arayüzü zorunludur.',
            'tunnel_net_interface.regex' => 'Arayüz adı yalnızca harf, rakam, nokta, tire ve alt çizgi içerebilir.',
        ]);

        if ((int) $validated['random_port_min'] >= (int) $validated['random_port_max']) {
            return redirect()->back()->withErrors(['random_port_max' => 'Maksimum port, minimum porttan büyük olmalıdır.'])->withInput();
        }

        $path = config_path('localtonet_v4_panel.php');
        $existing = [];
        if (File::exists($path)) {
            $loaded = require $path;
            $existing = is_array($loaded) ? $loaded : [];
        }

        $data = array_merge($existing, [
            'random_port_min' => (int) $validated['random_port_min'],
            'random_port_max' => (int) $validated['random_port_max'],
            'tunnel_net_interface' => $validated['tunnel_net_interface'],
        ]);

        File::put($path, '<?php return '.var_export($data, true).';');

        Artisan::call('config:clear');

        return redirect()->route('admin.localtonetV4.settings')->with('form_success', 'Localtonetv4 ayarları kaydedildi.');
    }
}
