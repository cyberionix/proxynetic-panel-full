<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PProxyUPool;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class PProxyUPoolController extends Controller
{
    use AjaxResponses;

    public function ajax(Request $request)
    {
        $columns = ['id', 'ip', 'port', 'username', 'is_active', 'created_at'];

        $countTotalRecords = PProxyUPool::count();

        $query = PProxyUPool::query();

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ip', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('label', 'LIKE', "%{$search}%");
            });
        }

        $countFilteredRecords = $query->count();

        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $query->orderBy($orderColumn, $orderDir);

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $items = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($items as $item) {
            $data[] = [
                '<span data-id="' . $item->id . '">' . $item->id . '</span>',
                '<code>' . e($item->ip) . ':' . $item->port . '</code>',
                '<code>' . e($item->username) . '</code>',
                '<code>' . e($item->password) . '</code>',
                $item->label ? e($item->label) : '<span class="text-muted">-</span>',
                $item->is_active
                    ? '<span class="badge badge-light-success">Aktif</span>'
                    : '<span class="badge badge-light-danger">Pasif</span>',
                $item->created_at?->format('d.m.Y H:i') ?? '-',
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary pproxyu-edit-btn" data-id="' . $item->id . '" data-ip="' . e($item->ip) . '" data-port="' . $item->port . '" data-username="' . e($item->username) . '" data-password="' . e($item->password) . '" data-label="' . e($item->label) . '" data-is-active="' . ($item->is_active ? '1' : '0') . '"><i class="fa fa-edit me-1"></i>Düzenle</a>
                 <a href="#" class="btn btn-sm btn-light-danger btn-flex btn-center pproxyu-delete-btn" data-id="' . $item->id . '"><i class="fa fa-trash me-1"></i>Sil</a>',
            ];
        }

        echo json_encode([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'is_active' => 'nullable|in:0,1',
        ]);

        PProxyUPool::create([
            'ip' => $request->ip,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $request->password,
            'label' => $request->label,
            'is_active' => $request->is_active ?? 1,
        ]);

        return $this->successResponse('Proxy havuza eklendi.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ip' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'label' => 'nullable|string|max:255',
            'is_active' => 'nullable|in:0,1',
        ]);

        $pool = PProxyUPool::findOrFail($id);
        $pool->update([
            'ip' => $request->ip,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $request->password,
            'label' => $request->label,
            'is_active' => $request->is_active ?? 1,
        ]);

        return $this->successResponse('Proxy güncellendi.');
    }

    public function destroy($id)
    {
        $pool = PProxyUPool::findOrFail($id);
        $pool->delete();
        return $this->successResponse('Proxy silindi.');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'proxies' => 'required|string',
        ]);

        $lines = preg_split('/\r?\n/', trim($request->proxies));
        $imported = 0;
        $errors = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode(':', $line);
            if (count($parts) < 4) {
                $errors[] = 'Satır ' . ($i + 1) . ': Geçersiz format (ip:port:user:pass olmalı)';
                continue;
            }

            $ip = $parts[0];
            $port = (int) $parts[1];
            $username = $parts[2];
            $password = $parts[3];

            if ($port < 1 || $port > 65535) {
                $errors[] = 'Satır ' . ($i + 1) . ': Geçersiz port numarası';
                continue;
            }

            PProxyUPool::create([
                'ip' => $ip,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'is_active' => true,
            ]);
            $imported++;
        }

        $msg = $imported . ' proxy başarıyla içe aktarıldı.';
        if (count($errors) > 0) {
            $msg .= ' ' . count($errors) . ' hata: ' . implode('; ', array_slice($errors, 0, 5));
        }

        return $this->successResponse($msg);
    }
}
