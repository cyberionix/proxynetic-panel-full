<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThreeProxyServer;
use App\Models\TokenPool;
use App\Models\UserGroup;
use App\Services\ThreeProxyService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ThreeProxyServerController extends Controller
{
    use AjaxResponses;
    public function index()
    {
        return view("admin.pages.threeProxy.servers.index");
    }

    public function bulkCreate(ThreeProxyServer $ThreeProxyServer,Request $request)
    {
        $data = $request->proxies;
        $protocol = $request->protocol;
        $arr = explode("\r\n",$data);

        $error = false;
        $msg = '';
        $service = new ThreeProxyService($ThreeProxyServer->ip_address,$ThreeProxyServer->port,$ThreeProxyServer->api_key);
        foreach ($arr as $item) {
            if (!$item || count(explode(':',$item)) != 4) continue;
            [$ip,$port,$username,$password] = explode(':',$item);

            $result = $service->createProxy($ip,$port,$username,$password,$protocol);

            if(@$result['status'] != 'success'){
                $error = true;
                $msg = 'Bir hata oluştu: '.($result['message'] ?? 'Bilinmeyen hata #10001');
                break;
            }
        }


        if ($error)
            return $this->errorResponse($msg);
        return $this->successResponse('Proxy oluşturma işlemi başarılı.');
        return $arr;
    }
    public function proxyList(ThreeProxyServer $ThreeProxyServer)
    {

        $service = new ThreeProxyService($ThreeProxyServer->ip_address,$ThreeProxyServer->port,$ThreeProxyServer->api_key);

        $proxies = $service->getProxies();

        if ($proxies && $proxies['status'] == 'success'){
            $data = $proxies['proxies'];
        }else{
            return redirect()->back();
        }

        $threeProxy = $ThreeProxyServer;
        return view("admin.pages.threeProxy.proxyList",compact('data','threeProxy'));

    }
    public function ajax(Request $request)
    {
        $searchableColumns = [
            "id",
            "ip_address",
            "status",
            "last_checked_at"
        ];

        $whereSearch = "deleted_at IS NULL";

        if (isset($request->order[0]["column"]) and isset($request->order[0]["dir"])) {
            $orderBy = $searchableColumns[$request->order[0]["column"]] . " " . $request->order[0]["dir"];
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

        $query = ThreeProxyServer::whereRaw($whereSearch)
            ->orderByRaw($orderBy)
            ->skip($start)->take($length);


        $list = $query->get();
        $countTotalRecords = $query->count();

        $query = ThreeProxyServer::whereRaw($whereSearch)
            ->orderByRaw($orderBy);

        $countFilteredRecords = $query->count();
        $data = [];

        foreach ($list as $item) {
            if ($item->is_active){
                $statusHtml = '<i class="fa fa-check-circle fs-2x text-success"></i>';
            }else{
                $statusHtml = '<i class="fa fa-times-circle fs-2x text-danger"></i>';
            }
            $data[] = [
                "<span data-ip='".$item->ip_address."' data-is-active='".($item->is_active ? 1 : 0)."' data-port='".$item->port."' data-api-key='".$item->api_key."' data-id='".$item->id."'>".$item->id."</span>",
                $item->ip_address.' : '.$item->port,
                $statusHtml,
                $item->created_at ? convertDateTime($item->created_at) : '-',
                '<a href="'.route('admin.products.3proxy.servers.proxyList',['ThreeProxyServer' => $item->id]).'"><button class="btn btn-sm btn-primary">Proxyler</button></a>',
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">'.__("actions").'
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 editBtn">'.__("edit").'</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0);" class="menu-link px-3 deleteBtn">'.__("delete").'</a>
                                        </div>
                                    </div>
                                   ',
            ];
        }

        $response = array(
            'recordsTotal' => $countTotalRecords,
            'recordsFiltered' => $countFilteredRecords,
            'data' => $data
        );
        echo json_encode($response);
    }

    public function show(TokenPool $tokenPool)
    {
        return $this->successResponse('',['data' => $tokenPool]);
    }
    public function store(Request $request)
    {
        $data = $request->only(['ip_address','port','api_key']);
        $data['is_active'] = $request->is_active ? 1 : 0;

        $create = ThreeProxyServer::create($data);
        if ($create){
            return $this->successResponse(__("created_response", ["name" => 'Sunucu']));
        }else{
            return $this->errorResponse();
        }
    }

    public function update(Request $request)
    {
        $server = ThreeProxyServer::find($request->id);
        if (!$server){
            return $this->errorResponse();
        }
        $data = $request->only(['ip_address','port','api_key']);
        $data['is_active'] = $request->is_active ? 1 : 0;


        if ($server->update($data)){
            return $this->successResponse(__("edited_response", ["name" => 'Sunucu']));

        }else{
            return $this->errorResponse();
        }
    }

    public function delete(Request $request)
    {
        $userGroup = UserGroup::find($request->id);

        if (!$userGroup) {
            return $this->errorResponse(__("record_not_found"));
        }

        if ($userGroup->delete()) {
            return $this->successResponse(__("family_group_deleted"));
        }

        return $this->errorResponse();
    }

}
