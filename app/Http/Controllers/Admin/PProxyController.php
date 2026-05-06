<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PProxySettings;
use App\Services\PlainProxiesApiService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class PProxyController extends Controller
{
    use AjaxResponses;

    public function settings()
    {
        $settings = PProxySettings::first() ?? new PProxySettings();
        return view('admin.pages.pproxy.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'api_key'       => 'required|string',
            'server_domain' => 'required|string|max:255',
        ]);

        $settings = PProxySettings::first();
        if (!$settings) {
            $settings = new PProxySettings();
        }

        $settings->api_key       = $request->api_key;
        $settings->server_domain = $request->server_domain;
        $settings->save();

        return $this->successResponse('PProxy ayarları başarıyla kaydedildi.');
    }

    public function testConnection()
    {
        $service = new PlainProxiesApiService();
        $result = $service->testConnection();

        if ($result['success']) {
            return $this->successResponse($result['message']);
        }
        return $this->errorResponse($result['message']);
    }
}
