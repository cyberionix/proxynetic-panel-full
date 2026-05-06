@extends("admin.template")
@section("title", 'Havuz Yönetimi')
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
<x-admin.bread-crumb data="Havuz Yönetimi"/>
@endsection
@section("master")
    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-8 fs-5 fw-bold">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#authTokenPoolTab">Auth Token Havuzu</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#ipPoolTab">IP Havuzları</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#threeProxyPoolTab">3Proxy Havuzu</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#localtonetRotatingPoolTab">Localtonet Rotating</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#pproxyuPoolTab">PProxyU</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="authTokenPoolTab">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-table-action="search"
                           class="form-control  w-250px ps-13"
                           placeholder="{{__("search_in_table")}}"/>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                    <button type="button" class="btn btn-primary addBtn"><i class="fa fa-plus fs-5"></i> Yeni Oluştur</button>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="tokenPoolsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="m-w-50">#</th>
                    <th class="min-w-125px">{{__("title")}}</th>
                    <th class="min-w-125px">{{__("created_date")}}</th>
                    <th class="min-w-125px">Token Sayısı</th>
                    <th class="min-w-125px"></th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="primaryGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>{{__("create")}}</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="modal-body py-lg-10 px-lg-15">
                    <form id="tokenPoolForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row mb-7">
                            <label class="required form-label mb-3">{{__("title")}}</label>
                            <input type="text" name="name" class="form-control form-control-lg " required>
                        </div>
                        <div class="fv-row">
                            <label class="required form-label mb-3">Auth Token Seçimi</label>
                            <x-admin.form-elements.auth-token-select
                                name="auth_tokens[]"
                                customAttr="multiple"
                                customClass="mw-100"/>
                        </div>
                        <div class="d-flex flex-center flex-row-fluid pt-12">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="user_group_submit_btn">
                                <span class="indicator-label">{{__("save")}}</span>
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>

        <div class="tab-pane fade" id="threeProxyPoolTab">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-tp-table-action="search"
                           class="form-control w-250px ps-13"
                           placeholder="{{__("search_in_table")}}"/>
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary tpAddBtn"><i class="fa fa-plus fs-5"></i> Yeni 3Proxy Havuzu</button>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="threeProxyPoolsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-50px">#</th>
                    <th class="min-w-125px">Havuz Adı</th>
                    <th class="min-w-100px">Sunucular</th>
                    <th class="min-w-80px">Toplam IP</th>
                    <th class="min-w-100px">{{__("created_date")}}</th>
                    <th class="min-w-100px"></th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="threeProxyPoolModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>3Proxy Havuzu Oluştur</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-15">
                    <form id="threeProxyPoolForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row mb-7">
                            <label class="required form-label mb-3">Havuz Adı</label>
                            <input type="text" name="name" class="form-control" required placeholder="Örn: Almanya Havuzu">
                        </div>

                        <div class="separator my-5"></div>
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h4 class="mb-0">Sunucular</h4>
                            <button type="button" class="btn btn-sm btn-success" id="tp_add_server_btn">
                                <i class="fa fa-plus me-1"></i> Sunucu Ekle
                            </button>
                        </div>

                        <div id="tp_servers_container"></div>

                        <div class="d-flex flex-center flex-row-fluid pt-8">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="tp_pool_submit_btn">
                                <span class="indicator-label">{{__("save")}}</span>
                                <span class="indicator-progress">{{__("please_wait")}}...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>

        <div class="tab-pane fade" id="ipPoolTab">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-ip-pool-table-action="search"
                           class="form-control w-250px ps-13"
                           placeholder="{{__("search_in_table")}}"/>
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary ipPoolAddBtn"><i class="fa fa-plus fs-5"></i> Yeni Havuz Oluştur</button>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="ipPoolsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-50px">#</th>
                    <th class="min-w-125px">Havuz Adı</th>
                    <th class="min-w-125px">İçerik</th>
                    <th class="min-w-125px">{{__("created_date")}}</th>
                    <th class="min-w-100px"></th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="ipPoolModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>IP Havuzu Oluştur</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-15">
                    <form id="ipPoolForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row mb-7">
                            <label class="required form-label mb-3">Havuz Adı</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <label class="form-label fw-bold mb-0">Auth Token &amp; IP Satırları</label>
                            <button type="button" class="btn btn-sm btn-light-primary" data-ip-pool-entry="add">
                                <i class="fa fa-plus me-1"></i>Satır ekle
                            </button>
                        </div>
                        <div data-ip-pool-entry="items">
                            <div class="border rounded p-4 mb-4" data-ip-pool-entry="row">
                                <div class="mb-4">
                                    <label class="form-label">Auth Token</label>
                                    <input type="text" class="form-control" name="entry_token[]">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">IP Listesi</label>
                                    <textarea class="form-control" rows="3" name="entry_ips[]"
                                              placeholder="Her satırda bir IP"></textarea>
                                </div>
                                <button type="button" class="btn btn-sm btn-light-danger" data-ip-pool-entry="remove">Satırı kaldır</button>
                            </div>
                        </div>
                        <div class="d-flex flex-center flex-row-fluid pt-8">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="ip_pool_submit_btn">
                                <span class="indicator-label">{{__("save")}}</span>
                                <span class="indicator-progress">{{__("please_wait")}}...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>

        <div class="tab-pane fade" id="localtonetRotatingPoolTab">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-lrp-table-action="search"
                           class="form-control w-250px ps-13"
                           placeholder="{{__("search_in_table")}}"/>
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary lrpAddBtn"><i class="fa fa-plus fs-5"></i> Yeni Localtonet Rotating Havuzu</button>
            </div>
        </div>
        <div class="card-body pt-0">
            <table id="localtonetRotatingPoolsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-50px">#</th>
                    <th class="min-w-125px">Havuz Adı</th>
                    <th class="min-w-80px">Tip</th>
                    <th class="min-w-80px">Tunnel Sayısı</th>
                    <th class="min-w-100px">{{__("created_date")}}</th>
                    <th class="min-w-100px"></th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="localtonetRotatingPoolModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Localtonet Rotating Havuzu Oluştur</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-15">
                    <form id="localtonetRotatingPoolForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row mb-7">
                            <label class="required form-label mb-3">Havuz Adı</label>
                            <input type="text" name="name" class="form-control" required placeholder="Örn: Türkiye Rotating Kotalı">
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required form-label mb-3">Havuz Tipi</label>
                            <select name="type" class="form-select" required>
                                <option value="quota">Tunnel ID (Kotalı)</option>
                                <option value="unlimited">Tunnel ID (Sınırsız)</option>
                            </select>
                            <div class="form-text">Kotalı: Bandwidth limiti olan tunnel'lar. Sınırsız: Limit olmayan tunnel'lar.</div>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required form-label mb-3">Localtonet API Key</label>
                            <input type="text" name="api_key" class="form-control" required placeholder="Localtonet API Key girin">
                            <div class="form-text">Localtonet hesabınızdaki API anahtarını buraya girin. Shared Proxy tunnel yönetimi için gereklidir.</div>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="form-label mb-3">Tunnel ID Listesi</label>
                            <textarea name="tunnel_ids_text" class="form-control" rows="6" placeholder="Her satırda bir Tunnel ID&#10;Örn:&#10;12345&#10;12346&#10;12347"></textarea>
                            <div class="form-text">Her satırda bir Tunnel ID girin. Localtonet panelindeki Shared Proxy Tunnel ID'lerini buraya ekleyin.</div>
                        </div>
                        <div class="d-flex flex-center flex-row-fluid pt-8">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="lrp_pool_submit_btn">
                                <span class="indicator-label">{{__("save")}}</span>
                                <span class="indicator-progress">{{__("please_wait")}}...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>

        <div class="tab-pane fade" id="pproxyuPoolTab">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" data-pproxyu-table-action="search"
                                   class="form-control w-250px ps-13"
                                   placeholder="Ara..."/>
                        </div>
                    </div>
                    <div class="card-toolbar gap-3">
                        <button type="button" class="btn btn-light-primary pproxyuBulkImportBtn">
                            <i class="fa fa-file-import me-1"></i> Toplu İçe Aktar
                        </button>
                        <button type="button" class="btn btn-primary pproxyuAddBtn">
                            <i class="fa fa-plus me-1"></i> Yeni Ekle
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <table id="pproxyuPoolTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px">#</th>
                            <th class="min-w-125px">IP:Port</th>
                            <th class="min-w-100px">Kullanıcı</th>
                            <th class="min-w-100px">Şifre</th>
                            <th class="min-w-100px">Etiket</th>
                            <th class="min-w-80px">Durum</th>
                            <th class="min-w-100px">Tarih</th>
                            <th class="min-w-100px">İşlem</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal fade" id="pproxyuPoolModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 id="pproxyuModalTitle">Yeni Proxy Ekle</h2>
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>
                        <div class="modal-body py-lg-10 px-lg-15">
                            <form id="pproxyuPoolForm">
                                @csrf
                                <input type="hidden" name="url">
                                <input type="hidden" name="id">
                                <div class="row g-5 mb-5">
                                    <div class="col-md-8">
                                        <label class="required form-label">IP Adresi</label>
                                        <input type="text" class="form-control" name="ip" placeholder="192.168.1.1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="required form-label">Port</label>
                                        <input type="number" class="form-control" name="port" placeholder="8080" min="1" max="65535" required>
                                    </div>
                                </div>
                                <div class="row g-5 mb-5">
                                    <div class="col-md-6">
                                        <label class="required form-label">Kullanıcı Adı</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="required form-label">Şifre</label>
                                        <input type="text" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="fv-row mb-5">
                                    <label class="form-label">Etiket</label>
                                    <input type="text" class="form-control" name="label" placeholder="Opsiyonel etiket">
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="form-label">Durum</label>
                                    <select class="form-select" name="is_active">
                                        <option value="1">Aktif</option>
                                        <option value="0">Pasif</option>
                                    </select>
                                </div>
                                <div class="d-flex flex-center flex-row-fluid pt-4">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Vazgeç</button>
                                    <button type="submit" class="btn btn-primary" id="pproxyu_pool_submit_btn">
                                        <span class="indicator-label">Kaydet</span>
                                        <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="pproxyuBulkImportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Toplu Proxy İçe Aktar</h2>
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>
                        <div class="modal-body py-lg-10 px-lg-15">
                            <form id="pproxyuBulkImportForm">
                                @csrf
                                <div class="fv-row mb-5">
                                    <label class="required form-label">Proxy Listesi</label>
                                    <textarea class="form-control" name="proxies" rows="10" placeholder="Her satırda bir proxy&#10;Format: ip:port:kullanıcı:şifre&#10;&#10;Örnek:&#10;1.2.3.4:8080:user1:pass1&#10;5.6.7.8:8080:user2:pass2" required></textarea>
                                    <div class="form-text">Her satırda <code>ip:port:kullanıcı:şifre</code> formatında girin.</div>
                                </div>
                                <div class="d-flex flex-center flex-row-fluid pt-4">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Vazgeç</button>
                                    <button type="submit" class="btn btn-primary" id="pproxyu_bulk_submit_btn">
                                        <span class="indicator-label">İçe Aktar</span>
                                        <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            /* ===== IP POOLS ===== */
            var ipT = $("#ipPoolsTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: true, targets: 0 },
                    { orderable: true, targets: 1 },
                    { orderable: false, targets: 2 },
                    { orderable: true, targets: 3 },
                    { orderable: false, targets: 4 }
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.ipPools.ajax') }}",
                    type: "POST",
                    data: function (d) { d._token = "{{ csrf_token() }}"; }
                }
            }).on("draw", function () { KTMenu.createInstances(); });

            document.querySelector('[data-ip-pool-table-action="search"]').addEventListener("keyup", function (e) {
                ipT.search(e.target.value).draw();
            });

            $(document).on('click', '[data-ip-pool-entry="add"]', function () {
                var tpl = '<div class="border rounded p-4 mb-4" data-ip-pool-entry="row">' +
                    '<div class="mb-4"><label class="form-label">Auth Token</label>' +
                    '<input type="text" class="form-control" name="entry_token[]"></div>' +
                    '<div class="mb-3"><label class="form-label">IP Listesi</label>' +
                    '<textarea class="form-control" rows="3" name="entry_ips[]" placeholder="Her satırda bir IP"></textarea></div>' +
                    '<button type="button" class="btn btn-sm btn-light-danger" data-ip-pool-entry="remove">Satırı kaldır</button></div>';
                $('[data-ip-pool-entry="items"]').append(tpl);
            });
            $(document).on('click', '[data-ip-pool-entry="remove"]', function () {
                if ($('[data-ip-pool-entry="row"]').length > 1) $(this).closest('[data-ip-pool-entry="row"]').remove();
            });

            $(document).on("click", ".ipPoolAddBtn", function () {
                $("#ipPoolForm [name='url']").val("{{ route('admin.ipPools.store') }}");
                $("#ipPoolForm [name='name']").val("");
                $("#ipPoolForm [name='id']").val("");
                $('[data-ip-pool-entry="items"]').html(
                    '<div class="border rounded p-4 mb-4" data-ip-pool-entry="row">' +
                    '<div class="mb-4"><label class="form-label">Auth Token</label>' +
                    '<input type="text" class="form-control" name="entry_token[]"></div>' +
                    '<div class="mb-3"><label class="form-label">IP Listesi</label>' +
                    '<textarea class="form-control" rows="3" name="entry_ips[]" placeholder="Her satırda bir IP"></textarea></div>' +
                    '<button type="button" class="btn btn-sm btn-light-danger" data-ip-pool-entry="remove">Satırı kaldır</button></div>'
                );
                $("#ipPoolModal .modal-header h2").html("IP Havuzu Oluştur");
                $("#ipPoolModal").modal("show");
            });

            $(document).on("click", ".ipPoolEditBtn", function () {
                var poolId = $(this).data("id");
                var showUrl = "{{ route('admin.ipPools.show', ['ipPool' => '__placeholder__']) }}".replace('__placeholder__', poolId);
                var updateUrl = "{{ route('admin.ipPools.update', ['ipPool' => '__placeholder__']) }}".replace('__placeholder__', poolId);

                $.get(showUrl, function (res) {
                    if (!res.success) return;
                    var pool = res.data;
                    $("#ipPoolForm [name='url']").val(updateUrl);
                    $("#ipPoolForm [name='name']").val(pool.name);
                    $("#ipPoolForm [name='id']").val(pool.id);

                    var entries = pool.entries || [];
                    var html = '';
                    if (entries.length === 0) entries = [{ token: '', ips: [] }];
                    $.each(entries, function (i, entry) {
                        html += '<div class="border rounded p-4 mb-4" data-ip-pool-entry="row">' +
                            '<div class="mb-4"><label class="form-label">Auth Token</label>' +
                            '<input type="text" class="form-control" name="entry_token[]" value="' + (entry.token || '') + '"></div>' +
                            '<div class="mb-3"><label class="form-label">IP Listesi</label>' +
                            '<textarea class="form-control" rows="3" name="entry_ips[]" placeholder="Her satırda bir IP">' + (entry.ips ? entry.ips.join("\n") : '') + '</textarea></div>' +
                            '<button type="button" class="btn btn-sm btn-light-danger" data-ip-pool-entry="remove">Satırı kaldır</button></div>';
                    });
                    $('[data-ip-pool-entry="items"]').html(html);
                    $("#ipPoolModal .modal-header h2").html("IP Havuzu Düzenle");
                    $("#ipPoolModal").modal("show");
                });
            });

            $(document).on("click", ".ipPoolDeleteBtn", function () {
                var poolId = $(this).data("id");
                var url = "{{ route('admin.ipPools.delete', ['ipPool' => '__placeholder__']) }}".replace('__placeholder__', poolId);
                Swal.fire({
                    icon: 'warning', title: "{{ __('warning') }}", text: '{{ __("are_you_sure_you_want_to_delete_it") }}',
                    showConfirmButton: 1, showCancelButton: 1, cancelButtonText: "{{ __('close') }}", confirmButtonText: "{{ __('yes') }}"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({ type: "POST", url: url, dataType: "json", data: { _token: "{{ csrf_token() }}" },
                            complete: function (data) {
                                var res = data.responseJSON;
                                if (res && res.success) {
                                    Swal.fire({ title: "{{ __('success') }}", text: res.message, icon: "success", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                                    ipT.draw();
                                } else {
                                    Swal.fire({ title: "{{ __('error') }}", text: res?.message || "{{ __('form_has_errors') }}", icon: "error", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("submit", "#ipPoolForm", function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.delete("url");
                $.ajax({
                    type: 'POST', url: $("#ipPoolForm [name='url']").val(), data: formData, dataType: 'json',
                    contentType: false, processData: false, cache: false,
                    beforeSend: function () { propSubmitButton($("#ip_pool_submit_btn"), 1); },
                    complete: function (data) {
                        propSubmitButton($("#ip_pool_submit_btn"), 0);
                        var res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({ title: "{{ __('success') }}", text: res.message, icon: "success", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                            ipT.draw();
                            $("#ipPoolModal").modal("hide");
                        } else {
                            Swal.fire({ title: "{{ __('error') }}", text: res?.message || "{{ __('form_has_errors') }}", icon: "error", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                        }
                    }
                });
            });

            /* ===== 3PROXY POOLS ===== */
            var tpT = $("#threeProxyPoolsTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: true, targets: 0 },
                    { orderable: true, targets: 1 },
                    { orderable: false, targets: 2 },
                    { orderable: false, targets: 3 },
                    { orderable: true, targets: 4 },
                    { orderable: false, targets: 5 }
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.products.3proxyPools.ajax') }}",
                    type: "POST",
                    data: function (d) { d._token = "{{ csrf_token() }}"; }
                }
            }).on("draw", function () { KTMenu.createInstances(); });

            document.querySelector('[data-tp-table-action="search"]').addEventListener("keyup", function (e) {
                tpT.search(e.target.value).draw();
            });

            var tpServerIdx = 0;
            function tpAddServerCard(data) {
                data = data || {};
                var idx = tpServerIdx++;
                var html = '<div class="card border border-dashed border-gray-300 mb-5 tp-server-card" data-idx="' + idx + '">' +
                    '<div class="card-header min-h-50px px-5">' +
                        '<div class="card-title"><h6 class="mb-0"><i class="fa fa-server me-2 text-primary"></i>Sunucu #' + (idx + 1) + '</h6></div>' +
                        '<div class="card-toolbar"><button type="button" class="btn btn-sm btn-icon btn-light-danger tp-remove-server-btn"><i class="fa fa-trash"></i></button></div>' +
                    '</div>' +
                    '<div class="card-body py-4 px-5">' +
                        '<div class="row mb-4">' +
                            '<div class="col-md-6"><label class="required form-label mb-2 fs-7">Sunucu IP</label>' +
                            '<input type="text" name="servers[' + idx + '][server_ip]" class="form-control form-control-sm" placeholder="212.98.228.115" value="' + (data.server_ip || '') + '"></div>' +
                            '<div class="col-md-6"><label class="required form-label mb-2 fs-7">API Port</label>' +
                            '<input type="number" name="servers[' + idx + '][port]" class="form-control form-control-sm" placeholder="7000" value="' + (data.port || 7000) + '"></div>' +
                        '</div>' +
                        '<div class="row mb-4">' +
                            '<div class="col-md-6"><label class="required form-label mb-2 fs-7">Auth Username</label>' +
                            '<input type="text" name="servers[' + idx + '][auth_username]" class="form-control form-control-sm" placeholder="Admin kullanıcı adı" value="' + (data.auth_username || '') + '"></div>' +
                            '<div class="col-md-6"><label class="required form-label mb-2 fs-7">Auth Password</label>' +
                            '<input type="text" name="servers[' + idx + '][auth_password]" class="form-control form-control-sm" placeholder="Admin şifresi" value="' + (data.auth_password || '') + '"></div>' +
                        '</div>' +
                        '<div class="mb-0"><label class="form-label mb-2 fs-7">IP Listesi</label>' +
                        '<textarea name="servers[' + idx + '][ip_list]" class="form-control form-control-sm" rows="4" placeholder="Her satırda bir IP adresi">' + (data.ip_list || '') + '</textarea></div>' +
                    '</div></div>';
                $("#tp_servers_container").append(html);
                tpRenumberCards();
            }

            function tpRenumberCards() {
                $("#tp_servers_container .tp-server-card").each(function (i) {
                    $(this).find(".card-title h6").html('<i class="fa fa-server me-2 text-primary"></i>Sunucu #' + (i + 1));
                });
                if ($("#tp_servers_container .tp-server-card").length <= 1) {
                    $(".tp-remove-server-btn").hide();
                } else {
                    $(".tp-remove-server-btn").show();
                }
            }

            $(document).on("click", "#tp_add_server_btn", function () {
                tpAddServerCard();
            });

            $(document).on("click", ".tp-remove-server-btn", function () {
                $(this).closest(".tp-server-card").remove();
                tpRenumberCards();
            });

            $(document).on("click", ".tpAddBtn", function () {
                $("#threeProxyPoolForm [name='url']").val("{{ route('admin.products.3proxyPools.store') }}");
                $("#threeProxyPoolForm [name='name']").val("");
                $("#threeProxyPoolForm [name='id']").val("");
                $("#tp_servers_container").html("");
                tpServerIdx = 0;
                tpAddServerCard();
                $("#threeProxyPoolModal .modal-header h2").html("3Proxy Havuzu Oluştur");
                $("#threeProxyPoolModal").modal("show");
            });

            $(document).on("click", ".tpEditBtn", function () {
                var span = $(this).closest("tr").find("td:first span");
                var id = span.data("id");
                var updateUrl = "{{ route('admin.products.3proxyPools.update', ['threeProxyPool' => '__placeholder__']) }}".replace('__placeholder__', id);
                var showUrl = "{{ route('admin.products.3proxyPools.show', ['threeProxyPool' => '__placeholder__']) }}".replace('__placeholder__', id);

                $.ajax({
                    url: showUrl, type: "GET", dataType: "json",
                    success: function (res) {
                        if (!res.success) return;
                        var pool = res.data;
                        $("#threeProxyPoolForm [name='url']").val(updateUrl);
                        $("#threeProxyPoolForm [name='id']").val(id);
                        $("#threeProxyPoolForm [name='name']").val(pool.name);
                        $("#tp_servers_container").html("");
                        tpServerIdx = 0;

                        if (pool.servers && pool.servers.length > 0) {
                            pool.servers.forEach(function (s) {
                                tpAddServerCard(s);
                            });
                        } else {
                            tpAddServerCard({
                                server_ip: pool.server_ip,
                                port: pool.port,
                                auth_username: pool.auth_username,
                                auth_password: pool.auth_password,
                                ip_list: pool.ip_list
                            });
                        }

                        $("#threeProxyPoolModal .modal-header h2").html("3Proxy Havuzu Düzenle");
                        $("#threeProxyPoolModal").modal("show");
                    }
                });
            });

            $(document).on("click", ".tpDeleteBtn", function () {
                var id = $(this).closest("tr").find("td:first span").data("id");
                var url = "{{ route('admin.products.3proxyPools.delete', ['threeProxyPool' => '__placeholder__']) }}".replace('__placeholder__', id);
                Swal.fire({
                    icon: 'warning', title: "{{ __('warning') }}", text: '{{ __("are_you_sure_you_want_to_delete_it") }}',
                    showConfirmButton: 1, showCancelButton: 1, cancelButtonText: "{{ __('close') }}", confirmButtonText: "{{ __('yes') }}"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({ type: "POST", url: url, dataType: "json", data: { _token: "{{ csrf_token() }}" },
                            complete: function (data) {
                                var res = data.responseJSON;
                                if (res && res.success) {
                                    Swal.fire({ title: "{{ __('success') }}", text: res.message, icon: "success", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                                    tpT.draw();
                                } else {
                                    Swal.fire({ title: "{{ __('error') }}", text: res?.message || "{{ __('form_has_errors') }}", icon: "error", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("submit", "#threeProxyPoolForm", function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.delete("url");
                $.ajax({
                    type: 'POST', url: $("#threeProxyPoolForm [name='url']").val(), data: formData, dataType: 'json',
                    contentType: false, processData: false, cache: false,
                    beforeSend: function () { propSubmitButton($("#tp_pool_submit_btn"), 1); },
                    complete: function (data) {
                        propSubmitButton($("#tp_pool_submit_btn"), 0);
                        var res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({ title: "{{ __('success') }}", text: res.message, icon: "success", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                            tpT.draw();
                            $("#threeProxyPoolModal").modal("hide");
                        } else {
                            Swal.fire({ title: "{{ __('error') }}", text: res?.message || "{{ __('form_has_errors') }}", icon: "error", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                        }
                    }
                });
            });

            /* ===== LOCALTONET ROTATING POOLS ===== */
            var lrpT = $("#localtonetRotatingPoolsTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: true, targets: 0 },
                    { orderable: true, targets: 1 },
                    { orderable: true, targets: 2 },
                    { orderable: false, targets: 3 },
                    { orderable: true, targets: 4 },
                    { orderable: false, targets: 5 }
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.localtonetRotatingPools.ajax') }}",
                    type: "POST",
                    data: function (d) { d._token = "{{ csrf_token() }}"; }
                }
            }).on("draw", function () { KTMenu.createInstances(); });

            document.querySelector('[data-lrp-table-action="search"]').addEventListener("keyup", function (e) {
                lrpT.search(e.target.value).draw();
            });

            $(document).on("click", ".lrpAddBtn", function () {
                $("#localtonetRotatingPoolForm [name='url']").val("{{ route('admin.localtonetRotatingPools.store') }}");
                $("#localtonetRotatingPoolForm [name='name']").val("");
                $("#localtonetRotatingPoolForm [name='type']").val("quota");
                $("#localtonetRotatingPoolForm [name='api_key']").val("");
                $("#localtonetRotatingPoolForm [name='tunnel_ids_text']").val("");
                $("#localtonetRotatingPoolForm [name='id']").val("");
                $("#localtonetRotatingPoolModal .modal-header h2").html("Localtonet Rotating Havuzu Oluştur");
                $("#localtonetRotatingPoolModal").modal("show");
            });

            $(document).on("click", ".lrpEditBtn", function () {
                var span = $(this).closest("tr").find("td:first span");
                var id = span.data("id");
                var updateUrl = "{{ route('admin.localtonetRotatingPools.update', ['localtonetRotatingPool' => '__placeholder__']) }}".replace('__placeholder__', id);

                $("#localtonetRotatingPoolForm [name='url']").val(updateUrl);
                $("#localtonetRotatingPoolForm [name='id']").val(id);
                $("#localtonetRotatingPoolForm [name='name']").val(span.data("name"));
                $("#localtonetRotatingPoolForm [name='type']").val(span.data("type"));
                $("#localtonetRotatingPoolForm [name='api_key']").val(span.data("api-key"));

                var tunnelIds = span.data("tunnel-ids");
                var tunnelText = '';
                if (tunnelIds && Array.isArray(tunnelIds)) {
                    tunnelText = tunnelIds.join("\n");
                } else if (typeof tunnelIds === 'string') {
                    try {
                        var parsed = JSON.parse(tunnelIds);
                        if (Array.isArray(parsed)) tunnelText = parsed.join("\n");
                    } catch(e) {}
                }
                $("#localtonetRotatingPoolForm [name='tunnel_ids_text']").val(tunnelText);
                $("#localtonetRotatingPoolModal .modal-header h2").html("Localtonet Rotating Havuzu Düzenle");
                $("#localtonetRotatingPoolModal").modal("show");
            });

            $(document).on("click", ".lrpDeleteBtn", function () {
                var id = $(this).closest("tr").find("td:first span").data("id");
                var url = "{{ route('admin.localtonetRotatingPools.delete', ['localtonetRotatingPool' => '__placeholder__']) }}".replace('__placeholder__', id);
                Swal.fire({
                    icon: 'warning', title: "{{ __('warning') }}", text: '{{ __("are_you_sure_you_want_to_delete_it") }}',
                    showConfirmButton: 1, showCancelButton: 1, cancelButtonText: "{{ __('close') }}", confirmButtonText: "{{ __('yes') }}"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({ type: "POST", url: url, dataType: "json", data: { _token: "{{ csrf_token() }}" },
                            complete: function (data) {
                                var res = data.responseJSON;
                                if (res && res.success) {
                                    Swal.fire({ title: "{{ __('success') }}", text: res.message, icon: "success", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                                    lrpT.draw();
                                } else {
                                    Swal.fire({ title: "{{ __('error') }}", text: res?.message || "{{ __('form_has_errors') }}", icon: "error", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("submit", "#localtonetRotatingPoolForm", function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.delete("url");
                $.ajax({
                    type: 'POST', url: $("#localtonetRotatingPoolForm [name='url']").val(), data: formData, dataType: 'json',
                    contentType: false, processData: false, cache: false,
                    beforeSend: function () { propSubmitButton($("#lrp_pool_submit_btn"), 1); },
                    complete: function (data) {
                        propSubmitButton($("#lrp_pool_submit_btn"), 0);
                        var res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({ title: "{{ __('success') }}", text: res.message, icon: "success", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                            lrpT.draw();
                            $("#localtonetRotatingPoolModal").modal("hide");
                        } else {
                            Swal.fire({ title: "{{ __('error') }}", text: res?.message || "{{ __('form_has_errors') }}", icon: "error", showConfirmButton: 0, showCancelButton: 1, cancelButtonText: "{{ __('close') }}" });
                        }
                    }
                });
            });

            /* ===== AUTH TOKEN POOLS ===== */
            var t = $("#tokenPoolsTable").DataTable({
                order: [],
                columnDefs: [
                    {
                        orderable: !0, targets: 0
                    },
                    {
                        orderable: !0, targets: 1
                    },
                    {
                        orderable: !0, targets: 2
                    },
                    {
                        orderable: !1, targets: 3
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.tokenPools.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".addBtn", function () {
                $("#tokenPoolForm [name='url']").val("{{route('admin.tokenPools.store')}}")
                $("#tokenPoolForm [name='name']").val("")
                $("#primaryGroupModal .modal-header h2").html("{{__("create")}}")
                $("#primaryGroupModal").modal("show")
            })
            $(document).on("click", ".editBtn", function () {
                let id, url, name, tokens;
                id = $(this).closest("tr").find("td:first span").data("id");
                name = $(this).closest("tr").find("td:first span").data("name");
                tokens = $(this).closest("tr").find("td:first span").data("tokens");

                url = `{{ route('admin.tokenPools.update', ['tokenPool' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);
                $("#tokenPoolForm [name='url']").val(url)
                $("#tokenPoolForm [name='name']").val(name)
                $("#tokenPoolForm [name='auth_tokens[]'] option").prop('selected',false);
                if(tokens){
                tokens = tokens.split(',');
                $.each(tokens,function(index,item){
                    $("#tokenPoolForm [name='auth_tokens[]'] option[value='"+item+"']").prop('selected',true);
                })
            }
                setTimeout(function(){
                    $("#tokenPoolForm [name='auth_tokens[]']").trigger('change');
                },200)
                $("#tokenPoolForm [name='id']").val(id)
                $("#tokenPoolForm [name='groupName']").val($(this).closest("tr").find("td:nth-child(2)").html())
                $("#primaryGroupModal .modal-header h2").html("{{__("edit")}}")
                $("#primaryGroupModal").modal("show")
            })
            $(document).on("click", ".deleteBtn", function () {
                let id = $(this).closest("tr").find("td:first span").data("id");

                let  url;
                id = $(this).closest("tr").find("td:first span").data("id");

                url = `{{ route('admin.tokenPools.delete', ['tokenPool' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: '{{__("are_you_sure_you_want_to_delete_it")}}',
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                id: id
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res.message,
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    })
                                    t.draw();
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res.message ? res.message : "{{__('form_has_errors')}}",
                                        icon: "error",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    })
                                }
                            }
                        })
                    }
                });
            })

            $(document).on("submit", "#tokenPoolForm", function (e) {
                e.preventDefault()
                let formData = new FormData(this);
                formData.delete("url");

                $.ajax({
                    type: 'POST',
                    url:  $("#tokenPoolForm [name='url']").val(),
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function (){
                        propSubmitButton($("#user_group_submit_btn"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton($("#user_group_submit_btn"), 0);
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            })
                            t.draw();
                            $("#primaryGroupModal").modal("hide");
                            $("#tokenPoolForm [name='groupName']").val("");
                            $("#tokenPoolForm [name='url']").val("");
                            $("#tokenPoolForm [name='id']").val("");
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                    }
                })
            })

            /* ===== PPROXYU POOL ===== */
            var ppuT = $("#pproxyuPoolTable").DataTable({
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: true, targets: [0, 1, 2, 5, 6] },
                    { orderable: false, targets: [3, 4, 7] }
                ],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.pproxyuPool.ajax') }}",
                    type: "POST",
                    data: function (d) { d._token = "{{ csrf_token() }}"; }
                }
            }).on("draw", function () { KTMenu.createInstances(); });

            document.querySelector('[data-pproxyu-table-action="search"]').addEventListener("keyup", function (e) {
                ppuT.search(e.target.value).draw();
            });

            $(document).on("click", ".pproxyuAddBtn", function () {
                $("#pproxyuPoolForm [name='url']").val("{{ route('admin.pproxyuPool.store') }}");
                $("#pproxyuPoolForm [name='id']").val("");
                $("#pproxyuPoolForm [name='ip']").val("");
                $("#pproxyuPoolForm [name='port']").val("");
                $("#pproxyuPoolForm [name='username']").val("");
                $("#pproxyuPoolForm [name='password']").val("");
                $("#pproxyuPoolForm [name='label']").val("");
                $("#pproxyuPoolForm [name='is_active']").val("1");
                $("#pproxyuModalTitle").text("Yeni Proxy Ekle");
                $("#pproxyuPoolModal").modal("show");
            });

            $(document).on("click", ".pproxyu-edit-btn", function (e) {
                e.preventDefault();
                var btn = $(this);
                var id = btn.data("id");
                var url = "{{ route('admin.pproxyuPool.update', ['id' => '__ID__']) }}".replace('__ID__', id);
                $("#pproxyuPoolForm [name='url']").val(url);
                $("#pproxyuPoolForm [name='id']").val(id);
                $("#pproxyuPoolForm [name='ip']").val(btn.data("ip"));
                $("#pproxyuPoolForm [name='port']").val(btn.data("port"));
                $("#pproxyuPoolForm [name='username']").val(btn.data("username"));
                $("#pproxyuPoolForm [name='password']").val(btn.data("password"));
                $("#pproxyuPoolForm [name='label']").val(btn.data("label"));
                $("#pproxyuPoolForm [name='is_active']").val(btn.data("is-active"));
                $("#pproxyuModalTitle").text("Proxy Düzenle");
                $("#pproxyuPoolModal").modal("show");
            });

            $(document).on("click", ".pproxyu-delete-btn", function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                var url = "{{ route('admin.pproxyuPool.destroy', ['id' => '__ID__']) }}".replace('__ID__', id);
                Swal.fire({
                    icon: 'warning', title: 'Uyarı', text: 'Bu proxyyi silmek istediğinize emin misiniz?',
                    showConfirmButton: true, showCancelButton: true,
                    cancelButtonText: 'Vazgeç', confirmButtonText: 'Evet, Sil'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST", url: url, dataType: "json",
                            data: { _token: "{{ csrf_token() }}" },
                            complete: function (data) {
                                var res = data.responseJSON;
                                if (res && res.success) {
                                    Swal.fire({ title: 'Başarılı', text: res.message, icon: 'success', showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Kapat' });
                                    ppuT.draw();
                                } else {
                                    Swal.fire({ title: 'Hata', text: res?.message || 'Bir hata oluştu', icon: 'error' });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("submit", "#pproxyuPoolForm", function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                var url = formData.get('url');
                formData.delete('url');
                $.ajax({
                    type: 'POST', url: url, data: formData, dataType: 'json',
                    contentType: false, processData: false, cache: false,
                    beforeSend: function () { propSubmitButton($("#pproxyu_pool_submit_btn"), 1); },
                    complete: function (data) {
                        propSubmitButton($("#pproxyu_pool_submit_btn"), 0);
                        var res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({ title: 'Başarılı', text: res.message, icon: 'success', showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Kapat' });
                            ppuT.draw();
                            $("#pproxyuPoolModal").modal("hide");
                        } else {
                            Swal.fire({ title: 'Hata', text: res?.message || 'Bir hata oluştu', icon: 'error' });
                        }
                    }
                });
            });

            $(document).on("click", ".pproxyuBulkImportBtn", function () {
                $("#pproxyuBulkImportForm [name='proxies']").val("");
                $("#pproxyuBulkImportModal").modal("show");
            });

            $(document).on("submit", "#pproxyuBulkImportForm", function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST', url: "{{ route('admin.pproxyuPool.bulkImport') }}",
                    data: formData, dataType: 'json',
                    contentType: false, processData: false, cache: false,
                    beforeSend: function () { propSubmitButton($("#pproxyu_bulk_submit_btn"), 1); },
                    complete: function (data) {
                        propSubmitButton($("#pproxyu_bulk_submit_btn"), 0);
                        var res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({ title: 'Başarılı', text: res.message, icon: 'success', showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Kapat' });
                            ppuT.draw();
                            $("#pproxyuBulkImportModal").modal("hide");
                        } else {
                            Swal.fire({ title: 'Hata', text: res?.message || 'Bir hata oluştu', icon: 'error' });
                        }
                    }
                });
            });
        })
    </script>
@endsection
