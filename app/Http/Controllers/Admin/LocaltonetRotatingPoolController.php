<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocaltonetRotatingPool;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocaltonetRotatingPoolController extends Controller
{
    use AjaxResponses;

    public function ajax(Request $request)
    {
        $searchableColumns = ['id', 'name', 'type', 'created_at'];

        $whereSearch = 'deleted_at IS NULL';

        if (isset($request->order[0]['column']) && isset($request->order[0]['dir'])) {
            $orderBy = $searchableColumns[$request->order[0]['column']] . ' ' . $request->order[0]['dir'];
        } else {
            $orderBy = 'id DESC';
        }

        $searchVal = $request->search['value'] ?? '';
        if ($searchVal) {
            $whereSearch .= " AND (";
            foreach ($searchableColumns as $key => $col) {
                $whereSearch .= "{$col} LIKE '%{$searchVal}%'";
                if (array_key_last($searchableColumns) != $key) {
                    $whereSearch .= ' OR ';
                } else {
                    $whereSearch .= ')';
                }
            }
        }

        $start  = $request->start ?? 0;
        $length = $request->length == -1 ? 10 : $request->length;

        $countFilteredRecords = DB::table('localtonet_rotating_pools')
            ->whereRaw($whereSearch)
            ->count();

        $query = DB::table('localtonet_rotating_pools')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list = $query->get();
        $countTotalRecords = $list->count();

        $data = [];
        foreach ($list as $item) {
            $tunnelIds = json_decode($item->tunnel_ids ?: '[]', true);
            $tunnelCount = is_array($tunnelIds) ? count($tunnelIds) : 0;
            $typeLabel = $item->type === 'unlimited' ? '<span class="badge badge-success">Sınırsız</span>' : '<span class="badge badge-warning">Kotalı</span>';

            $data[] = [
                "<span data-id='{$item->id}' data-name='" . htmlspecialchars($item->name, ENT_QUOTES) . "' data-type='{$item->type}' data-api-key='" . htmlspecialchars($item->api_key ?? '', ENT_QUOTES) . "' data-tunnel-ids='" . htmlspecialchars($item->tunnel_ids ?? '[]', ENT_QUOTES) . "'>{$item->id}</span>",
                e($item->name),
                $typeLabel,
                $tunnelCount . ' Tunnel',
                convertDateTime($item->created_at),
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' . __('actions') . '
                    <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="javascript:void(0);" class="menu-link px-3 lrpEditBtn">' . __('edit') . '</a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="javascript:void(0);" class="menu-link px-3 lrpDeleteBtn">' . __('delete') . '</a>
                    </div>
                </div>',
            ];
        }

        echo json_encode([
            'recordsTotal'    => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data'            => $data,
        ]);
    }

    public function show(LocaltonetRotatingPool $localtonetRotatingPool)
    {
        return $this->successResponse('', ['data' => $localtonetRotatingPool]);
    }

    public function store(Request $request)
    {
        if (!$request->name) {
            return $this->errorResponse('Havuz adı zorunludur.');
        }
        if (!$request->type || !in_array($request->type, ['quota', 'unlimited'])) {
            return $this->errorResponse('Havuz tipi seçimi zorunludur.');
        }
        if (!$request->api_key) {
            return $this->errorResponse('Localtonet API Key zorunludur.');
        }

        $tunnelIds = $this->parseTunnelIds($request->input('tunnel_ids_text', ''));

        $pool = LocaltonetRotatingPool::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'api_key'    => $request->api_key,
            'tunnel_ids' => $tunnelIds,
        ]);

        if ($pool) {
            return $this->successResponse('Localtonet Rotating havuzu başarıyla oluşturuldu.');
        }
        return $this->errorResponse();
    }

    public function update(LocaltonetRotatingPool $localtonetRotatingPool, Request $request)
    {
        if (!$request->name) {
            return $this->errorResponse('Havuz adı zorunludur.');
        }
        if (!$request->type || !in_array($request->type, ['quota', 'unlimited'])) {
            return $this->errorResponse('Havuz tipi seçimi zorunludur.');
        }
        if (!$request->api_key) {
            return $this->errorResponse('Localtonet API Key zorunludur.');
        }

        $tunnelIds = $this->parseTunnelIds($request->input('tunnel_ids_text', ''));

        $localtonetRotatingPool->fill([
            'name'       => $request->name,
            'type'       => $request->type,
            'api_key'    => $request->api_key,
            'tunnel_ids' => $tunnelIds,
        ]);

        if ($localtonetRotatingPool->save()) {
            return $this->successResponse('Localtonet Rotating havuzu başarıyla güncellendi.');
        }
        return $this->errorResponse();
    }

    public function delete(LocaltonetRotatingPool $localtonetRotatingPool)
    {
        if ($localtonetRotatingPool->delete()) {
            return $this->successResponse('Localtonet Rotating havuzu başarıyla silindi.');
        }
        return $this->errorResponse();
    }

    private function parseTunnelIds(string $raw): array
    {
        if (trim($raw) === '') return [];

        $lines = preg_split('/[\r\n,]+/', $raw);
        $ids = [];
        foreach ($lines as $line) {
            $val = trim($line);
            if ($val !== '' && is_numeric($val)) {
                $ids[] = (int) $val;
            }
        }
        return array_values(array_unique($ids));
    }
}
