<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpPool;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpPoolController extends Controller
{
    use AjaxResponses;

    public function ajax(Request $request)
    {
        $searchableColumns = ['id', 'name', 'created_at'];

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

        $query = DB::table('ip_pools')
            ->whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);

        $list              = $query->get();
        $countTotalRecords = $query->count();

        $countFilteredRecords = DB::table('ip_pools')
            ->whereRaw($whereSearch)
            ->count();

        $data = [];
        foreach ($list as $item) {
            $entries   = json_decode($item->entries ?: '[]', true);
            $tokenCnt  = count($entries);
            $ipCnt     = 0;
            foreach ($entries as $e) {
                $ipCnt += is_array($e['ips'] ?? null) ? count($e['ips']) : 0;
            }

            $data[] = [
                "<span data-id='{$item->id}'>{$item->id}</span>",
                e($item->name),
                "{$tokenCnt} token / {$ipCnt} IP",
                convertDateTime($item->created_at),
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' . __('actions') . '
                    <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="javascript:void(0);" class="menu-link px-3 ipPoolEditBtn" data-id="' . $item->id . '">'. __('edit') .'</a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="javascript:void(0);" class="menu-link px-3 ipPoolDeleteBtn" data-id="' . $item->id . '">'. __('delete') .'</a>
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

    public function show(IpPool $ipPool)
    {
        return $this->successResponse('', ['data' => $ipPool]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $entries = $this->parseEntries($request);

        $pool = IpPool::create([
            'name'    => $request->name,
            'entries' => $entries,
        ]);

        if ($pool) {
            return $this->successResponse('IP havuzu başarıyla oluşturuldu.');
        }
        return $this->errorResponse();
    }

    public function update(IpPool $ipPool, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $entries = $this->parseEntries($request);

        $ipPool->fill([
            'name'    => $request->name,
            'entries' => $entries,
        ]);

        if ($ipPool->save()) {
            return $this->successResponse('Değişiklikler başarıyla kaydedildi.');
        }
        return $this->errorResponse();
    }

    public function delete(IpPool $ipPool)
    {
        if ($ipPool->delete()) {
            return $this->successResponse('Kayıt başarıyla silindi.');
        }
        return $this->errorResponse();
    }

    private function parseEntries(Request $request): array
    {
        $tokens  = $request->input('entry_token', []);
        $ipLists = $request->input('entry_ips', []);

        if (! is_array($tokens)) $tokens = [];
        if (! is_array($ipLists)) $ipLists = [];

        $entries = [];
        foreach ($tokens as $i => $rawToken) {
            $token = trim((string) $rawToken);
            if ($token === '') continue;

            $rawIps = trim((string) ($ipLists[$i] ?? ''));
            $ips = $rawIps !== ''
                ? array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/u', $rawIps)))))
                : [];

            $entries[] = [
                'token' => $token,
                'ips'   => $ips,
            ];
        }

        return $entries;
    }
}
