@extends("admin.template")
@section("title", 'Sistem Ayarları')
@section("css")
<style>
    .animation-blink {
        animation: blink-animation 1.5s ease-in-out infinite;
    }
    @keyframes blink-animation {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
</style>
@endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
    <x-admin.bread-crumb data="Sistem Ayarları"/>
@endsection
@section("master")

    @if(session()->has('form_success'))
        <div class="alert alert-success">{{session()->get('form_success')}}</div>
    @endif
    @if(session()->has('form_error'))
        <div class="alert alert-danger">{{session()->get('form_error')}}</div>
    @endif
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->

                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold gap-5 mb-8">
                <li class="nav-item">
                    <a class="nav-link pb-4 active" data-bs-toggle="tab" href="#system_status_tab">
                        <i class="fa fa-heartbeat me-2 text-danger"></i>Sistem Durumu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_general_tab">Genel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_localtonet_tab">Localtonet</a>
                </li>
            </ul>

            <div class="tab-content" id="systemSettingsTabs">
                {{-- Sistem Durumu Tab --}}
                <div class="tab-pane fade show active" id="system_status_tab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h3 class="fw-bold mb-1">Otomatik Sistemler</h3>
                            <span class="text-muted fs-7" id="statusLastUpdate">Son güncelleme: {{ now()->format('d.m.Y H:i:s') }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light-primary" id="refreshStatusBtn">
                            <i class="fa fa-sync-alt me-1"></i>Yenile
                        </button>
                    </div>

                    {{-- Genel Durum Kartları --}}
                    <div class="row g-5 mb-8">
                        <div class="col-md-3">
                            <div class="card card-flush border border-dashed h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                                    <div class="mb-2" id="schedulerDot">
                                        @if($systemStatus['scheduler_running'])
                                            <span class="bullet bullet-dot bg-success h-15px w-15px animation-blink"></span>
                                        @else
                                            <span class="bullet bullet-dot bg-danger h-15px w-15px"></span>
                                        @endif
                                    </div>
                                    <span class="fs-4 fw-bold" id="schedulerStatusText">
                                        {{ $systemStatus['scheduler_running'] ? 'Çalışıyor' : 'Durdu' }}
                                    </span>
                                    <span class="text-muted fs-7">Zamanlayıcı (Scheduler)</span>
                                    <span class="text-muted fs-8 mt-1" id="schedulerLastRunText">
                                        {{ $systemStatus['scheduler_last_run'] ? 'Son: '.$systemStatus['scheduler_last_run'] : 'Hiç çalışmadı' }}
                                    </span>
                                    <div class="mt-3" id="schedulerBtnArea">
                                        @if($systemStatus['scheduler_running'])
                                            <button type="button" class="btn btn-sm btn-light-danger processBtn" data-type="scheduler" data-action="stop">
                                                <i class="fa fa-stop me-1"></i>Durdur
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-light-success processBtn" data-type="scheduler" data-action="start">
                                                <i class="fa fa-play me-1"></i>Başlat
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-flush border border-dashed h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                                    <div class="mb-2" id="queueDot">
                                        @if($systemStatus['queue_worker_running'] ?? false)
                                            <span class="bullet bullet-dot bg-success h-15px w-15px animation-blink"></span>
                                        @else
                                            <span class="bullet bullet-dot bg-danger h-15px w-15px"></span>
                                        @endif
                                    </div>
                                    <span class="fs-4 fw-bold" id="queueStatusText">
                                        {{ ($systemStatus['queue_worker_running'] ?? false) ? 'Çalışıyor' : 'Durdu' }}
                                    </span>
                                    <span class="text-muted fs-7">Kuyruk İşçisi (Queue)</span>
                                    <span class="fs-6 fw-bold text-primary mt-1" id="queuePendingText">{{ $systemStatus['queue_pending'] }} bekleyen</span>
                                    <div class="mt-3" id="queueBtnArea">
                                        @if($systemStatus['queue_worker_running'] ?? false)
                                            <button type="button" class="btn btn-sm btn-light-danger processBtn" data-type="queue" data-action="stop">
                                                <i class="fa fa-stop me-1"></i>Durdur
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-light-success processBtn" data-type="queue" data-action="start">
                                                <i class="fa fa-play me-1"></i>Başlat
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-flush border border-dashed h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                                    <div class="mb-2">
                                        <i class="fa fa-exclamation-triangle fs-2x text-danger"></i>
                                    </div>
                                    <span class="fs-2x fw-bold text-danger" id="queueFailedText">{{ $systemStatus['queue_failed'] }}</span>
                                    <span class="text-muted fs-7">Başarısız İş</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-flush border border-dashed h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                                    <div class="mb-2">
                                        <i class="fa fa-robot fs-2x text-warning"></i>
                                    </div>
                                    <span class="fs-2x fw-bold text-warning" id="autoReplyActiveText">
                                        @php
                                            $arActive = collect($systemStatus['auto_systems'])->firstWhere('type', 'auto_reply');
                                        @endphp
                                        {{ $arActive ? $arActive['stats']['Aktif Kural'] : 0 }}
                                    </span>
                                    <span class="text-muted fs-7">Aktif Oto-Yanıt Kuralı</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Otomatik Sistemler --}}
                    <div class="row g-5 mb-8" id="autoSystemsContainer">
                        @foreach($systemStatus['auto_systems'] as $sys)
                        <div class="col-md-6 col-xl-4">
                            <div class="card card-flush border border-dashed h-100">
                                <div class="card-header pt-5 pb-3 min-h-auto">
                                    <div class="card-title d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-light-{{ $sys['color'] }}" style="width:42px;height:42px;">
                                            <i class="fa {{ $sys['icon'] }} fs-4 text-{{ $sys['color'] }}"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold fs-6 d-block">{{ $sys['name'] }}</span>
                                            <span class="text-muted fs-8">{{ $sys['description'] }}</span>
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        @if($sys['running'])
                                            <span class="badge badge-light-success fs-8 px-3 py-2">
                                                <span class="bullet bullet-dot bg-success me-1 animation-blink"></span>Aktif
                                            </span>
                                        @else
                                            <span class="badge badge-light-danger fs-8 px-3 py-2">
                                                <span class="bullet bullet-dot bg-danger me-1"></span>Pasif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($sys['stats']))
                                <div class="card-body pt-2 pb-4">
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach($sys['stats'] as $label => $value)
                                        <div class="border border-gray-200 rounded px-3 py-2 text-center" style="min-width:90px;">
                                            <span class="fw-bold fs-6 d-block">{{ $value }}</span>
                                            <span class="text-muted fs-8">{{ $label }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Zamanlanmış Komutlar Tablosu --}}
                    <div class="card card-flush border border-dashed">
                        <div class="card-header pt-5 pb-3 min-h-auto">
                            <div class="card-title">
                                <i class="fa fa-clock text-muted me-2"></i>
                                <span class="fw-bold fs-5">Zamanlanmış Komutlar</span>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-row-bordered table-row-gray-200 align-middle fs-7 gy-3">
                                    <thead>
                                        <tr class="text-muted fw-semibold">
                                            <th>Komut</th>
                                            <th>Zamanlama</th>
                                            <th>Açıklama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($systemStatus['scheduled_commands'] as $cmd)
                                        <tr>
                                            <td><code class="fs-7">{{ $cmd['command'] }}</code></td>
                                            <td><span class="badge badge-light-primary fs-8">{{ $cmd['schedule'] }}</span></td>
                                            <td class="text-muted">{{ $cmd['description'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if(!$systemStatus['scheduler_running'])
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-5 mt-6">
                        <i class="fa fa-exclamation-triangle fs-2 text-warning me-4"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Zamanlayıcı çalışmıyor!</h5>
                            <p class="text-muted fs-7 mb-2">Otomatik teslimat, sipariş yenileme ve fatura hatırlatmaları çalışmıyor olabilir. Sunucunuzda aşağıdaki cron job'un tanımlı olduğundan emin olun:</p>
                            <code class="d-block bg-dark text-white rounded p-3 fs-8">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                        </div>
                    </div>
                    @endif
                </div>

                <form action="{{ route('admin.updateSettings') }}" method="POST">
                    @csrf
                    <div class="tab-pane fade" id="system_settings_general_tab" role="tabpanel">
                        <div class="w-50 mx-auto row">
                            <div class="fv-row mb-7">
                                <label class="required form-label mb-3">ACL List</label>
                                <textarea name="urls" id="" cols="30" rows="5"
                                          class="form-control form-control-solid">{{implode("\n",$urls)}}</textarea>
                            </div>
                            <hr>
                            <div class="w-100 d-none">
                                <h3 class="my-3">Test Ürünü Ayarları</h3>
                                <div class="row">
                                    <div class="col-12 mb-7">
                                        <label class="form-label fw-semibold required">Durum</label>
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" {{config('test_product.status') == 1 ? 'checked' : ''}} name="test_product[status]" value="1" id="flexSwitchDefault"/>
                                            <label class="form-check-label" for="flexSwitchDefault">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-7">
                                        <label class="form-label fw-semibold required">{{__("product")}}</label>
                                        <x-admin.form-elements.product-select name="test_product[product_id]"
                                                                              customClass="productSelection"
                                                                              :withPassives="true"
                                                                              :selectedOption="$test_product ? ['label' => $test_product->name,'value' => $test_product->id] : ''"
                                                                              />
                                    </div>
                                    <div class="col-12 mb-7">
                                        <label class="form-label fw-semibold required">{{__("price")}}</label>
                                        <x-admin.form-elements.select name="test_product[price_id]"
                                                                      customClass="priceSelection"
                                                                      :ajaxSelect2="true"
                                                                      :hideSearch="true"
                                                                      :selectedOption="$test_product_price ? ['label' => $test_product_price->name,'value' => $test_product_price->id] : ''"
                                                                      />
                                    </div>
                                    <div class="col-12 d-none">
                                        <label class="form-label fw-semibold">{{__("additional_services")}}</label>
                                        <table id="additionalTable" class="table table-bordered">
                                            <tbody>
                                            <tr>
                                                <td colspan='2' class='text-center fw-bold text-gray-600'>Ek Hizmet Yok</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="system_settings_localtonet_tab" role="tabpanel">
                        <div class="w-50 mx-auto row">
                            <div class="col-12 mb-7">
                                <label class="form-label fw-semibold">HTTPS SSL doğrulama (cURL verify)</label>
                                <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                    <input type="hidden" name="localtonet_http_verify" value="0" id="localtonetHttpVerifyHidden"/>
                                    <input class="form-check-input" type="checkbox" name="localtonet_http_verify" value="1"
                                           id="localtonetHttpVerifySwitch" {{ $localtonetHttpVerify ? 'checked' : '' }}/>
                                    <label class="form-check-label" for="localtonetHttpVerifySwitch">
                                        Açık (önerilen — üretim)
                                    </label>
                                </div>
                                <div class="form-text text-gray-600 mt-3">
                                    Kapalıyken Localtonet API ve IP değiştirme gibi HTTPS isteklerinde sertifika doğrulanmaz (Windows geliştirme ortamında cURL 60 hatasını önlemek için kullanılır).
                                    Canlı sunucuda güvenlik için açık tutun veya sunucu CA sertifikalarını yapılandırın.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
                        </div>
                    </div>
                </form>
            </div>
            <!--end::Tab content-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Modals-->
    <div class="modal fade" id="primaryGroupModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>{{__("create")}}</h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body py-lg-10 px-lg-15">
                    <form id="tokenPoolForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required form-label mb-3">{{__("title")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name" class="form-control form-control-lg " required>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row">
                            <!--begin::Label-->
                            <label class="required form-label mb-3">Auth Token Seçimi</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <!--begin::Input-->
                            <x-admin.form-elements.auth-token-select
                                name="auth_tokens[]"
                                customAttr="multiple"
                                customClass="mw-100"/>
                            <!--end::Input-->
                            <!--end::Input-->
                        </div>
                        <!--begin::Actions-->
                        <div class="d-flex flex-center flex-row-fluid pt-12">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="user_group_submit_btn">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{__("save")}}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modals-->
@endsection
@section("js")
    <script>
        $(document).ready(function(){
            var $ltHidden = $("#localtonetHttpVerifyHidden");
            var $ltSwitch = $("#localtonetHttpVerifySwitch");
            function syncLocaltonetVerifyHidden() {
                if (!$ltHidden.length || !$ltSwitch.length) return;
                $ltHidden.prop("disabled", $ltSwitch.is(":checked"));
            }
            $ltSwitch.on("change", syncLocaltonetVerifyHidden);
            syncLocaltonetVerifyHidden();

            $(document).on("select2:select", '.productSelection', function (e) {
                let additionalServiceArea = $("#additionalTable"),
                    extraParams = e.params.data.extraParams,
                    attributes,
                    body = additionalServiceArea.find("tbody");

                $(".priceSelection").val("").trigger("change")

                if (extraParams.attrs.length > 0) {
                    attributes = extraParams.attrs.filter((item) => {
                        return item.service_type === "protocol_select";
                    })

                    body.html("")
                    attributes.map((item) => {
                        body.append("<tr>" +
                            "<td>" + item.label + "</td>" +
                            "<td>" + drawFormElement(item) + "</td>" +
                            "</tr>")
                    })
                } else {
                    body.html("")
                    body.append("<tr>" +
                        "<td colspan='2' class='text-center fw-bold text-gray-600'>Ek Hizmet Yok</td>" +
                        "</tr>")
                }
            })

            $(".priceSelection").select2({
                placeholder: "{{__(":name_selection", ["name" => __("district")])}}",
                allowClear: true,
                minimumResultsForSearch: Infinity,
                tags: false,
                language: {
                    searching: function () {
                        return "{{__("searching")}}...";
                    },
                    "noResults": function () {
                        return "{{__("result_not_found")}}";
                    },
                    "errorLoading": function () {
                        return 'Ürün seçiniz';
                    }
                },
                ajax: {
                    url: "{{route("admin.prices.searchByProduct")}}",
                    type: "GET",
                    dataType: 'json',
                    quietMillis: 50,
                    data: function (term) {
                        return {
                            _token: "{{csrf_token()}}",
                            term: term,
                            product_id: $(".productSelection").val()
                        };
                    },
                    processResults: function (data) {
                        var res = data.items.map(function (item) {
                            return {
                                id: item.id,
                                text: item.name
                            };
                        });
                        return {
                            results: res
                        };
                    }
                }
            });

            function refreshStatus(){
                var btn = $('#refreshStatusBtn');
                btn.prop('disabled', true).find('i').addClass('fa-spin');
                $.ajax({
                    url: '{{ route("admin.systemStatusAjax") }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res){
                        if(res.success){
                            var d = res.data;
                            $('#statusLastUpdate').text('Son güncelleme: ' + res.timestamp);
                            $('#schedulerStatusText').text(d.scheduler_running ? 'Çalışıyor' : 'Durdu');
                            $('#schedulerLastRunText').text(d.scheduler_last_run ? 'Son: ' + d.scheduler_last_run : 'Hiç çalışmadı');
                            $('#schedulerDot').html(d.scheduler_running
                                ? '<span class="bullet bullet-dot bg-success h-15px w-15px animation-blink"></span>'
                                : '<span class="bullet bullet-dot bg-danger h-15px w-15px"></span>');
                            $('#schedulerBtnArea').html(d.scheduler_running
                                ? '<button type="button" class="btn btn-sm btn-light-danger processBtn" data-type="scheduler" data-action="stop"><i class="fa fa-stop me-1"></i>Durdur</button>'
                                : '<button type="button" class="btn btn-sm btn-light-success processBtn" data-type="scheduler" data-action="start"><i class="fa fa-play me-1"></i>Başlat</button>');

                            var qr = d.queue_worker_running || false;
                            $('#queueStatusText').text(qr ? 'Çalışıyor' : 'Durdu');
                            $('#queueDot').html(qr
                                ? '<span class="bullet bullet-dot bg-success h-15px w-15px animation-blink"></span>'
                                : '<span class="bullet bullet-dot bg-danger h-15px w-15px"></span>');
                            $('#queuePendingText').text(d.queue_pending + ' bekleyen');
                            $('#queueBtnArea').html(qr
                                ? '<button type="button" class="btn btn-sm btn-light-danger processBtn" data-type="queue" data-action="stop"><i class="fa fa-stop me-1"></i>Durdur</button>'
                                : '<button type="button" class="btn btn-sm btn-light-success processBtn" data-type="queue" data-action="start"><i class="fa fa-play me-1"></i>Başlat</button>');

                            $('#queueFailedText').text(d.queue_failed);

                            var container = $('#autoSystemsContainer');
                            container.empty();
                            d.auto_systems.forEach(function(sys){
                                var statsHtml = '';
                                if(sys.stats && Object.keys(sys.stats).length > 0){
                                    statsHtml = '<div class="card-body pt-2 pb-4"><div class="d-flex flex-wrap gap-3">';
                                    Object.keys(sys.stats).forEach(function(label){
                                        statsHtml += '<div class="border border-gray-200 rounded px-3 py-2 text-center" style="min-width:90px;">'
                                            + '<span class="fw-bold fs-6 d-block">' + sys.stats[label] + '</span>'
                                            + '<span class="text-muted fs-8">' + label + '</span></div>';
                                    });
                                    statsHtml += '</div></div>';
                                }
                                var statusBadge = sys.running
                                    ? '<span class="badge badge-light-success fs-8 px-3 py-2"><span class="bullet bullet-dot bg-success me-1 animation-blink"></span>Aktif</span>'
                                    : '<span class="badge badge-light-danger fs-8 px-3 py-2"><span class="bullet bullet-dot bg-danger me-1"></span>Pasif</span>';

                                container.append(
                                    '<div class="col-md-6 col-xl-4">'
                                    + '<div class="card card-flush border border-dashed h-100">'
                                    + '<div class="card-header pt-5 pb-3 min-h-auto">'
                                    + '<div class="card-title d-flex align-items-center gap-3">'
                                    + '<div class="d-flex align-items-center justify-content-center rounded-circle bg-light-' + sys.color + '" style="width:42px;height:42px;">'
                                    + '<i class="fa ' + sys.icon + ' fs-4 text-' + sys.color + '"></i></div>'
                                    + '<div><span class="fw-bold fs-6 d-block">' + sys.name + '</span>'
                                    + '<span class="text-muted fs-8">' + sys.description + '</span></div></div>'
                                    + '<div class="card-toolbar">' + statusBadge + '</div></div>'
                                    + statsHtml
                                    + '</div></div>'
                                );
                            });

                            if(d.auto_systems.length > 0){
                                var arSys = d.auto_systems.find(function(s){ return s.type === 'auto_reply'; });
                                if(arSys) $('#autoReplyActiveText').text(arSys.stats['Aktif Kural'] || 0);
                            }
                        }
                    },
                    complete: function(){
                        btn.prop('disabled', false).find('i').removeClass('fa-spin');
                    }
                });
            }

            $('#refreshStatusBtn').on('click', refreshStatus);

            $(document).on('click', '.processBtn', function(){
                var btn = $(this);
                var type = btn.data('type');
                var action = btn.data('action');
                var url = action === 'start'
                    ? '{{ route("admin.systemProcessStart") }}'
                    : '{{ route("admin.systemProcessStop") }}';
                var label = type === 'scheduler' ? 'Zamanlayıcı' : 'Kuyruk İşçisi';

                btn.prop('disabled', true);
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', type: type },
                    success: function(res){
                        if(res.success){
                            toastr.success(res.message);
                            setTimeout(refreshStatus, 2000);
                        } else {
                            toastr.error(res.message || 'Hata oluştu');
                        }
                    },
                    error: function(){
                        toastr.error('İşlem sırasında hata oluştu');
                    },
                    complete: function(){
                        btn.prop('disabled', false);
                    }
                });
            });
        })
    </script>
@endsection
