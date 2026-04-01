<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThreeProxyPool;
use App\Models\ThreeProxyPoolServer;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreeProxyPoolController extends Controller
{
    use AjaxResponses;

    public function ajax(Request $request)
    {
        $searchableColumns = [
            "id",
            "name",
            "created_at"
        ];

        $whereSearch = "deleted_at IS NULL";

        if (isset($request->order[0]["column"]) && isset($request->order[0]["dir"])) {
            $col = min($request->order[0]["column"], count($searchableColumns) - 1);
            $orderBy = $searchableColumns[$col] . " " . $request->order[0]["dir"];
        } else {
            $orderBy = "id DESC";
        }

        $searchVal = $request->search["value"];
        if ($searchVal) {
            $whereSearch .= " AND (";
            foreach ($searchableColumns as $key => $searchableColumn) {
                $whereSearch .= "$searchableColumn LIKE '%{$searchVal}%'";
                if (array_key_last($searchableColumns) != $key) {
                    $whereSearch .= " OR ";
                } else {
                    $whereSearch .= ")";
                }
            }
        }

        $start = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $query = DB::table('three_proxy_pools')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $query = $query->skip($start)->take($length);
        $list = $query->get();
        $countTotalRecords = $list->count();

        $data = [];
        foreach ($list as $item) {
            $pool = ThreeProxyPool::with('servers')->find($item->id);
            $serverCount = $pool ? $pool->getServerCount() : 0;
            $totalIps = $pool ? $pool->getTotalIpCount() : 0;

            $data[] = [
                "<span data-id='" . $item->id . "'>" . $item->id . "</span>",
                htmlspecialchars($item->name),
                $serverCount . ' sunucu',
                $totalIps . ' IP',
                convertDateTime($item->created_at),
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' . __("actions") . '
                    <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="javascript:void(0);" class="menu-link px-3 tpEditBtn">' . __("edit") . '</a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="javascript:void(0);" class="menu-link px-3 tpDeleteBtn">' . __("delete") . '</a>
                    </div>
                </div>',
            ];
        }

        echo json_encode([
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->name) {
            return $this->errorResponse('Havuz adı zorunludur.');
        }

        $servers = $request->input('servers', []);
        if (empty($servers) || !is_array($servers)) {
            return $this->errorResponse('En az bir sunucu eklenmelidir.');
        }

        foreach ($servers as $i => $s) {
            if (empty($s['server_ip'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': IP adresi zorunludur.');
            if (empty($s['port'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': Port zorunludur.');
            if (empty($s['auth_username'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': Auth Username zorunludur.');
            if (empty($s['auth_password'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': Auth Password zorunludur.');
        }

        DB::beginTransaction();
        try {
            $pool = ThreeProxyPool::create([
                'name' => $request->name,
                'server_ip' => $servers[0]['server_ip'] ?? '',
                'port' => $servers[0]['port'] ?? 7000,
                'auth_username' => $servers[0]['auth_username'] ?? '',
                'auth_password' => $servers[0]['auth_password'] ?? '',
                'ip_list' => null,
            ]);

            foreach ($servers as $s) {
                ThreeProxyPoolServer::create([
                    'pool_id' => $pool->id,
                    'server_ip' => $s['server_ip'],
                    'port' => $s['port'] ?? 7000,
                    'auth_username' => $s['auth_username'],
                    'auth_password' => $s['auth_password'],
                    'ip_list' => $s['ip_list'] ?? null,
                ]);
            }

            DB::commit();
            return $this->successResponse('3Proxy havuzu başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Hata: ' . $e->getMessage());
        }
    }

    public function show(ThreeProxyPool $threeProxyPool)
    {
        $threeProxyPool->load('servers');
        return $this->successResponse('', ['data' => $threeProxyPool]);
    }

    public function update(ThreeProxyPool $threeProxyPool, Request $request)
    {
        if (!$request->name) {
            return $this->errorResponse('Havuz adı zorunludur.');
        }

        $servers = $request->input('servers', []);
        if (empty($servers) || !is_array($servers)) {
            return $this->errorResponse('En az bir sunucu eklenmelidir.');
        }

        foreach ($servers as $i => $s) {
            if (empty($s['server_ip'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': IP adresi zorunludur.');
            if (empty($s['port'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': Port zorunludur.');
            if (empty($s['auth_username'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': Auth Username zorunludur.');
            if (empty($s['auth_password'])) return $this->errorResponse('Sunucu ' . ($i + 1) . ': Auth Password zorunludur.');
        }

        DB::beginTransaction();
        try {
            $threeProxyPool->update([
                'name' => $request->name,
                'server_ip' => $servers[0]['server_ip'] ?? $threeProxyPool->server_ip,
                'port' => $servers[0]['port'] ?? $threeProxyPool->port,
                'auth_username' => $servers[0]['auth_username'] ?? $threeProxyPool->auth_username,
                'auth_password' => $servers[0]['auth_password'] ?? $threeProxyPool->auth_password,
            ]);

            $threeProxyPool->servers()->delete();

            foreach ($servers as $s) {
                ThreeProxyPoolServer::create([
                    'pool_id' => $threeProxyPool->id,
                    'server_ip' => $s['server_ip'],
                    'port' => $s['port'] ?? 7000,
                    'auth_username' => $s['auth_username'],
                    'auth_password' => $s['auth_password'],
                    'ip_list' => $s['ip_list'] ?? null,
                ]);
            }

            DB::commit();
            return $this->successResponse('3Proxy havuzu başarıyla güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Hata: ' . $e->getMessage());
        }
    }

    public function delete(ThreeProxyPool $threeProxyPool)
    {
        $threeProxyPool->servers()->delete();
        if ($threeProxyPool->delete()) {
            return $this->successResponse('3Proxy havuzu başarıyla silindi.');
        }
        return $this->errorResponse();
    }
}
