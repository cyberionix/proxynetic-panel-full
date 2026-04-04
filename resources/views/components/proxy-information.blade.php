@props([
    'order',
    'context' => 'portal',
])
@php
    $isAdminContext = ($context === 'admin');
    $ltPrefix = $isAdminContext ? 'admin.orders.localtonet.' : 'portal.orders.localtonet.';
@endphp
@if($order->isThreeProxyDelivery())
    @if($order->status != "ACTIVE" && !$isAdminContext)
        <div class="alert alert-primary d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            </div>
            <div class="d-flex align-items-center">
                <h6 class="mb-0 text-primary">Hizmet durumunuz aktif olmadığı için proxy bilgileri görüntülenemez.
                    (Hizmet Durumu: {{__(mb_strtolower($order->status))}})</h6>
            </div>
        </div>
    @else
        @php
            $tpList = $order->getThreeProxyDisplayList();
            $tpInfo = $order->product_info ?? [];
            $hasSocks = collect($tpList)->pluck('socks_port')->filter()->isNotEmpty();
            $tpHttpLines = [];
            $tpSocksLines = [];
            foreach ($tpList as $tp) {
                $tpHttpLines[] = ($tp['ip'] ?? '') . ':' . ($tp['http_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '');
                if (!empty($tp['socks_port'])) {
                    $tpSocksLines[] = ($tp['ip'] ?? '') . ':' . ($tp['socks_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '');
                }
            }
            $tpHttpText = implode("\n", $tpHttpLines);
            $tpSocksText = implode("\n", $tpSocksLines);
        @endphp
        <div class="np-threeproxy-root">
            @if(!$isAdminContext)
            @php
                $tpProduct = $order->product;
                $tpExtraDurAttr = $tpProduct ? $tpProduct->findAttrsByServiceType('tp_extra_duration') : null;
                $tpChangeIpsAttr_p = $tpProduct ? collect($tpProduct->attrs ?? [])->where('service_type', 'tp_change_ips')->first() : null;
                $tpSubnetIpsAttr_p = $tpProduct ? collect($tpProduct->attrs ?? [])->where('service_type', 'tp_subnet_ips')->first() : null;
                $tpClassIpsAttr_p = $tpProduct ? collect($tpProduct->attrs ?? [])->where('service_type', 'tp_class_ips')->first() : null;
                $hasTpAdditionalServices = $tpExtraDurAttr || $tpChangeIpsAttr_p || $tpSubnetIpsAttr_p || $tpClassIpsAttr_p;
            @endphp

            <div class="card border border-gray-300 mb-8">
                <div class="card-header border-0 min-h-50px h-50px bg-light-secondary px-6">
                    <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-bold">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#np_tp_tab_account">
                                <i class="fa fa-key me-2 text-primary"></i>Hesap Bilgileri
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#np_tp_tab_extras">
                                <i class="fa fa-puzzle-piece me-2 text-warning"></i>Ek Hizmetler
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="np_tp_tab_account">
                            <form id="npTpCredForm">
                                <div class="row g-4">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Kullanıcı Adı</label>
                                        <input type="text" class="form-control" id="np_tp_portal_username"
                                               value="{{ $tpInfo['three_proxy_username'] ?? '' }}" minlength="3" maxlength="32">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Şifre</label>
                                        <input type="text" class="form-control" id="np_tp_portal_password"
                                               value="{{ $tpInfo['three_proxy_password'] ?? '' }}" minlength="4" maxlength="64">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-sm w-100" id="np_tp_cred_submit_btn">
                                            <span class="indicator-label">Kaydet</span>
                                            <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="np_tp_tab_extras">
                            @if($hasTpAdditionalServices)
                            @if($tpExtraDurAttr && count($tpExtraDurAttr['options'] ?? []) > 0)
                            <div class="mb-7">
                                <h5 class="fw-bold mb-4"><i class="fa fa-clock me-2 text-primary"></i>Ek Süre</h5>
                                <form id="npTpExtraDurForm">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <select class="form-select" name="tp_extra_duration" id="np_tp_extra_dur_select">
                                                @foreach($tpExtraDurAttr['options'] as $opt)
                                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }} — {{ showBalance($opt['price'], true) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-primary btn-sm w-100" id="np_tp_extra_dur_btn">
                                                <span class="indicator-label">Satın Al</span>
                                                <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @endif

                            @if($tpChangeIpsAttr_p)
                            <div class="mb-5">
                                <div class="np-tp-extra-action-row d-flex justify-content-between align-items-center p-4 border border-gray-300 rounded cursor-pointer" role="button" tabindex="0">
                                    <div>
                                        <h6 class="mb-1 fw-bold">IP'leri Değiştir</h6>
                                        <span class="text-gray-600 fs-7">Havuzdan rastgele yeni IP'ler atanır.</span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary mb-1">{{ showBalance($tpChangeIpsAttr_p['price'], true) }}</div>
                                        <button type="button" class="btn btn-sm btn-warning np-tp-action-btn" data-action="tp_change_ips" tabindex="-1">
                                            <span class="indicator-label">Satın Al</span>
                                            <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($tpSubnetIpsAttr_p)
                            <div class="mb-5">
                                <div class="np-tp-extra-action-row d-flex justify-content-between align-items-center p-4 border border-gray-300 rounded cursor-pointer" role="button" tabindex="0">
                                    <div>
                                        <h6 class="mb-1 fw-bold">Her Subnetten Farklı IP</h6>
                                        <span class="text-gray-600 fs-7">Her /24 subnetten farklı IP atanır.</span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary mb-1">{{ showBalance($tpSubnetIpsAttr_p['price'], true) }}</div>
                                        <button type="button" class="btn btn-sm btn-info np-tp-action-btn" data-action="tp_subnet_ips" tabindex="-1">
                                            <span class="indicator-label">Satın Al</span>
                                            <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($tpClassIpsAttr_p)
                            <div class="mb-0">
                                <div class="np-tp-extra-action-row d-flex justify-content-between align-items-center p-4 border border-gray-300 rounded cursor-pointer" role="button" tabindex="0">
                                    <div>
                                        <h6 class="mb-1 fw-bold">Her Class IP'den Farklı IP</h6>
                                        <span class="text-gray-600 fs-7">Her /16 class bloğundan farklı IP atanır.</span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary mb-1">{{ showBalance($tpClassIpsAttr_p['price'], true) }}</div>
                                        <button type="button" class="btn btn-sm btn-success np-tp-action-btn" data-action="tp_class_ips" tabindex="-1">
                                            <span class="indicator-label">Satın Al</span>
                                            <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @php $tpPurchasedServices = $order->allAdditionalServices(); @endphp
                            @if(count($tpPurchasedServices) > 0)
                            <div class="mt-7">
                                <h6 class="fw-bold mb-3">Satın Alınan Ek Hizmetler</h6>
                                <x-proxy-additional-services :order="$order" />
                            </div>
                            @endif
                            @else
                            <div class="text-center py-10">
                                <i class="fa fa-puzzle-piece fs-2x text-gray-400 mb-4 d-block"></i>
                                <p class="text-gray-600 fw-semibold fs-6 mb-0">Bu ürün için henüz ek hizmet tanımlanmamış.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card border border-primary border-opacity-25 bg-primary bg-opacity-10 mb-8 shadow-none">
                <div class="card-body p-6 p-lg-8">
                    @if($hasSocks)
                    <div class="d-flex gap-2 mb-4">
                        <button type="button" class="btn btn-sm btn-primary np-tp-proto-btn active" data-proto="http">HTTP</button>
                        <button type="button" class="btn btn-sm btn-light-success np-tp-proto-btn" data-proto="socks5">SOCKS5</button>
                    </div>
                    @endif
                    <div class="mb-5">
                        <label class="form-label fw-semibold text-gray-800 mb-2">Proxy satırı (kopyala)</label>
                        <textarea id="np_tp_bulk" class="form-control form-control-solid font-monospace text-gray-800 w-100"
                                  style="min-height: 6.5rem;" rows="{{ min(count($tpHttpLines) + 1, 8) }}" readonly
                                  placeholder="IP:Port:Kullanıcı:Şifre">{{ $tpHttpText }}</textarea>
                    </div>
                    <div class="row g-3 g-lg-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-light btn-active-light-primary btn-sm w-100 py-2 border border-primary border-opacity-25"
                                    onclick="var t=document.getElementById('np_tp_bulk');t.select();document.execCommand('copy');toastr.success('Kopyalandı');">Tümünü Kopyala</button>
                        </div>
                    </div>
                </div>
            </div>

            @if(count($tpList) <= 3)
                @foreach($tpList as $tpIdx => $tp)
                    @php
                        $tpHttpLine = ($tp['ip'] ?? '') . ':' . ($tp['http_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '');
                        $tpSocksLine = !empty($tp['socks_port']) ? ($tp['ip'] ?? '') . ':' . ($tp['socks_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '') : '';
                    @endphp
                    <div class="card shadow-sm border border-gray-300 mb-8 np-tp-card"
                         data-http-line="{{ $tpHttpLine }}" data-socks-line="{{ $tpSocksLine }}"
                         data-http-port="{{ $tp['http_port'] ?? '' }}" data-socks-port="{{ $tp['socks_port'] ?? '' }}">
                        <div class="card-body p-6 p-lg-8">
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="badge badge-light-dark align-self-center me-1">#{{ $tpIdx + 1 }}</span>
                                <span class="badge badge-light-primary np-tp-proto-badge-http">HTTP</span>
                                @if(!empty($tp['socks_port']))
                                    <span class="badge badge-light-success np-tp-proto-badge-socks d-none">SOCKS5</span>
                                @endif
                            </div>
                            <div class="table-responsive rounded border border-gray-300 bg-body p-4">
                                <table class="table table-row-bordered align-middle fs-6 gy-3 mb-0">
                                    <tbody>
                                    <tr>
                                        <td class="text-gray-600 fw-semibold w-125px">IP</td>
                                        <td class="font-monospace text-gray-900 fw-semibold">{{ $tp['ip'] ?? '—' }}</td>
                                        <td class="text-end w-50px">
                                            @if(!empty($tp['ip']))
                                                <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2"
                                                        onclick="navigator.clipboard.writeText('{{ $tp['ip'] }}');toastr.success('Kopyalandı');">Kopyala</button>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="np-tp-port-row">
                                        <td class="text-gray-600 fw-semibold np-tp-port-label">Port</td>
                                        <td class="font-monospace text-gray-900 fw-semibold np-tp-port-value">{{ $tp['http_port'] ?? '—' }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2 np-tp-port-copy"
                                                    onclick="navigator.clipboard.writeText(this.dataset.val);toastr.success('Kopyalandı');"
                                                    data-val="{{ $tp['http_port'] ?? '' }}">Kopyala</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-semibold">Kullanıcı Adı</td>
                                        <td class="font-monospace text-gray-900 fw-semibold">{{ $tp['username'] ?? '—' }}</td>
                                        <td class="text-end">
                                            @if(!empty($tp['username']))
                                                <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2"
                                                        onclick="navigator.clipboard.writeText('{{ $tp['username'] }}');toastr.success('Kopyalandı');">Kopyala</button>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-semibold">Şifre</td>
                                        <td class="font-monospace text-gray-900 fw-semibold">{{ $tp['password'] ?? '—' }}</td>
                                        <td class="text-end">
                                            @if(!empty($tp['password']))
                                                <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2"
                                                        onclick="navigator.clipboard.writeText('{{ $tp['password'] }}');toastr.success('Kopyalandı');">Kopyala</button>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-gray-600 fw-semibold">Satır</td>
                                        <td class="font-monospace text-gray-900 fw-semibold text-break np-tp-line-value">{{ $tpHttpLine }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2 np-tp-line-copy"
                                                    onclick="navigator.clipboard.writeText(this.dataset.val);toastr.success('Kopyalandı');"
                                                    data-val="{{ $tpHttpLine }}">Kopyala</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @elseif(count($tpList) > 0)
                <div class="card shadow-sm border border-gray-300 mb-8">
                    <div class="card-header min-h-50px bg-light-secondary py-3">
                        <h3 class="card-title fw-bold fs-6 mb-0">Tüm Proxyler ({{ count($tpList) }})</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-hover align-middle fs-7 gy-3 mb-0" id="np_tp_table">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 w-50px">#</th>
                                        <th>IP</th>
                                        <th>HTTP Port</th>
                                        @if($hasSocks)
                                            <th>SOCKS5 Port</th>
                                        @endif
                                        <th>Kullanıcı</th>
                                        <th>Şifre</th>
                                        <th class="text-end pe-4">Kopyala</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($tpList as $tpIdx => $tp)
                                    @php
                                        $tpHttpLine = ($tp['ip'] ?? '') . ':' . ($tp['http_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '');
                                        $tpSocksLine = !empty($tp['socks_port']) ? ($tp['ip'] ?? '') . ':' . ($tp['socks_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '') : $tpHttpLine;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-gray-600">{{ $tpIdx + 1 }}</td>
                                        <td class="font-monospace text-gray-900">{{ $tp['ip'] ?? '—' }}</td>
                                        <td class="font-monospace text-gray-900">{{ $tp['http_port'] ?? '—' }}</td>
                                        @if($hasSocks)
                                            <td class="font-monospace text-gray-900">{{ $tp['socks_port'] ?? '—' }}</td>
                                        @endif
                                        <td class="font-monospace text-gray-900">{{ $tp['username'] ?? '—' }}</td>
                                        <td class="font-monospace text-gray-900">{{ $tp['password'] ?? '—' }}</td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary np-tp-row-copy"
                                                    data-http="{{ $tpHttpLine }}"
                                                    data-socks="{{ $tpSocksLine }}"
                                                    title="Kopyala"><i class="fa fa-copy"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-light-warning mb-8">Proxy bilgisi henüz oluşmadı veya teslim edilmemiş.</div>
            @endif

            @if(!empty($tpInfo['three_proxy_expire']))
                <div class="card border border-gray-300 mb-8">
                    <div class="card-body py-5 px-6">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-calendar-alt text-primary fs-3 me-3"></i>
                            <div>
                                <span class="text-gray-600 fw-semibold fs-7 d-block">Bitiş Tarihi</span>
                                <span class="text-gray-900 fw-bold fs-6">{{ $tpInfo['three_proxy_expire'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <script id="np_tp_data" type="application/json">{!! json_encode(['http' => $tpHttpText, 'socks' => $tpSocksText]) !!}</script>
        <script>
        (function(){
            var d = JSON.parse(document.getElementById('np_tp_data').textContent);
            var bulkEl = document.getElementById('np_tp_bulk');

            document.querySelectorAll('.np-tp-proto-btn').forEach(function(btn){
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    var proto = this.getAttribute('data-proto');
                    document.querySelectorAll('.np-tp-proto-btn').forEach(function(b){
                        b.classList.remove('active','btn-primary','btn-success');
                        b.classList.add('btn-light-primary');
                    });
                    if (proto === 'socks5') {
                        this.classList.remove('btn-light-primary');
                        this.classList.add('active','btn-success');
                        bulkEl.value = d.socks;
                    } else {
                        this.classList.remove('btn-light-primary');
                        this.classList.add('active','btn-primary');
                        bulkEl.value = d.http;
                    }
                    document.querySelectorAll('.np-tp-card').forEach(function(card){
                        var port = proto === 'socks5' ? card.getAttribute('data-socks-port') : card.getAttribute('data-http-port');
                        var line = proto === 'socks5' ? card.getAttribute('data-socks-line') : card.getAttribute('data-http-line');
                        var pv = card.querySelector('.np-tp-port-value');
                        if(pv) pv.textContent = port || '—';
                        var pc = card.querySelector('.np-tp-port-copy');
                        if(pc) pc.setAttribute('data-val', port || '');
                        var lv = card.querySelector('.np-tp-line-value');
                        if(lv) lv.textContent = line || '';
                        var lc = card.querySelector('.np-tp-line-copy');
                        if(lc) lc.setAttribute('data-val', line || '');
                        var bh = card.querySelector('.np-tp-proto-badge-http');
                        var bs = card.querySelector('.np-tp-proto-badge-socks');
                        if (proto === 'socks5') {
                            if(bh) bh.classList.add('d-none');
                            if(bs) bs.classList.remove('d-none');
                        } else {
                            if(bh) bh.classList.remove('d-none');
                            if(bs) bs.classList.add('d-none');
                        }
                    });
                    document.querySelectorAll('.np-tp-row-copy').forEach(function(cb){
                        cb._npCopyLine = proto === 'socks5' ? cb.getAttribute('data-socks') : cb.getAttribute('data-http');
                    });
                });
            });

            document.addEventListener('click', function(e){
                var cb = e.target.closest('.np-tp-row-copy');
                if (cb) {
                    var txt = cb._npCopyLine || cb.getAttribute('data-http') || '';
                    navigator.clipboard.writeText(txt);
                    toastr.success('Kopyalandı');
                }
            });

            @if(!$isAdminContext)
            var credForm = document.getElementById('npTpCredForm');
            if (credForm) {
                credForm.addEventListener('submit', function(e){
                    e.preventDefault();
                    var u = document.getElementById('np_tp_portal_username').value,
                        p = document.getElementById('np_tp_portal_password').value,
                        btn = $('#np_tp_cred_submit_btn');
                    if (!u || u.length < 3) { toastr.error('Kullanıcı adı en az 3 karakter olmalıdır.'); return; }
                    if (!p || p.length < 4) { toastr.error('Şifre en az 4 karakter olmalıdır.'); return; }
                    $.ajax({
                        type: 'POST',
                        url: '{{ route("portal.orders.localtonet.threeProxyChangeCredentials", ["order" => $order->id]) }}',
                        data: { _token: '{{ csrf_token() }}', username: u, password: p },
                        dataType: 'json',
                        beforeSend: function(){ propSubmitButton(btn, 1); },
                        complete: function(data){
                            propSubmitButton(btn, 0);
                            var res = data.responseJSON;
                            if (res && res.success === true) {
                                toastr.success(res.message || 'Güncellendi.');
                                setTimeout(function(){ window.location.reload(); }, 1500);
                            } else {
                                toastr.error(res?.message || 'Hata oluştu.');
                            }
                        }
                    });
                });
            }

            var extraDurForm = document.getElementById('npTpExtraDurForm');
            if (extraDurForm) {
                extraDurForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var sel = document.getElementById('np_tp_extra_dur_select');
                    if (!sel || !sel.value) { toastr.error('Lütfen bir süre seçin.'); return; }
                    if (!confirm('Bu ek süreyi satın almak istediğinize emin misiniz? Fatura oluşturulacaktır.')) return;
                    var btn = $('#np_tp_extra_dur_btn');
                    $.ajax({
                        type: 'POST',
                        url: '{{ route("portal.orders.tpExtraDurationPost", ["order" => $order->id]) }}',
                        data: { _token: '{{ csrf_token() }}', tp_extra_duration: sel.value },
                        dataType: 'json',
                        beforeSend: function() { propSubmitButton(btn, 1); },
                        complete: function(data) {
                            propSubmitButton(btn, 0);
                            var res = data.responseJSON;
                            if (res && res.success === true) {
                                toastr.success(res.message || 'Fatura oluşturuldu.');
                                if (res.redirectUrl) setTimeout(function(){ window.location.href = res.redirectUrl; }, 1500);
                            } else {
                                toastr.error(res?.message || 'Hata oluştu.');
                            }
                        }
                    });
                });
            }

            var tpRootEl = document.querySelector('.np-threeproxy-root');
            if (tpRootEl) {
                var tpServiceLabels = { tp_change_ips: "IP'leri Değiştir", tp_subnet_ips: "Her Subnetten Farklı IP", tp_class_ips: "Her Class IP'den Farklı IP" };
                function npTpFindActionButton(target) {
                    var btn = target && target.closest ? target.closest('.np-tp-action-btn') : null;
                    if (btn && tpRootEl.contains(btn)) return btn;
                    var row = target && target.closest ? target.closest('.np-tp-extra-action-row') : null;
                    if (row && tpRootEl.contains(row)) return row.querySelector('.np-tp-action-btn');
                    return null;
                }
                function npTpRunServicePurchase(btn) {
                    if (!btn) return;
                    var actionType = btn.getAttribute('data-action');
                    if (!actionType) return;
                    if (!confirm('"' + (tpServiceLabels[actionType] || actionType) + '" hizmetini satın almak istediğinize emin misiniz? Fatura oluşturulacaktır.')) return;
                    var jBtn = $(btn);
                    $.ajax({
                        type: 'POST',
                        url: '{{ route("portal.orders.tpServiceActionPost", ["order" => $order->id]) }}',
                        data: { _token: '{{ csrf_token() }}', action_type: actionType },
                        dataType: 'json',
                        beforeSend: function() { propSubmitButton(jBtn, 1); },
                        complete: function(data) {
                            propSubmitButton(jBtn, 0);
                            var res = data.responseJSON;
                            if (res && res.success === true) {
                                toastr.success(res.message || 'Fatura oluşturuldu.');
                                if (res.redirectUrl) setTimeout(function(){ window.location.href = res.redirectUrl; }, 1500);
                            } else {
                                toastr.error((res && res.message) ? res.message : 'Hata oluştu.');
                            }
                        }
                    });
                }
                tpRootEl.addEventListener('click', function(e) {
                    var btn = npTpFindActionButton(e.target);
                    if (!btn) return;
                    e.preventDefault();
                    npTpRunServicePurchase(btn);
                });
                tpRootEl.addEventListener('keydown', function(e) {
                    if (e.key !== 'Enter' && e.key !== ' ') return;
                    var t = e.target;
                    if (!t || !t.classList || !t.classList.contains('np-tp-extra-action-row')) return;
                    if (!tpRootEl.contains(t)) return;
                    e.preventDefault();
                    npTpRunServicePurchase(t.querySelector('.np-tp-action-btn'));
                });
            }
            @endif
        })();
        </script>
    @endif
@elseif($order->isCanDeliveryType("STACK"))
    @if($order->status != "ACTIVE" && !$isAdminContext)
        <div class="alert alert-primary d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <!--begin::Icon-->
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span
                        class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <!--end::Icon-->
            </div>
            <!--begin::Wrapper-->
            <div class="d-flex align-items-center">
                <!--begin::Title-->
                <h6 class="mb-0 text-primary">Hizmet durumunuz aktif olmadığı için proxy bilgileri görüntülenemez.
                    (Hizmet Durumu: {{__(mb_strtolower($order->status))}})</h6>
                <!--end::Title-->
            </div>
            <!--end::Wrapper-->
        </div>
    @else
        <div class="text-gray-700 fw-semibold fs-6 mh-300px scroll-y">
            @foreach($order->proxy_list as $proxy)
                <div class="mb-1">
                    {{$proxy}}
                    <span data-text="{{$proxy}}"
                          class="copy-text cursor-pointer text-hover-primary ms-1"><i
                            class="fa fa-copy fw-bold"></i></span>
                </div>
            @endforeach
        </div>
    @endif
@elseif($order->isLocaltonetLikeDelivery())
    @php
        $order->loadMissing('product');

        $ltRoute = function (string $name, array $query = []) use ($order, $ltPrefix) {
            return route($ltPrefix . $name, array_merge(['order' => $order->id], $query));
        };
        $proxyLocaltonet = $order->getProxyLocaltonet();
        if ($proxyLocaltonet){
            $proxyLocaltonet = $proxyLocaltonet["result"];
        }

        $ltPortalOk = $order->isLocaltonetPortalOperationsAllowed();
        $hideProxy = !$isAdminContext && (! $ltPortalOk);
    @endphp
    @if($order->isLocaltonetLikeDelivery() && ! $ltPortalOk)
        <div class="alert alert-{{ $isAdminContext ? 'warning' : 'primary' }} d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span
                        class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            </div>
            <div class="d-flex align-items-center">
                <h6 class="mb-0 text-primary">
                    @if($order->status != "ACTIVE")
                        @if($isAdminContext)
                            Sipariş durumu aktif değil ({{__(mb_strtolower($order->status))}}); yönetim panelinden teknik işlemler yine de yapılabilir.
                        @else
                            Hizmet durumunuz aktif olmadığı için proxy bilgilerinde düzenleme
                            yapılamaz. (Hizmet Durumu: {{__(mb_strtolower($order->status))}})
                        @endif
                    @else
                        @if($isAdminContext)
                            Müşteri tarafında henüz teslim onayı yok; aşağıdan Localtonet proxy bilgilerini görüntüleyip yönetebilirsiniz.
                        @else
                            Henüz proxy teslim edilmediği için düzenleme yapılamaz.
                        @endif
                    @endif
                </h6>
            </div>
        </div>
    @endif
    @if($order->isCanDeliveryType('LOCALTONETV4'))
        @php
            $order->loadMissing(['activeDetail']);
            $v4Snapshots = is_array($order->product_info ?? null)
                ? ($order->product_info['localtonet_v4_snapshots'] ?? [])
                : [];
            $v4LegacySnap = is_array($order->product_info ?? null)
                ? ($order->product_info['localtonet_v4_snapshot'] ?? [])
                : [];
            $v4TunnelIds = $order->getAllLocaltonetProxyIds();
            $v4Proxies = [];
            $__allTunnelDetails = ($order->product_info ?? [])['localtonet_v4_tunnel_details'] ?? [];
            if (! $hideProxy && count($v4TunnelIds) > 0 && empty($__allTunnelDetails)) {
                try {
                    $order->fetchAndPersistAllTunnelDetails();
                    $order->refresh();
                    $__allTunnelDetails = ($order->product_info ?? [])['localtonet_v4_tunnel_details'] ?? [];
                } catch (\Throwable $e) {}
            }
            if (! $hideProxy) {
                foreach ($v4TunnelIds as $v4Idx => $v4Tid) {
                    $row = $__allTunnelDetails[(int) $v4Tid] ?? null;
                    if (! $row || ! is_array($row)) {
                        continue;
                    }
                    $snap = is_array($v4Snapshots[$v4Idx] ?? null) ? $v4Snapshots[$v4Idx] : (count($v4TunnelIds) === 1 ? $v4LegacySnap : []);
                    $v4PoolIpRow = trim((string) ($snap['selected_ip'] ?? ''));
                    $isSocks = function_exists('localtonet_tunnel_result_is_socks') && localtonet_tunnel_result_is_socks($row);
                    $v4AuthActiveRaw = data_get($row, 'authentication.isActive');
                    $v4AuthActive = $v4AuthActiveRaw === null
                        ? true
                        : filter_var($v4AuthActiveRaw, FILTER_VALIDATE_BOOLEAN);
                    $v4IpOnlyRow = ! $v4AuthActive;
                    $v4Ip = $v4PoolIpRow !== '' ? $v4PoolIpRow : trim((string) ($row['serverIp'] ?? ''));
                    $v4Port = isset($row['serverPort']) ? (string) $row['serverPort'] : '';
                    $v4User = trim((string) ($row['authentication']['userName'] ?? ''));
                    $v4Pass = (string) ($row['authentication']['password'] ?? '');
                    if ($v4IpOnlyRow) {
                        $v4Line = ($v4Ip !== '' && $v4Port !== '') ? $v4Ip.':'.$v4Port : '';
                    } else {
                        $v4Line = ($v4Ip !== '' && $v4Port !== '' && $v4User !== '' && $v4Pass !== '')
                            ? $v4Ip.':'.$v4Port.':'.$v4User.':'.$v4Pass
                            : '';
                    }
                    $v4Proxies[] = [
                        'tunnel_id' => (int) $v4Tid,
                        'ip' => $v4Ip,
                        'port' => $v4Port,
                        'user' => $v4User,
                        'pass' => $v4Pass,
                        'line' => $v4Line,
                        'ipOnly' => $v4IpOnlyRow,
                        'is_socks' => $isSocks,
                        'toggle_label' => $isSocks ? 'HTTP protokolüne geç' : 'SOCKS5 protokolüne geç',
                    ];
                }
            }
            $v4Multi = count($v4Proxies) > 1;
            $firstP4 = $v4Proxies[0] ?? null;
            $v4TopProtocolLabel = $firstP4['toggle_label'] ?? 'SOCKS5 protokolüne geç';
            if ($v4Multi && count($v4Proxies) > 0) {
                $socksCount = collect($v4Proxies)->filter(fn ($p) => ! empty($p['is_socks']))->count();
                $n = count($v4Proxies);
                if ($socksCount === $n) {
                    $v4TopProtocolLabel = 'Tüm tünellerde HTTP protokolüne geç';
                } elseif ($socksCount === 0) {
                    $v4TopProtocolLabel = 'Tüm tünellerde SOCKS5 protokolüne geç';
                } else {
                    $v4TopProtocolLabel = 'Tüm tünellerde protokolü tersine çevir';
                }
            }
            $v4CanToggleProtocolTop = ! $hideProxy && count($v4Proxies) >= 1;
            $v4IpOnlyDisplay = $firstP4['ipOnly'] ?? true;
            $v4BulkText = collect($v4Proxies)->pluck('line')->filter()->values()->implode("\n");
            $v4ConnectivityUrl = $ltRoute('v4ConnectivityTest');
            $v4ToggleProtocolUrl = $ltRoute('v4ToggleProtocol');
            $v4AdminPoolIp = '';
            if ($firstP4) {
                $snap0 = is_array($v4Snapshots[0] ?? null) ? $v4Snapshots[0] : $v4LegacySnap;
                $v4AdminPoolIp = trim((string) ($snap0['selected_ip'] ?? ''));
            }
        @endphp
        <div class="np-localtonet-v4-root" style="{{ $hideProxy ? 'filter: blur(2px); pointer-events: none;' : '' }}">
            <div class="card border border-primary border-opacity-25 bg-primary bg-opacity-10 mb-8 shadow-none">
                <div class="card-body p-6 p-lg-8">
                    <div class="mb-5">
                        <label class="form-label fw-semibold text-gray-800 mb-2">Proxy satırı (kopyala)</label>
                        <textarea id="np_ltv4_bulk" class="form-control form-control-solid font-monospace text-gray-800 w-100"
                                  style="min-height: 6.5rem;" rows="4" readonly
                                  placeholder="{{ $v4IpOnlyDisplay ? 'IP:Port' : 'IP:Port:Kullanıcı:Şifre' }}">{{ $v4BulkText }}</textarea>
                    </div>
                    <div class="row g-3 g-lg-4">
                        <div class="col-12 col-md-6">
                            <button type="button" class="btn btn-primary btn-sm w-100 py-2" data-np-ltv4-toggle-protocol
                                    @if($v4Multi) data-np-ltv4-toggle-protocol-bulk="1" @endif
                                    @if(! $v4CanToggleProtocolTop) disabled @endif><span class="me-1">⇄</span> {{ $v4TopProtocolLabel }}</button>
                            @if($v4Multi)
                                <p class="text-muted fs-8 mt-2 mb-0">İsterseniz her tünel kartındaki düğme ile tek tek de değiştirebilirsiniz.</p>
                            @endif
                        </div>
                        <div class="col-12 col-md-6">
                            <button type="button" class="btn btn-light btn-active-light-primary btn-sm w-100 py-2 border border-primary border-opacity-25"
                                    data-np-ltv4-copy-bulk>Tümünü kopyala</button>
                        </div>
                    </div>
                    <p class="text-muted fs-8 mt-6 mb-0 text-center px-2">IPv4 statik proxy — Protokol değişimi Localtonet API ile uygulanır. IP döndürme ve cihaz yeniden başlatma bu ürün türünde yoktur.</p>
                </div>
            </div>

            @php
                $v4AuthCreds = ($order->product_info ?? [])['localtonet_v4_auth_credentials'] ?? [];
                $v4FirstCred = ! empty($v4AuthCreds) ? collect($v4AuthCreds)->first() : null;
                $v4AuthIsActive = $v4FirstCred ? ($v4FirstCred['isActive'] ?? true) : (@$proxyLocaltonet['authentication']['isActive'] ?? true);
                $v4AuthUser = $v4FirstCred['userName'] ?? @$proxyLocaltonet['authentication']['userName'] ?? '';
                $v4AuthPass = $v4FirstCred['password'] ?? @$proxyLocaltonet['authentication']['password'] ?? '';
            @endphp
            <div id="ltv4_authorization_section" class="card border border-gray-300 mb-8" data-np-ltv4-auth>
                <div class="card-header border-0 min-h-60px h-60px bg-light-secondary">
                    <h3 class="card-title fw-bold">{{ __('Yetkilendirme') }}
                        <span class="badge badge-light-primary ms-2">{{ count($v4TunnelIds) }} proxy</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-3 mb-5 fs-7">
                        <i class="fa fa-info-circle me-1"></i>
                        Yapılan değişiklikler tüm <strong>{{ count($v4TunnelIds) }}</strong> proxy'ye uygulanır.
                    </div>
                    <form id="authorizationForm" action="{{ $ltRoute('authentication') }}" class="row g-5">
                        <div class="col-12">
                            <label class="required form-label fw-bold">{{ __('user_name') }} {{ __('password') }}
                                Kullanım Durumu</label>
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    {{ $v4AuthIsActive ? "checked='checked'" : '' }}>
                                <span class="form-check-label fw-semibold text-muted">{{ __('active') }}</span>
                            </label>
                        </div>
                        <div class="col-12 text-end userNamePassArea">
                            <div class="badge badge-success badeg-sm generateUserNamePassBtn cursor-pointer">Kullanıcı Adı
                                Parola Oluştur
                            </div>
                        </div>
                        <div class="col-xl-6 userNamePassArea">
                            <label class="required form-label">{{ __('user_name') }}</label>
                            <input name="user_name" value="{{ $v4AuthUser }}"
                                   class="form-control"/>
                        </div>
                        <div class="col-xl-6 userNamePassArea">
                            <label class="required form-label">{{ __('password') }}</label>
                            <input name="password" value="{{ $v4AuthPass }}"
                                   class="form-control"/>
                        </div>
                        <div class="col-12 whitelistArea">
                            <label class="form-label fw-semibold">Whitelist</label>
                            @php
                                $ltv4IpRestrictions = @$proxyLocaltonet['ipRestrictions'] ?? [];
                                $ltv4DrawIpRestrictions = '';
                                if (is_array($ltv4IpRestrictions) && count($ltv4IpRestrictions) > 0) {
                                    $ltv4DrawIpRestrictions = implode("\n", collect($ltv4IpRestrictions)->pluck('ipAddress')->toArray());
                                }
                            @endphp
                            <textarea name="whitelist" class="form-control" rows="4"
                                      placeholder="Her satırda bir IP. Kullanıcı adı/şifre kapalıyken yalnızca bu adreslerden erişim. Havuz IP'si otomatik eklenmez.">{{ $ltv4DrawIpRestrictions }}</textarea>
                            <div class="form-text">Aktif kapalıyken en az bir geçerli IP girin ve kaydedin.</div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-light-primary btn-sm" type="submit">
                                <span class="indicator-label">{{ __('save_changes') }}</span>
                                <span class="indicator-progress">{{ __('please_wait') }}...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($v4Proxies) <= 3)
                @forelse($v4Proxies as $v4Idx => $p4)
                    <div class="card shadow-sm border border-gray-300 mb-8 np-ltv4-tunnel-card">
                        <div class="card-body p-6 p-lg-8">
                            <div class="row g-6 align-items-lg-stretch">
                                <div class="col-lg-6 d-flex flex-column">
                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                        <span class="badge badge-light-dark align-self-center me-1">#{{ $v4Idx + 1 }}</span>
                                        @if($v4Multi)
                                            <button type="button" class="btn btn-sm btn-primary flex-grow-1 flex-sm-grow-0" data-np-ltv4-toggle-protocol data-np-ltv4-tunnel-id="{{ (int) $p4['tunnel_id'] }}"><span class="me-1">⇄</span> {{ $p4['toggle_label'] }}</button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-light-primary flex-grow-1 flex-sm-grow-0" data-np-ltv4-proxy-test="{{ (int) $v4Idx }}" data-np-ltv4-tunnel-id="{{ (int) $p4['tunnel_id'] }}">Proxy Test Et</button>
                                        <button type="button" class="btn btn-sm btn-light-primary flex-grow-1 flex-sm-grow-0" data-np-ltv4-ping-test="{{ (int) $v4Idx }}" data-np-ltv4-tunnel-id="{{ (int) $p4['tunnel_id'] }}">Bağlantı / Gecikme</button>
                                    </div>
                                    <div class="table-responsive flex-grow-1 rounded border border-gray-300 bg-body p-4 np-ltv4-cred-box" style="min-height: 280px;">
                                        <table class="table table-row-bordered align-middle fs-6 gy-3 mb-0">
                                            <tbody>
                                            <tr>
                                                <td class="text-gray-600 fw-semibold w-125px">Protokol</td>
                                                <td class="text-gray-900 fw-semibold">
                                                    @if(! empty($p4['is_socks']))
                                                        <span class="badge badge-light-success">SOCKS5</span>
                                                    @else
                                                        <span class="badge badge-light-primary">HTTP</span>
                                                    @endif
                                                </td>
                                                <td class="text-end w-50px"></td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-600 fw-semibold w-125px">IP</td>
                                                <td class="font-monospace text-gray-900 fw-semibold">{{ $p4['ip'] !== '' ? $p4['ip'] : '—' }}</td>
                                                <td class="text-end w-50px">
                                                    @if($p4['ip'] !== '')
                                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2" data-np-ltv4-copy="{{ $p4['ip'] }}">Kopyala</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-600 fw-semibold">Port</td>
                                                <td class="font-monospace text-gray-900 fw-semibold">{{ $p4['port'] !== '' ? $p4['port'] : '—' }}</td>
                                                <td class="text-end">
                                                    @if($p4['port'] !== '')
                                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2" data-np-ltv4-copy="{{ $p4['port'] }}">Kopyala</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if(empty($p4['ipOnly']))
                                            <tr>
                                                <td class="text-gray-600 fw-semibold">Kullanıcı Adı</td>
                                                <td class="font-monospace text-gray-900 fw-semibold">{{ $p4['user'] !== '' ? $p4['user'] : '—' }}</td>
                                                <td class="text-end">
                                                    @if($p4['user'] !== '')
                                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2" data-np-ltv4-copy="{{ $p4['user'] }}">Kopyala</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-600 fw-semibold">Şifre</td>
                                                <td class="font-monospace text-gray-900 fw-semibold">{{ $p4['pass'] !== '' ? $p4['pass'] : '—' }}</td>
                                                <td class="text-end">
                                                    @if($p4['pass'] !== '')
                                                        <button type="button" class="btn btn-sm btn-light btn-active-light-primary py-1 px-2" data-np-ltv4-copy="{{ $p4['pass'] }}">Kopyala</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-6 d-flex flex-column">
                                    <pre class="rounded bg-gray-900 text-gray-400 p-4 font-monospace fs-7 mb-0 w-100 flex-grow-1 np-ltv4-terminal"
                                         style="min-height: 280px;"
                                         data-np-ltv4-terminal="{{ (int) $v4Idx }}">Test sonuçlarınız burada görüntülenecek...</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    @if(! $hideProxy)
                        <div class="alert alert-light-warning mb-8">Proxy bilgisi henüz oluşmadı veya API yanıtı eksik.</div>
                    @endif
                @endforelse
            @elseif(count($v4Proxies) > 0)
                {{-- Compact table view for many tunnels --}}
                <div class="card shadow-sm border border-gray-300 mb-8">
                    <div class="card-header min-h-50px bg-light-secondary py-3">
                        <h3 class="card-title fw-bold fs-6 mb-0">Tüm Tüneller ({{ count($v4Proxies) }})</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-hover align-middle fs-7 gy-3 mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 w-50px">#</th>
                                        <th>Protokol</th>
                                        <th>IP</th>
                                        <th>Port</th>
                                        <th>Kullanıcı</th>
                                        <th>Şifre</th>
                                        <th class="text-end pe-4">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($v4Proxies as $v4Idx => $p4)
                                    <tr>
                                        <td class="ps-4 text-gray-600">{{ $v4Idx + 1 }}</td>
                                        <td>
                                            @if(! empty($p4['is_socks']))
                                                <span class="badge badge-sm badge-light-success">SOCKS5</span>
                                            @else
                                                <span class="badge badge-sm badge-light-primary">HTTP</span>
                                            @endif
                                        </td>
                                        <td class="font-monospace text-gray-900">{{ $p4['ip'] !== '' ? $p4['ip'] : '—' }}</td>
                                        <td class="font-monospace text-gray-900">{{ $p4['port'] !== '' ? $p4['port'] : '—' }}</td>
                                        @if(empty($p4['ipOnly']))
                                            <td class="font-monospace text-gray-900">{{ $p4['user'] !== '' ? $p4['user'] : '—' }}</td>
                                            <td class="font-monospace text-gray-900">{{ $p4['pass'] !== '' ? $p4['pass'] : '—' }}</td>
                                        @else
                                            <td colspan="2" class="text-muted">IP Only</td>
                                        @endif
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary" data-np-ltv4-copy="{{ $p4['line'] }}" title="Kopyala"><i class="fa fa-copy"></i></button>
                                            @if($v4Multi)
                                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" data-np-ltv4-toggle-protocol data-np-ltv4-tunnel-id="{{ (int) $p4['tunnel_id'] }}" title="{{ $p4['toggle_label'] }}"><i class="fa fa-exchange-alt"></i></button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" data-np-ltv4-proxy-test="{{ (int) $v4Idx }}" data-np-ltv4-tunnel-id="{{ (int) $p4['tunnel_id'] }}" title="Proxy Test"><i class="fa fa-plug"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <pre class="rounded bg-gray-900 text-gray-400 p-4 font-monospace fs-7 mb-8 np-ltv4-terminal" style="min-height: 100px;" data-np-ltv4-terminal="0">Test sonuçlarınız burada görüntülenecek...</pre>
            @else
                @if(! $hideProxy)
                    <div class="alert alert-light-warning mb-8">Proxy bilgisi henüz oluşmadı veya API yanıtı eksik.</div>
                @endif
            @endif

        </div>
    @else
    <div style="{{$hideProxy ? "filter: blur(2px); pointer-events: none;" : ""}}">
        <div class="row mb-5">
            <div class="col-xl-8">
                <div class="row">
                    <div class="col-xxl-6">
                        <!--begin::Label-->
                        <label class="required form-label">PORT</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="server_port"
                               value="{{!$hideProxy ? @$proxyLocaltonet["serverPort"] : ""}}"
                               class="form-control"/>
                        <!--end::Input-->
                        <button class="btn btn-light-primary btn-sm mt-2 changePortBtn">Portu Kaydet
                        </button>
                    </div>
                    <div class="col-xxl-6">
                        <!--begin::Label-->
                        <label class="required form-label">Server IP</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="" disabled
                               value="{{!$hideProxy ? @$proxyLocaltonet["serverIp"] : ""}}"
                               class="form-control"/>
                        <!--end::Input-->
                    </div>
                </div>
            </div>
            <div class="col-xl-4 text-end">
                <button
                    class="btn btn-{{$proxyLocaltonet && $proxyLocaltonet["status"]  == 1 ? "danger" : "success"}} btn-sm proxyChangeStatusBtn"
                    data-alert-text="{{$proxyLocaltonet && $proxyLocaltonet["status"]  == 1 ? "Durdurmak istediğinize emin misiniz?" : "Başlatmak istediğinze emin misiniz?"}}"
                    data-action="{{$proxyLocaltonet && $proxyLocaltonet["status"]  == 1 ? $ltRoute('stop') : $ltRoute('start')}}">
                    <!--begin::Indicator label-->
                    <span
                        class="indicator-label">{{$proxyLocaltonet && $proxyLocaltonet["status"] == 1 ? "Proxyi Durdur" : "Proxyi Başlat"}}</span>
                    <!--end::Indicator label-->
                    <!--begin::Indicator progress-->
                    <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    <!--end::Indicator progress-->
                </button>
            </div>
        </div>
        <!--begin:::Tabs-->
        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold gap-4 d-flex align-items-center flex-wrap mb-10">
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link mx-0 text-active-primary pb-4 active"
                   data-bs-toggle="tab"
                   href="#localtonet_general_tab">{{__("general")}}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link mx-0 text-active-primary pb-4"
                   data-bs-toggle="tab"
                   href="#localtonet_authorization_tab">{{__("Yetkilendirme")}}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link mx-0 text-active-primary pb-4"
                   data-bs-toggle="tab"
                   href="#localtonet_ip_history_tab">{{__("IP Yönetimi")}}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link mx-0 text-active-primary pb-4"
                   data-bs-toggle="tab"
                   href="#localtonet_bandwidth_tab">{{__("Kullanım Detayları")}}</a>
            </li>
            <!--end:::Tab item-->
            @if($isAdminContext)
            <li class="nav-item">
                <a class="nav-link mx-0 text-active-primary pb-4"
                   data-bs-toggle="tab"
                   href="#localtonet_admin_adjust_tab">Ek kota / süre</a>
            </li>
            @endif
            @if(!$isAdminContext)
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link mx-0 text-active-primary pb-4"
                   data-bs-toggle="tab"
                   href="#localtonet_order_extensions_tab">{{__("add_duration_and_quota")}}</a>
            </li>
            <!--end:::Tab item-->
            @endif
        </ul>
        <!--end:::Tabs-->
        <!--begin:::Tab content-->
        <div class="tab-content" id="localtonet_tab_content">
            <!--begin:::Tab pane-->
            <div class="tab-pane fade show active"
                 id="localtonet_general_tab" role="tabpanel">
                <div class="row mb-5 g-5">
                    <div class="col-xl-6">
                        <div class="card text-center">
                            <div class="card-body p-4">
                                <div class="form-label fw-bolder fs-3 mb-4">IP Değiştir</div>
                                <button class="btn btn-light-primary ipChangeBtn"
                                        data-swal-text="{{__("are_you_sure_you_want_to_change_ip")}}"
                                        data-ajax-url="{{ $ltRoute('ipChangePost', ['t' => base64_encode(@$proxyLocaltonet["airplaneMode"]["ipChangeLink"]["linkToken"])]) }}">
                                    IP Değiştir
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card text-center">
                            <div class="card-body p-4">
                                <div class="form-label fw-bolder fs-3 mb-4">Cihazı Yeniden Başlat</div>
                                <button class="btn btn-light-primary deviceRestartBtn"
                                        data-swal-text="{{__("are_you_sure_you_want_to_restart_the_device")}}"
                                        data-ajax-url="{{ $ltRoute('deviceRestartPost', ['t' => base64_encode(@$proxyLocaltonet["airplaneMode"]["deviceRestartLink"]["linkToken"])]) }}">
                                    Yeniden Başlat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table class="table table-row-bordered gy-3" style="overflow-wrap: anywhere;">
                            <tbody>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6 min-w-150px">{{ __("status") }}</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        {!! @$proxyLocaltonet["status"] ? "<span class='badge badge-success'>ONLINE</span>" : "<span class='badge badge-danger'>OFFLINE</span>" !!}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6">{{ __("protocol") }}</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        {{@$proxyLocaltonet["drawProtocolType"]}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6">{{ __("proxy_info_label") }}</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        {{@$proxyLocaltonet["drawProxy"]}}
                                        <span
                                            data-text="{{@$proxyLocaltonet["drawProxy"]}}"
                                            class="copy-text cursor-pointer text-hover-primary ms-1"><i
                                                class="fa fa-copy fw-bold"></i></span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6">IP Değiştir</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        {{route("portal.ipChange", ["order"=> $order->id, "t" => base64_encode(@$proxyLocaltonet["airplaneMode"]["ipChangeLink"]["linkToken"])])}}
                                        <span
                                            data-text="{{route("portal.ipChange", ["order"=> $order->id, "t" => base64_encode(@$proxyLocaltonet["airplaneMode"]["ipChangeLink"]["linkToken"])])}}"
                                            class="copy-text cursor-pointer text-hover-primary ms-1"><i
                                                class="fa fa-copy fw-bold"></i></span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6">Cihazı Yeniden Başlat</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        {{route("portal.deviceRestart", ["order"=> $order->id, "t" => base64_encode(@$proxyLocaltonet["airplaneMode"]["deviceRestartLink"]["linkToken"])])}}
                                        <span
                                            data-text="{{route("portal.deviceRestart", ["order"=> $order->id, "t" => base64_encode(@$proxyLocaltonet["airplaneMode"]["deviceRestartLink"]["linkToken"])])}}"
                                            class="copy-text cursor-pointer text-hover-primary ms-1"><i
                                                class="fa fa-copy fw-bold"></i></span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6">{{__("total_usage")}}</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        @if(isset($proxyLocaltonet["bandwidthUsage"]) && is_numeric($proxyLocaltonet["bandwidthUsage"]))
                                            <span
                                                class="{{$proxyLocaltonet["bgBandwidthUsage"] ? "text-" . $proxyLocaltonet["bgBandwidthUsage"] : ""}}">{{convertByteToGB($proxyLocaltonet["bandwidthUsage"])}} GB</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-gray-800 fw-bold fs-6">{{__("usage_limit")}}</td>
                                <td class="text-gray-600 fw-semibold fs-6">
                                    @if(!$hideProxy)
                                        @if(isset($proxyLocaltonet) && isset($proxyLocaltonet["bandwidthLimit"]))
                                            @if(is_numeric($proxyLocaltonet["bandwidthLimit"]))
                                                {{ convertByteToGB($proxyLocaltonet["bandwidthLimit"]) }} GB
                                            @elseif($proxyLocaltonet["bandwidthLimit"] == "unlimited")
                                                {{ __("unlimited") }}
                                            @endif
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade"
                 id="localtonet_authorization_tab" role="tabpanel">
                <form id="authorizationForm"
                      action="{{ $ltRoute('authentication') }}"
                      class="row g-5">
                    <div class="col-12">
                        <!--begin::Label-->
                        <label
                            class="required form-label fw-bold">{{__("user_name")}} {{__("password")}}
                            Kullanım Durumu</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{@$proxyLocaltonet["authentication"]["isActive"] ? "checked='checked'" : ""}}>
                            <span
                                class="form-check-label fw-semibold text-muted">{{__("active")}}</span>
                        </label>
                        <!--end::Input-->
                    </div>
                    <div class="col-12 text-end userNamePassArea">
                        <div class="badge badge-success badeg-sm generateUserNamePassBtn cursor-pointer">Kullanıcı Adı
                            Parola Oluştur
                        </div>
                    </div>
                    <div class="col-xl-6 userNamePassArea">
                        <!--begin::Label-->
                        <label class="required form-label">{{__("user_name")}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="user_name"
                               value="{{@$proxyLocaltonet["authentication"]["userName"]}}"
                               class="form-control"/>
                        <!--end::Input-->
                    </div>
                    <div class="col-xl-6 userNamePassArea">
                        <!--begin::Label-->
                        <label class="required form-label">{{__("password")}}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input name="password"
                               value="{{@$proxyLocaltonet["authentication"]["password"]}}"
                               class="form-control"/>
                        <!--end::Input-->
                    </div>
                    <div class="col-12 whitelistArea">
                        <!--begin::Label-->
                        <label class="required form-label">Whitelist</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        @php
                            $ipRestrictions = @$proxyLocaltonet["ipRestrictions"] ?? "";
                            $drawIpRestrictions = "";
                            if ($ipRestrictions && count($ipRestrictions) > 0){
                                $drawIpRestrictions = collect($ipRestrictions)->pluck("ipAddress")->toArray();
                                $drawIpRestrictions = implode("\n", $drawIpRestrictions);
                            }
                        @endphp
                        <textarea name="whitelist" class="form-control"
                                  placeholder="Sadece bu ip adreslerinden gelen istekler onaylanacaktır. IP Adreslerini alt alta yazmayı unutmayınız!"
                                  rows="3">{{$drawIpRestrictions}}</textarea>
                        <!--end::Input-->
                    </div>
                    <div class="col-12">
                        <button class="btn btn-light-primary btn-sm" type="submit">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">{{__('save_changes')}}</span>
                            <!--end::Indicator label-->
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                </form>
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade"
                 id="localtonet_ip_history_tab" role="tabpanel">
                <div class="card text-center mb-5">
                    <div class="card-body p-4">
                        <div class="form-label fw-bolder fs-3 mb-4">{{__("automatic_ip_renewal")}}</div>
                        <div class="text-center mb-4">
                            @if(@$proxyLocaltonet["airplaneMode"]["isAirPlaneModeOn"])
                                <span
                                    class="text-gray-800 fw-bold fs-6">{{__("duration")}}: {{@$proxyLocaltonet["airplaneMode"]["time"]}} {{__("seconds")}} <i
                                        class="fa fa-info-circle ms-1 fs-5" data-bs-toggle="tooltip"
                                        title="Belirtilen süre miktarında bir ip yenilenir. Süreyi düzenlemek için ip yenilemeyi durdurup yeniden başlatabilirsiniz."></i></span>
                            @endif
                        </div>
                        <button
                            class="btn btn-light-{{@$proxyLocaltonet["airplaneMode"]["isAirPlaneModeOn"] ? "danger active" : "primary passive"}} changeAirplaneModeBtn">{{@$proxyLocaltonet["airplaneMode"]["isAirPlaneModeOn"] ? __("Durdur") : __("Başlat")}}</button>
                    </div>
                </div>
                <div class="table-responsive" data-np-ip-history="area">

                </div>
                <div data-np-ip-history="table-template" class="d-none">
                    <table data-np-ip-history="table" class="table g-3 table-row-dashed">
                        <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>IP</th>
                            <th>{{__("date")}}</th>
                        </tr>
                        </thead>
                        <tbody class="text-gray-800 fw-semibold fs-6" data-np-ip-history="items">
                        <tr data-np-ip-history="loader">
                            <td colspan="2">
                                <div class="d-flex flex-center mt-10">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade"
                 id="localtonet_bandwidth_tab" role="tabpanel">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card text-center">
                            <div class="card-body p-4">
                                <div class="form-label fw-bolder fs-3 mb-4">{{__("total_usage")}}</div>
                                <div class="badge badge-secondary badge-lg px-6 py-4">
                                    @if(isset($proxyLocaltonet["bandwidthUsage"]) && is_numeric($proxyLocaltonet["bandwidthUsage"]))
                                        {{convertByteToGB($proxyLocaltonet["bandwidthUsage"])}} GB
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card text-center">
                            <div class="card-body p-4">
                                <div class="form-label fw-bolder fs-3 mb-4">{{__("usage_limit")}}</div>
                                <div class="badge badge-secondary badge-lg px-6 py-4">
                                    @if(isset($proxyLocaltonet) && isset($proxyLocaltonet["bandwidthLimit"]))
                                        @if(is_numeric($proxyLocaltonet["bandwidthLimit"]))
                                            {{ convertByteToGB($proxyLocaltonet["bandwidthLimit"]) }} GB
                                        @elseif($proxyLocaltonet["bandwidthLimit"] == "unlimited")
                                            {{ __("unlimited") }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end:::Tab pane-->
            @if($isAdminContext)
            <div class="tab-pane fade" id="localtonet_admin_adjust_tab" role="tabpanel">
                <div class="alert alert-warning mb-7">
                    <span class="fw-semibold">Yönetim:</span>
                    Fatura oluşturulmaz; kota ve süre Localtonet ile sipariş kaydına
                    <strong>anında</strong> yansır. En az bir alan doldurun. Sınırsız trafikte kota artırılamaz.
                </div>
                <form id="npAdminLocaltonetAdjustForm" class="row g-6">
                    <div class="col-md-6">
                        <label class="form-label">Ek kota (GB)</label>
                        <input type="number" name="add_gb" class="form-control" step="0.01" min="0.01"
                               placeholder="Örn. 10">
                        <div class="form-text">Mevcut limite eklenecek gigabayt.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ek süre (gün)</label>
                        <input type="number" name="add_days" class="form-control" min="1" placeholder="Örn. 7">
                        <div class="form-text">Sipariş bitişi ve tunnel son kullanımına eklenecek gün.</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Kaydet ve uygula</span>
                            <span class="indicator-progress">{{__("please_wait")}}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
            @endif
            @if(!$isAdminContext)
            <!--begin:::Tab pane-->
            <div class="tab-pane fade"
                 id="localtonet_order_extensions_tab" role="tabpanel">
                <div class="row gap-5">
                    <div class="col-12 text-center">
                        <span class="fw-bold fs-6">Seçeceğiniz paket kadar uzatma faturanız oluşturulacaktır.</span>
                    </div>
                    <div class="col-12">
                        @if($order->product)
                            @if($order->product->findAttrsByServiceType('quota'))
                                <div class="text-center fw-bold fs-6 my-5">{{__("extend_quota")}}</div>
                                <div class="row mx-auto" style="width: 80%">
                                    <div class="col-xl-6">
                                        <x-portal.draw-form-element
                                            :value="@$order->product->findAttrsByServiceType('quota')['options'][0]['value']"
                                            :element="$order->product->findAttrsByServiceType('quota')"/>
                                    </div>
                                    <div class="col-xl-6">
                                        <button type="button" class="btn btn-primary w-100 addQuotaBtn">
                                            <!--begin::Indicator label-->
                                            <span class="indicator-label"><i
                                                    class="fa fa-plus"></i> Kota Ekle</span>
                                            <!--end::Indicator label-->
                                            <!--begin::Indicator progress-->
                                            <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            <!--end::Indicator progress-->
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div
                        class="col-12 {{@$order->activeDetails[0]->price_data["duration_unit"] == "ONE_TIME" ? "d-none" : ""}}">
                        @if($order->product)
                            @if($order->product->findAttrsByServiceType('quota_duration'))
                                <div class="text-center fw-bold fs-6 my-5">{{__("quota_and_time_extension")}}</div>
                                <div class="row mx-auto" style="width: 80%">
                                    <div class="col-xl-6">
                                        <x-portal.draw-form-element
                                            :value="@$order->product->findAttrsByServiceType('quota_duration')['options'][0]['value']"
                                            :element="$order->product->findAttrsByServiceType('quota_duration')"/>
                                    </div>
                                    <div class="col-xl-6">
                                        <button type="button" class="btn btn-primary w-100 addQuotaDurationBtn">
                                            <!--begin::Indicator label-->
                                            <span class="indicator-label"><i
                                                    class="fa fa-plus"></i> Kota ve Süre Ekle</span>
                                            <!--end::Indicator label-->
                                            <!--begin::Indicator progress-->
                                            <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            <!--end::Indicator progress-->
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <!--end:::Tab pane-->
            @endif
        </div>
        <!--end:::Tab content-->
    </div>
    @endif
    @if($isAdminContext && !$order->isCanDeliveryType('LOCALTONETV4'))
        <div class="modal fade" id="changeAirplaneModeModal" data-bs-backdrop="static" data-bs-keyboard="false"
             tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header" id="npAdminAirplaneModal_header">
                        <h2>{{__("automatic_ip_renewal")}}</h2>
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body py-lg-10 px-lg-15">
                        <form id="changeAirplaneModeForm"
                              action="{{ route($ltPrefix.'setAutoAirplaneModeSetting', ['order' => $order->id]) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="mb-5 alert alert-primary">En az 30 saniye kabul edilir.</div>
                                    <label class="required form-label">{{__("duration")}} ({{__("seconds")}})</label>
                                    <input type="number" name="time" min="30" class="form-control" required>
                                </div>
                            </div>
                            <div class="d-flex flex-center flex-row-fluid pt-12">
                                <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                                <button type="submit" class="btn btn-primary">
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
    @endif
@elseif($order->isCanDeliveryType('LOCALTONET_ROTATING'))
    @php
        $lrPi = $order->product_info ?? [];
        $lrHost = $lrPi['lr_host'] ?? ($order->product_data['delivery_items']['host'] ?? '');
        $lrPort = $lrPi['lr_port'] ?? ($order->product_data['delivery_items']['port'] ?? '');
        $lrUsername = $lrPi['lr_username'] ?? '';
        $lrPassword = $lrPi['lr_password'] ?? '';
        $lrActive = $order->status === 'ACTIVE' && $order->delivery_status === 'DELIVERED';
    @endphp
    @if(!$lrActive && !$isAdminContext)
        <div class="alert alert-primary d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            </div>
            <div class="d-flex align-items-center">
                <h6 class="mb-0 text-primary">Hizmet durumunuz aktif olmadığı için proxy bilgileri görüntülenemez.
                    (Hizmet Durumu: {{__(mb_strtolower($order->status))}})</h6>
            </div>
        </div>
    @else
        <div class="row g-5" id="lrProxyPanel">
            {{-- Sol: Proxy Generator --}}
            <div class="col-lg-4">
                <div class="border rounded p-5 h-100">
                    <h4 class="fw-bold text-gray-800 mb-5 text-center">Proxy Oluşturucu</h4>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Proxy Tipi</label>
                        <select class="form-select form-select-sm" id="lrProxyType">
                            <option value="rotating">Rotating</option>
                            <option value="sticky">Sticky</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Proxy Adedi</label>
                        <input type="number" class="form-control form-control-sm" id="lrProxyAmount" value="1" min="1" max="100">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Proxy Formatı</label>
                        <select class="form-select form-select-sm" id="lrProxyFormat">
                            <option value="ip:port:user:pass">IP:PORT:KULLANICI:ŞİFRE</option>
                            <option value="user:pass@ip:port">KULLANICI:ŞİFRE@IP:PORT</option>
                            <option value="ip:port@user:pass">IP:PORT@KULLANICI:ŞİFRE</option>
                            <option value="user:pass:ip:port">KULLANICI:ŞİFRE:IP:PORT</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary w-100 fw-bold" id="lrGenerateBtn">
                        <i class="fa fa-bolt me-1"></i> Oluştur
                    </button>
                </div>
            </div>

            {{-- Orta: Üretilen Proxyler --}}
            <div class="col-lg-4">
                <div class="border rounded p-5 h-100">
                    <h4 class="fw-bold text-gray-800 mb-5 text-center">Üretilen Proxyler</h4>
                    <textarea class="form-control bg-light-dark text-gray-700" id="lrGeneratedProxies" rows="10" readonly placeholder="Üretilen proxyler burada görünecek."></textarea>
                    <button type="button" class="btn btn-success w-100 mt-3 fw-semibold" id="lrCopyBtn">
                        <i class="fa fa-copy me-1"></i> Kopyala
                    </button>
                </div>
            </div>

            {{-- Sağ: Proxy Bilgileri --}}
            <div class="col-lg-4">
                <div class="border rounded p-5 h-100">
                    <h4 class="fw-bold text-gray-800 mb-5 text-center">Proxy Bilgileri</h4>

                    <div class="mb-4">
                        <div class="text-gray-500 text-uppercase fw-bold fs-8 mb-1">PROXY IP</div>
                        <div class="d-flex align-items-center justify-content-between bg-light rounded px-3 py-2">
                            <span class="fw-semibold text-gray-800" id="lrInfoHost">{{ $lrHost }}</span>
                            <span class="copy-text cursor-pointer text-hover-primary" data-copy="{{ $lrHost }}"><i class="fa fa-copy"></i></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-gray-500 text-uppercase fw-bold fs-8 mb-1">PROXY PORT</div>
                        <div class="d-flex align-items-center justify-content-between bg-light rounded px-3 py-2">
                            <span class="fw-semibold text-gray-800" id="lrInfoPort">{{ $lrPort }}</span>
                            <span class="copy-text cursor-pointer text-hover-primary" data-copy="{{ $lrPort }}"><i class="fa fa-copy"></i></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-gray-500 text-uppercase fw-bold fs-8 mb-1">KULLANICI ADI</div>
                        <div class="d-flex align-items-center justify-content-between bg-light rounded px-3 py-2">
                            <span class="fw-semibold text-gray-800" id="lrInfoUser">{{ $lrUsername }}</span>
                            <span class="copy-text cursor-pointer text-hover-primary" data-copy="{{ $lrUsername }}"><i class="fa fa-copy"></i></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-gray-500 text-uppercase fw-bold fs-8 mb-1">ŞİFRE</div>
                        <div class="d-flex align-items-center justify-content-between bg-light rounded px-3 py-2">
                            <span class="fw-semibold text-gray-800" id="lrInfoPass">{{ $lrPassword }}</span>
                            <button type="button" class="btn btn-sm btn-icon btn-light-primary" id="lrChangePassBtn" title="Şifre Değiştir">
                                <i class="fa fa-pen fs-7"></i>
                            </button>
                        </div>
                    </div>

                    <div class="separator my-4"></div>
                    <h5 class="fw-bold text-gray-800 mb-4">Plan Bilgileri</h5>

                    <div id="lrPlanInfo">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa fa-calendar-alt text-primary me-3 fs-4"></i>
                            <div>
                                <div class="text-gray-500 fs-8 text-uppercase fw-bold">Bitiş Tarihi</div>
                                <div class="fw-semibold text-gray-800" id="lrInfoExpiry">
                                    {{ $order->end_date ? $order->end_date->format('d/m/Y') : 'Sınırsız' }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <i class="fa fa-database text-primary me-3 fs-4"></i>
                            <div>
                                <div class="text-gray-500 fs-8 text-uppercase fw-bold">Kullanılan Bant Genişliği</div>
                                <div class="fw-semibold text-gray-800" id="lrInfoUsage">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <i class="fa fa-chart-pie text-primary me-3 fs-4"></i>
                            <div>
                                <div class="text-gray-500 fs-8 text-uppercase fw-bold">Kalan</div>
                                <div class="fw-semibold text-gray-800" id="lrInfoRemaining">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <i class="fa fa-tachometer-alt text-primary me-3 fs-4"></i>
                            <div>
                                <div class="text-gray-500 fs-8 text-uppercase fw-bold">Toplam Kota</div>
                                <div class="fw-semibold text-gray-800" id="lrInfoTotal">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('js')
        <script>
        $(document).ready(function() {
            var lrHost = @json($lrHost);
            var lrPort = @json($lrPort);
            var lrUser = @json($lrUsername);
            var lrPass = @json($lrPassword);

            function lrFormatBytes(bytes) {
                if (bytes === null || bytes === undefined) return '-';
                if (typeof bytes === 'string') {
                    var m = bytes.match(/[\d,\.]+/);
                    bytes = m ? parseFloat(m[0].replace(',', '.')) : 0;
                }
                bytes = parseFloat(bytes);
                if (isNaN(bytes) || bytes === 0) return '0 B';
                var units = ['B', 'KB', 'MB', 'GB', 'TB'];
                var i = 0;
                while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
                return bytes.toFixed(2) + ' ' + units[i];
            }

            function lrBuildProxy(format, user, pass, host, port) {
                switch (format) {
                    case 'user:pass@ip:port': return user + ':' + pass + '@' + host + ':' + port;
                    case 'ip:port@user:pass': return host + ':' + port + '@' + user + ':' + pass;
                    case 'user:pass:ip:port': return user + ':' + pass + ':' + host + ':' + port;
                    default: return host + ':' + port + ':' + user + ':' + pass;
                }
            }

            function lrRandomSession() {
                return Math.floor(100000 + Math.random() * 900000);
            }

            $('#lrGenerateBtn').on('click', function() {
                var type = $('#lrProxyType').val();
                var amount = Math.max(1, Math.min(100, parseInt($('#lrProxyAmount').val()) || 1));
                var format = $('#lrProxyFormat').val();
                var lines = [];

                for (var i = 0; i < amount; i++) {
                    var u = lrUser;
                    if (type === 'sticky') {
                        u = lrUser + '-session-' + lrRandomSession();
                    }
                    lines.push(lrBuildProxy(format, u, lrPass, lrHost, lrPort));
                }

                $('#lrGeneratedProxies').val(lines.join('\n'));
            });

            $('#lrCopyBtn').on('click', function() {
                var text = $('#lrGeneratedProxies').val();
                if (!text) return;
                navigator.clipboard.writeText(text).then(function() {
                    Swal.fire({ icon: 'success', title: 'Kopyalandı!', timer: 1000, showConfirmButton: false });
                });
            });

            $(document).on('click', '[data-copy]', function() {
                var text = $(this).data('copy');
                navigator.clipboard.writeText(text).then(function() {
                    Swal.fire({ icon: 'success', title: 'Kopyalandı!', timer: 800, showConfirmButton: false });
                });
            });

            $('#lrChangePassBtn').on('click', function() {
                var btn = $(this);
                Swal.fire({
                    icon: 'warning',
                    title: 'Şifre Değiştir',
                    text: 'Yeni bir şifre oluşturulacak. Mevcut şifre geçersiz olacaktır. Devam etmek istiyor musunuz?',
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Evet, Değiştir',
                    cancelButtonText: 'İptal'
                }).then(function(r) {
                    if (r.isConfirmed) {
                        $.ajax({
                            type: 'POST',
                            url: '{{ route("portal.orders.localtonet.lrChangePassword", ["order" => $order->id]) }}',
                            data: { _token: '{{ csrf_token() }}' },
                            dataType: 'json',
                            beforeSend: function() { btn.prop('disabled', true); },
                            complete: function(data) {
                                btn.prop('disabled', false);
                                var res = data.responseJSON;
                                if (res && res.success) {
                                    lrPass = res.new_password;
                                    $('#lrInfoPass').text(lrPass);
                                    Swal.fire({ icon: 'success', title: 'Şifre değiştirildi!', text: 'Yeni şifre: ' + lrPass, showCancelButton: true, showConfirmButton: false, cancelButtonText: 'Kapat' });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Hata', text: res?.message || 'Bir hata oluştu.' });
                                }
                            }
                        });
                    }
                });
            });

            $.ajax({
                type: 'POST',
                url: '{{ route("portal.orders.localtonet.lrGetClients", ["order" => $order->id]) }}',
                data: { _token: '{{ csrf_token() }}' },
                dataType: 'json',
                complete: function(data) {
                    var res = data.responseJSON;
                    if (res && res.success) {
                        $('#lrInfoUsage').text(res.lr_usage !== null ? lrFormatBytes(res.lr_usage) : '-');
                        $('#lrInfoTotal').text(res.lr_bandwidth !== null ? lrFormatBytes(res.lr_bandwidth) : 'Sınırsız');
                        $('#lrInfoRemaining').text(res.lr_remaining !== null ? lrFormatBytes(res.lr_remaining) : 'Sınırsız');
                        if (res.lr_expiration) {
                            var dt = new Date(res.lr_expiration);
                            if (!isNaN(dt)) {
                                $('#lrInfoExpiry').text(dt.toLocaleDateString('tr-TR'));
                            }
                        }
                    } else {
                        $('#lrInfoUsage, #lrInfoTotal, #lrInfoRemaining').text('-');
                    }
                }
            });
        });
        </script>
        @endpush
    @endif
@elseif($order->isPProxyDelivery())
    @php
        $ppPi = $order->product_info ?? [];
        $ppUuid = $ppPi['pproxy_uuid'] ?? '';
        $ppUsername = $ppPi['pproxy_username'] ?? '';
        $ppPassword = $ppPi['pproxy_password'] ?? '';
        $ppServerIp = $ppPi['pproxy_server_ip'] ?? '';
        $ppServerPort = $ppPi['pproxy_server_port'] ?? '';
        $ppQuotaGb = $ppPi['pproxy_quota_gb'] ?? '';
        $ppDays = $ppPi['pproxy_days'] ?? 30;
        $ppRaw = $ppPi['pproxy_raw'] ?? [];
        $ppActiveUntil = $ppRaw['active_until'] ?? '';
        $ppCreatedAt = $ppRaw['created_at'] ?? '';
        $ppDataBytes = $ppRaw['proxy_information']['data'] ?? 0;
        $ppThreads = $ppRaw['proxy_information']['threads'] ?? 0;
        $ppBpsLimit = $ppRaw['proxy_information']['BpsLimit'] ?? 0;
        $ppActive = $order->status === 'ACTIVE' && $order->delivery_status === 'DELIVERED';
    @endphp
    @if(!$ppActive && !$isAdminContext)
        <div class="alert alert-primary d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            </div>
            <div class="d-flex align-items-center">
                <h6 class="mb-0 text-primary">Hizmet durumunuz aktif olmadığı için proxy bilgileri görüntülenemez.
                    (Hizmet Durumu: {{__(mb_strtolower($order->status))}})</h6>
            </div>
        </div>
    @else
        @push('css')
        <style>
            .pp-dashboard { display: grid; grid-template-columns: 320px 1fr 320px; gap: 20px; align-items: stretch; }
            .pp-gen-card { grid-column: 1; grid-row: 1; }
            .pp-proxies-card { grid-column: 2; grid-row: 1; display: flex; flex-direction: column; }
            .pp-proxies-card .pp-textarea { flex: 1; min-height: 200px; }
            .pp-info-card { grid-column: 3; grid-row: 1 / 3; }
            .pp-test-card { grid-column: 1 / 3; grid-row: 2; opacity: 0.4; pointer-events: none; transition: opacity 0.3s ease; }
            .pp-test-card.pp-active { opacity: 1; pointer-events: auto; }
            .pp-metrics-card { grid-column: 1 / 4; grid-row: 3; }
            .pp-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.1); border: 1px solid #e2e8f0; }
            .pp-card h2 { color: #1a202c; font-size: 18px; margin: 0 0 4px; font-weight: 700; }
            .pp-card-sub { color: #718096; font-size: 13px; margin: 0 0 18px; }
            .pp-form { display: flex; flex-direction: column; gap: 14px; }
            .pp-form label { font-size: 11px; font-weight: 600; color: #4a5568; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
            .pp-form input, .pp-form select { padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 14px; width: 100%; box-sizing: border-box; background: #fff; }
            .pp-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
            .pp-gen-btn { padding: 12px 20px; background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; transition: all .2s; }
            .pp-gen-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,.4); }
            .pp-gen-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: none; }
            .pp-textarea { min-height: 420px; width: 100%; padding: 16px; border: 2px solid #4299e1; border-radius: 8px; font-family: 'Courier New',monospace; font-size: 13px; line-height: 1.8; resize: none; background: #fff; margin-bottom: 14px; box-sizing: border-box; }
            .pp-copy-btn { padding: 10px 18px; background: #667eea; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-transform: uppercase; letter-spacing: .5px; width: 100%; transition: all .2s; }
            .pp-copy-btn:hover { background: #5a6fd6; }
            .pp-info-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
            .pp-info-item:last-child { border-bottom: none; padding-bottom: 0; }
            .pp-info-icon { font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: #f7fafc; border-radius: 8px; flex-shrink: 0; }
            .pp-info-content { flex: 1; min-width: 0; }
            .pp-info-label { font-size: 10px; color: #718096; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; font-weight: 600; }
            .pp-info-value { font-size: 14px; color: #1a202c; font-weight: 700; word-break: break-all; }
            .pp-info-value.pp-code { font-family: 'Courier New',monospace; font-size: 13px; background: #f7fafc; padding: 4px 8px; border-radius: 4px; display: inline-block; }
            .pp-pass-row { display: flex; align-items: center; gap: 8px; }
            .pp-edit-btn { background: none; border: none; cursor: pointer; font-size: 14px; padding: 2px 4px; border-radius: 4px; transition: background .2s; }
            .pp-edit-btn:hover { background: #e2e8f0; }
            .pp-save-pass-btn { background: #48bb78; color: #fff; border: none; cursor: pointer; padding: 6px 10px; border-radius: 6px; font-size: 14px; font-weight: 700; transition: background .2s; }
            .pp-save-pass-btn:hover { background: #38a169; }
            .pp-save-pass-btn:disabled { background: #a0aec0; cursor: not-allowed; }
            .pp-cancel-pass-btn { background: #e2e8f0; color: #4a5568; border: none; cursor: pointer; padding: 6px 10px; border-radius: 6px; font-size: 14px; font-weight: 700; transition: background .2s; }
            .pp-cancel-pass-btn:hover { background: #cbd5e0; }
            .pp-metric-item { margin-bottom: 20px; width: 100%; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; }
            .pp-metric-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
            .pp-metric-label { font-size: 13px; color: #2d3748; font-weight: 600; margin-bottom: 4px; }
            .pp-metric-desc { font-size: 12px; color: #718096; margin-bottom: 8px; }
            .pp-metric-val { font-size: 15px; color: #1a202c; font-weight: 700; text-align: right; margin-bottom: 8px; }
            .pp-progress { width: 100%; height: 12px; background: #e2e8f0; border-radius: 12px; overflow: hidden; }
            .pp-progress-fill { height: 100%; border-radius: 12px; transition: width .4s ease; }
            .pp-progress-fill.pp-green { background: linear-gradient(90deg,#48bb78 0%,#38a169 100%); }
            .pp-progress-fill.pp-orange { background: linear-gradient(90deg,#ed8936 0%,#dd6b20 100%); }
            .pp-progress-fill.pp-red { background: linear-gradient(90deg,#fc5c65 0%,#e53e3e 100%); }
            .pp-progress-fill.pp-blue { background: linear-gradient(90deg,#4299e1 0%,#3182ce 100%); }
            .pp-error { background: #fff5f5; color: #c92a2a; padding: 10px; border-radius: 6px; font-size: 12px; text-align: center; margin-bottom: 10px; display: none; }
            .pp-test-btn { padding: 12px 24px; background: linear-gradient(135deg,#38b2ac 0%,#319795 100%); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: .5px; transition: all .2s; }
            .pp-test-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(56,178,172,.4); }
            .pp-test-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: none; }
            .pp-test-result { margin-top: 16px; }
            .pp-test-status { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; letter-spacing: .5px; }
            .pp-test-status.pp-online { background: #c6f6d5; color: #22543d; }
            .pp-test-status.pp-offline { background: #fed7d7; color: #742a2a; }
            .pp-test-status-dot { width: 8px; height: 8px; border-radius: 50%; }
            .pp-online .pp-test-status-dot { background: #22543d; }
            .pp-offline .pp-test-status-dot { background: #742a2a; }
            .pp-test-details { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px; }
            .pp-test-detail-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #f7fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
            .pp-test-detail-icon { font-size: 18px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 6px; flex-shrink: 0; }
            .pp-test-detail-content { flex: 1; min-width: 0; }
            .pp-test-detail-label { font-size: 10px; color: #718096; text-transform: uppercase; letter-spacing: .8px; font-weight: 600; }
            .pp-test-detail-value { font-size: 13px; color: #1a202c; font-weight: 700; word-break: break-all; }
            .pp-test-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: ppSpin .6s linear infinite; }
            @keyframes ppSpin { to { transform: rotate(360deg); } }
            @media (max-width: 1200px) {
                .pp-dashboard { grid-template-columns: 1fr; }
                .pp-gen-card, .pp-proxies-card, .pp-info-card, .pp-metrics-card, .pp-test-card { grid-column: 1; grid-row: auto; }
                .pp-test-details { grid-template-columns: 1fr; }
            }
        </style>
        @endpush

        <div class="pp-dashboard">
            {{-- Sol: Proxy Generator --}}
            <div class="pp-card pp-gen-card">
                <h2>Proxy Oluşturucu</h2>
                <p class="pp-card-sub">İhtiyacınıza göre proxy oluşturun.</p>
                <form id="ppGenerateForm" class="pp-form">
                    <div>
                        <label>Ülke</label>
                        <select id="ppCountry">
                            <option value="">🌍 Tüm Dünya</option>
                        </select>
                    </div>
                    <div>
                        <label>Eyalet / İl</label>
                        <select id="ppState" disabled>
                            <option value="">Tüm Eyaletler</option>
                        </select>
                    </div>
                    <div>
                        <label>Şehir</label>
                        <select id="ppCity" disabled>
                            <option value="">Tüm Şehirler</option>
                        </select>
                    </div>
                    <div class="pp-form-row">
                        <div>
                            <label>Proxy Protokolü</label>
                            <select id="ppProtocol">
                                <option value="http">HTTP/S</option>
                                <option value="socks5">SOCKS5</option>
                            </select>
                        </div>
                        <div>
                            <label>Proxy Tipi</label>
                            <select id="ppProxyType">
                                <option value="rotating">Rotating</option>
                                <option value="sticky">Sticky</option>
                            </select>
                        </div>
                    </div>
                    <div class="pp-form-row" id="ppStickyFields" style="display:none">
                        <div>
                            <label>Oturum Süresi</label>
                            <input type="number" id="ppSessionLifetime" value="1" min="1">
                        </div>
                        <div>
                            <label>Süre Birimi</label>
                            <select id="ppSessionTimespan">
                                <option value="minute">Dakika</option>
                                <option value="hour">Saat</option>
                                <option value="day">Gün</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label>Proxy Adedi</label>
                        <input type="number" id="ppProxyAmount" value="1" min="1" max="1000">
                    </div>
                    <div class="pp-error" id="ppErrorMsg"></div>
                    <button type="submit" class="pp-gen-btn" id="ppGenerateBtn">Oluştur</button>
                </form>
            </div>

            {{-- Orta: Generated Proxies --}}
            <div class="pp-card pp-proxies-card">
                <h2>Oluşturulan Proxyler</h2>
                <p class="pp-card-sub">Oluşturduğunuz proxyler aşağıda görünür.</p>
                <textarea id="ppGeneratedProxies" class="pp-textarea" readonly placeholder="Oluşturulan proxyler burada görünecek..."></textarea>
                <button class="pp-copy-btn" id="ppCopyBtn">Kopyala</button>
            </div>

            {{-- Sağ: Proxy Information --}}
            <div class="pp-card pp-info-card">
                <h2>Proxy Bilgileri</h2>
                <p class="pp-card-sub">Temel proxy detaylarınız.</p>
                <div class="pp-info-item">
                    <div class="pp-info-icon">☁️</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">PROXY ADRESİ</div>
                        <div class="pp-info-value" id="ppDispIp">loading...</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">🗄️</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">PROXY PORTU</div>
                        <div class="pp-info-value" id="ppDispPort">8080</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">👤</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">KULLANICI ADI</div>
                        <div class="pp-info-value pp-code" id="ppDispUser">{{ $ppUsername }}</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">🔑</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">ŞİFRE</div>
                        <div class="pp-pass-row">
                            <span class="pp-info-value pp-code" id="ppDispPass">{{ $ppPassword }}</span>
                            <button type="button" class="pp-edit-btn" id="ppEditPassBtn" title="Şifreyi Değiştir">✏️</button>
                        </div>
                        <div id="ppPassEditForm" style="display:none; margin-top:8px;">
                            <div style="display:flex; gap:6px; align-items:center;">
                                <input type="text" id="ppNewPassInput" maxlength="12" minlength="4" placeholder="Yeni şifre (4-12 karakter)" style="flex:1; padding:6px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; font-family:monospace;">
                                <button type="button" class="pp-save-pass-btn" id="ppSavePassBtn">✓</button>
                                <button type="button" class="pp-cancel-pass-btn" id="ppCancelPassBtn">✕</button>
                            </div>
                            <div id="ppPassError" style="display:none; color:#ef4444; font-size:12px; margin-top:4px;"></div>
                        </div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">📦</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">ÜRÜN TİPİ</div>
                        <div class="pp-info-value">Residential IPv4</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">📅</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">SÜRE</div>
                        <div class="pp-info-value" id="ppDuration">{{ $ppDays }} Gün</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">⏳</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">BİTİŞ TARİHİ</div>
                        <div class="pp-info-value" id="ppExpires">-</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">🌐</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">İŞ PARÇACIĞI LİMİTİ</div>
                        <div class="pp-info-value">{{ $ppThreads > 0 ? number_format($ppThreads) : '∞ Limitsiz' }}</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">💾</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">KULLANILAN BANT GENİŞLİĞİ</div>
                        <div class="pp-info-value" id="ppBandwidthUsed">0 GB</div>
                    </div>
                </div>
                <div class="pp-info-item">
                    <div class="pp-info-icon">📊</div>
                    <div class="pp-info-content">
                        <div class="pp-info-label">BANT GENİŞLİĞİ LİMİTİ</div>
                        <div class="pp-info-value" id="ppInfoBandwidthLimit">{{ $ppQuotaGb ? $ppQuotaGb . ' GB' : '∞ Limitsiz' }}</div>
                    </div>
                </div>
            </div>

            {{-- Proxy Test --}}
            <div class="pp-card pp-test-card" id="ppTestCard">
                <h2>Proxy Test Et</h2>
                <p class="pp-card-sub">Test Et butonuna basarak proxy durumunu kontrol edebilirsin.</p>
                <button type="button" class="pp-test-btn" id="ppTestBtn">Test Et</button>
                <div class="pp-test-result" id="ppTestResult" style="display:none;">
                    <div id="ppTestStatusBadge"></div>
                    <div class="pp-test-details" id="ppTestDetails"></div>
                </div>
            </div>

            {{-- Alt: Live Metrics --}}
            <div class="pp-card pp-metrics-card">
                <h2>Canlı İstatistikler</h2>
                <p class="pp-card-sub">Proxy'nizin anlık trafik bilgileri.</p>
                <div class="pp-metric-item">
                    <div class="pp-metric-label">Bant Genişliği Kullanımı</div>
                    <div class="pp-metric-desc">Toplam aktarılan veri</div>
                    <div class="pp-metric-val" id="ppMetricBandwidth">0 / {{ $ppQuotaGb ?: '∞' }} GB</div>
                    <div class="pp-progress">
                        <div class="pp-progress-fill pp-green" id="ppBandwidthBar" style="width:0%"></div>
                    </div>
                    @if(!$isAdminContext && $order->product && $order->product->findAttrsByServiceType('pproxy_quota'))
                    <div id="ppExtraQuotaBox" style="margin-top:12px;">
                        <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" id="ppExtraQuotaBtn">
                            <i class="fas fa-plus-circle me-1"></i> Ek Kota Satın Al
                        </button>
                    </div>
                    @endif
                </div>
                <div class="pp-metric-item">
                    <div class="pp-metric-label">Kalan Süre</div>
                    <div class="pp-metric-desc">Abonelik bitiş süreniz</div>
                    <div class="pp-metric-val" id="ppMetricTime">-</div>
                    <div class="pp-progress">
                        <div class="pp-progress-fill pp-blue" id="ppTimeBar" style="width:100%"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($ppActive)
    @push('js')
    <script>
    $(document).ready(function(){
        var ppUuid = @json($ppUuid);
        var ppQuotaGb = {{ $ppQuotaGb ?: 0 }};
        var ppActiveUntil = @json($ppActiveUntil);
        var ppCreatedAt = @json($ppCreatedAt);
        var ppDays = {{ $ppDays }};

        // Expires hesapla
        function ppCalcExpires(){
            if(!ppActiveUntil) return;
            var exp = new Date(ppActiveUntil);
            var now = new Date();
            var diff = exp - now;
            if(diff > 0){
                var d = Math.floor(diff/(1000*60*60*24));
                var h = Math.floor((diff%(1000*60*60*24))/(1000*60*60));
                var m = Math.floor((diff%(1000*60*60))/(1000*60));
                $('#ppExpires').text(d + ' gün, ' + h + ' saat ve ' + m + ' dakika');
                $('#ppMetricTime').text(d + ' gün ' + h + ' saat kaldı');
                if(ppDays > 0){
                    var total = ppDays * 24 * 60 * 60 * 1000;
                    var pct = Math.max(0, Math.min(100, (diff / total) * 100));
                    $('#ppTimeBar').css('width', pct + '%');
                }
            } else {
                $('#ppExpires').text('Süresi Dolmuş');
                $('#ppMetricTime').text('Süresi Dolmuş');
                $('#ppTimeBar').css('width', '0%');
            }
        }
        ppCalcExpires();

        // Ülke verileri
        var ppCountriesData = [];
        var ppCountryTR = {
            'AF':'Afganistan','AL':'Arnavutluk','DZ':'Cezayir','AO':'Angola','AR':'Arjantin',
            'AM':'Ermenistan','AU':'Avustralya','AT':'Avusturya','AZ':'Azerbaycan','BH':'Bahreyn',
            'BD':'Bangladeş','BY':'Belarus','BE':'Belçika','BO':'Bolivya','BA':'Bosna Hersek',
            'BR':'Brezilya','BN':'Brunei','BG':'Bulgaristan','BF':'Burkina Faso','KH':'Kamboçya',
            'CA':'Kanada','CL':'Şili','CN':'Çin','CO':'Kolombiya','CR':'Kosta Rika',
            'HR':'Hırvatistan','CY':'Kıbrıs','CZ':'Çekya','DK':'Danimarka','DO':'Dominik Cumhuriyeti',
            'EC':'Ekvador','EG':'Mısır','SV':'El Salvador','EE':'Estonya','ET':'Etiyopya',
            'FI':'Finlandiya','FR':'Fransa','GE':'Gürcistan','DE':'Almanya','GH':'Gana',
            'GR':'Yunanistan','GT':'Guatemala','HN':'Honduras','HK':'Hong Kong','HU':'Macaristan',
            'IS':'İzlanda','IN':'Hindistan','ID':'Endonezya','IR':'İran','IQ':'Irak',
            'IE':'İrlanda','IL':'İsrail','IT':'İtalya','JM':'Jamaika','JP':'Japonya',
            'JO':'Ürdün','KZ':'Kazakistan','KE':'Kenya','KR':'Güney Kore','KW':'Kuveyt',
            'KG':'Kırgızistan','LV':'Letonya','LB':'Lübnan','LY':'Libya','LT':'Litvanya',
            'LU':'Lüksemburg','MO':'Makao','MK':'Kuzey Makedonya','MG':'Madagaskar','MY':'Malezya',
            'MV':'Maldivler','MT':'Malta','MX':'Meksika','MD':'Moldova','MN':'Moğolistan',
            'ME':'Karadağ','MA':'Fas','MZ':'Mozambik','MM':'Myanmar','NP':'Nepal',
            'NL':'Hollanda','NZ':'Yeni Zelanda','NI':'Nikaragua','NG':'Nijerya','NO':'Norveç',
            'OM':'Umman','PK':'Pakistan','PA':'Panama','PY':'Paraguay','PE':'Peru',
            'PH':'Filipinler','PL':'Polonya','PT':'Portekiz','PR':'Porto Riko','QA':'Katar',
            'RO':'Romanya','RU':'Rusya','SA':'Suudi Arabistan','SN':'Senegal','RS':'Sırbistan',
            'SG':'Singapur','SK':'Slovakya','SI':'Slovenya','ZA':'Güney Afrika','ES':'İspanya',
            'LK':'Sri Lanka','SE':'İsveç','CH':'İsviçre','TW':'Tayvan','TJ':'Tacikistan',
            'TZ':'Tanzanya','TH':'Tayland','TN':'Tunus','TR':'Türkiye','UA':'Ukrayna',
            'AE':'Birleşik Arap Emirlikleri','GB':'İngiltere','US':'Amerika Birleşik Devletleri',
            'UY':'Uruguay','UZ':'Özbekistan','VE':'Venezuela','VN':'Vietnam','ZM':'Zambiya','ZW':'Zimbabve',
            'PS':'Filistin','SD':'Sudan','CI':'Fildişi Sahili','CU':'Küba','TT':'Trinidad ve Tobago',
            'CM':'Kamerun','CD':'Kongo Demokratik Cumhuriyeti','UG':'Uganda','AW':'Aruba','CW':'Curaçao',
            'SX':'Sint Maarten','RE':'Réunion','GP':'Guadeloupe','MQ':'Martinik','GF':'Fransız Guyanası',
            'NC':'Yeni Kaledonya','PF':'Fransız Polinezyası','BJ':'Benin','ML':'Mali','NE':'Nijer',
            'TG':'Togo','GA':'Gabon','CG':'Kongo','RW':'Ruanda','SO':'Somali','BI':'Burundi',
            'ER':'Eritre','DJ':'Cibuti','MW':'Malavi','LS':'Lesotho','SZ':'Esvati̇ni̇','BW':'Botsvana',
            'NA':'Namibya','SC':'Seyşeller','MU':'Mauritius','CV':'Yeşil Burun Adaları','GM':'Gambiya',
            'GW':'Gine-Bissau','SL':'Sierra Leone','LR':'Liberya','CF':'Orta Afrika Cumhuriyeti',
            'TD':'Çad','GN':'Gine','GQ':'Ekvator Ginesi','ST':'São Tomé ve Príncipe',
            'KM':'Komorlar','YT':'Mayotte','KP':'Kuzey Kore','LA':'Laos','BT':'Butan',
            'TL':'Doğu Timor','FJ':'Fiji','PG':'Papua Yeni Gine','WS':'Samoa','TO':'Tonga',
            'SB':'Solomon Adaları','VU':'Vanuatu','KI':'Kiribati','MH':'Marshall Adaları',
            'FM':'Mikronezya','PW':'Palau','NR':'Nauru','TV':'Tuvalu','CK':'Cook Adaları',
            'NU':'Niue','TK':'Tokelau','AS':'Amerikan Samoası','GU':'Guam','MP':'Kuzey Mariana Adaları',
            'VI':'ABD Virjin Adaları','AG':'Antigua ve Barbuda','BB':'Barbados','DM':'Dominika',
            'GD':'Grenada','HT':'Haiti','KN':'Saint Kitts ve Nevis','LC':'Saint Lucia',
            'VC':'Saint Vincent ve Grenadinler','BS':'Bahamalar','BZ':'Belize','GY':'Guyana',
            'SR':'Surinam','BM':'Bermuda','KY':'Cayman Adaları','TC':'Turks ve Caicos Adaları',
            'VG':'İngiliz Virjin Adaları','AI':'Anguilla','MS':'Montserrat','YE':'Yemen',
            'SY':'Suriye','AF':'Afganistan','LI':'Lihtenştayn','MC':'Monako','SM':'San Marino',
            'AD':'Andorra','VA':'Vatikan','MT':'Malta','GL':'Grönland','FO':'Faroe Adaları',
            'GI':'Cebelitarık','JE':'Jersey','IM':'Man Adası','GG':'Guernsey'
        };
        function ppTrCountry(name, iso2){
            if(iso2 && ppCountryTR[iso2]) return ppCountryTR[iso2];
            return name || iso2 || '';
        }
        function ppLoadCountries(){
            $.ajax({
                url: '{{ route("portal.orders.pproxy.getCountries") }}',
                type: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.success && res.data){
                        ppCountriesData = Array.isArray(res.data) ? res.data : Object.values(res.data);
                        ppCountriesData.sort(function(a,b){
                            var na = ppTrCountry(a.name, a.iso2||a.code);
                            var nb = ppTrCountry(b.name, b.iso2||b.code);
                            return na.localeCompare(nb, 'tr');
                        });
                        var sel = $('#ppCountry');
                        var trItem = ppCountriesData.find(function(c){ return (c.iso2||c.code) === 'TR'; });
                        if(trItem){
                            sel.append('<option value="TR">Türkiye</option>');
                        }
                        ppCountriesData.forEach(function(c){
                            var iso = c.iso2||c.code||'';
                            if(iso === 'TR') return;
                            sel.append('<option value="' + iso + '">' + ppTrCountry(c.name, iso) + '</option>');
                        });
                    }
                }
            });
        }
        ppLoadCountries();

        var ppServerDomain = 'tr.saglamproxy.com';
        $.ajax({
            url: '{{ route("portal.orders.pproxy.getServerDomain", ["order" => $order->id]) }}',
            type: 'GET',
            dataType: 'json',
            success: function(res){
                if(res.success && res.domain){
                    ppServerDomain = res.domain;
                }
                $('#ppDispIp').text(ppServerDomain);
            },
            error: function(){
                $('#ppDispIp').text(ppServerDomain);
            }
        });

        $('#ppCountry').on('change', function(){
            var iso2 = $(this).val();
            var stateSel = $('#ppState');
            var citySel = $('#ppCity');
            stateSel.html('<option value="">Tüm Eyaletler</option>').prop('disabled', true);
            citySel.html('<option value="">Tüm Şehirler</option>').prop('disabled', true);
            if(!iso2) return;
            var country = ppCountriesData.find(function(c){ return (c.iso2||c.code) === iso2; });
            if(!country) return;
            if(country.states && typeof country.states === 'object'){
                var stKeys = Object.keys(country.states);
                if(stKeys.length > 0){
                    stKeys.forEach(function(k){
                        var st = country.states[k];
                        stateSel.append('<option value="' + k + '">' + (st.name||k) + '</option>');
                    });
                    stateSel.prop('disabled', false);
                }
            }
            if(country.cities && country.cities.length > 0){
                country.cities.forEach(function(c){
                    citySel.append('<option value="' + (c.code||c.ascii||'') + '">' + (c.name||c.code||'') + '</option>');
                });
                citySel.prop('disabled', false);
            }
        });

        $('#ppState').on('change', function(){
            var stateCode = $(this).val();
            var iso2 = $('#ppCountry').val();
            var citySel = $('#ppCity');
            citySel.html('<option value="">Tüm Şehirler</option>');
            if(!iso2) return;
            var country = ppCountriesData.find(function(c){ return (c.iso2||c.code) === iso2; });
            if(!country || !country.cities) return;
            var filtered = country.cities;
            if(stateCode){
                filtered = country.cities.filter(function(c){ return String(c.state) === String(stateCode); });
            }
            filtered.forEach(function(c){
                citySel.append('<option value="' + (c.code||c.ascii||'') + '">' + (c.name||c.code||'') + '</option>');
            });
            citySel.prop('disabled', filtered.length === 0);
        });

        // Traffic bilgisi çek
        function ppLoadTraffic(){
            $.ajax({
                url: '{{ route("portal.orders.pproxy.getTrafficInfo", ["order" => $order->id]) }}',
                type: 'GET',
                dataType: 'json',
                success: function(res){
                    if(res.success && res.data){
                        var d = res.data;
                        var trafficBytes = d.traffic_used || 0;
                        var totalBytes = d.bandwidth || 0;
                        var usedGb = trafficBytes / (1024*1024*1024);
                        var apiQuotaGb = totalBytes / 1000000000;

                        if(apiQuotaGb > 0) ppQuotaGb = Math.round(apiQuotaGb * 100) / 100;

                        var quotaDisplay = ppQuotaGb > 0 ? ppQuotaGb : '∞';

                        $('#ppBandwidthUsed').text(usedGb.toFixed(2) + ' GB');
                        $('#ppInfoBandwidthLimit').text(ppQuotaGb > 0 ? ppQuotaGb + ' GB' : '∞ Limitsiz');

                        if(ppQuotaGb > 0){
                            var pct = Math.min(100, (usedGb / ppQuotaGb) * 100);
                            $('#ppMetricBandwidth').text(usedGb.toFixed(2) + ' / ' + quotaDisplay + ' GB');
                            $('#ppBandwidthBar').css('width', pct + '%');
                            $('#ppBandwidthBar').removeClass('pp-green pp-orange pp-red');
                            if(pct >= 90){
                                $('#ppBandwidthBar').addClass('pp-red');
                            } else if(pct >= 75){
                                $('#ppBandwidthBar').addClass('pp-orange');
                            } else {
                                $('#ppBandwidthBar').addClass('pp-green');
                            }
                        } else {
                            $('#ppMetricBandwidth').text(usedGb.toFixed(2) + ' GB / Limitsiz');
                        }
                        var au = d.active_until;
                        if(au){
                            ppActiveUntil = au;
                            ppCalcExpires();
                        }
                    }
                }
            });
        }
        ppLoadTraffic();
        setInterval(ppLoadTraffic, 60000);

        @if(!$isAdminContext && $order->product && $order->product->findAttrsByServiceType('pproxy_quota'))
        @php
            $ppQuotaAttr = $order->product->findAttrsByServiceType('pproxy_quota');
            $ppQuotaOptions = $ppQuotaAttr['options'] ?? [];
        @endphp
        $('#ppExtraQuotaBtn').on('click', function(){
            var quotaOptions = @json($ppQuotaOptions);
            var optionsHtml = '<div style="text-align:left;">';
            quotaOptions.forEach(function(opt, idx){
                var checked = idx === 0 ? 'checked' : '';
                optionsHtml += '<label style="display:flex;align-items:center;padding:10px 0;border-bottom:1px solid #eee;cursor:pointer;">' +
                    '<input type="radio" name="ppQuotaChoice" value="' + opt.value + '" ' + checked + ' style="margin-right:10px;width:18px;height:18px;">' +
                    '<span style="flex:1;font-weight:600;">' + opt.label + '</span>' +
                    '<span style="color:#e53e3e;font-weight:700;">' + parseFloat(opt.price).toFixed(2) + ' ₺</span>' +
                '</label>';
            });
            optionsHtml += '</div>';

            Swal.fire({
                title: 'Ek Kota Satın Al',
                html: '<p style="margin-bottom:12px;color:#666;">Seçeceğiniz kota kadar faturanız oluşturulacaktır.</p>' + optionsHtml,
                confirmButtonText: '<i class="fas fa-file-invoice me-1"></i> Fatura Oluştur',
                cancelButtonText: 'İptal',
                showCancelButton: true,
                confirmButtonColor: '#f6993f',
                preConfirm: function(){
                    var sel = $('input[name="ppQuotaChoice"]:checked').val();
                    if(!sel){
                        Swal.showValidationMessage('Lütfen bir kota paketi seçin');
                        return false;
                    }
                    return sel;
                }
            }).then(function(result){
                if(result.isConfirmed && result.value){
                    Swal.fire({title:'Fatura oluşturuluyor...',allowOutsideClick:false,didOpen:function(){Swal.showLoading();}});
                    $.ajax({
                        type: 'POST',
                        url: '{{ route("portal.orders.addPProxyQuotaPost", ["order" => $order->id]) }}',
                        dataType: 'json',
                        data: {
                            _token: '{{ csrf_token() }}',
                            quota: result.value
                        },
                        success: function(res){
                            if(res.success){
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Fatura Oluşturuldu',
                                    text: res.message || 'Ödeme sayfasına yönlendiriliyorsunuz...',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                if(res.redirectUrl){
                                    setTimeout(function(){ window.location.href = res.redirectUrl; }, 2000);
                                }
                            } else {
                                Swal.fire({icon:'error',title:'Hata',text: res.message || 'Bir hata oluştu.'});
                            }
                        },
                        error: function(xhr){
                            var msg = 'Bir hata oluştu.';
                            if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            Swal.fire({icon:'error',title:'Hata',text: msg});
                        }
                    });
                }
            });
        });
        @endif

        // Proxy Type değişimi
        $('#ppProxyType').on('change', function(){
            $('#ppStickyFields').toggle(this.value === 'sticky');
        });

        $('#ppProtocol').on('change', function(){
            $('#ppDispPort').text(this.value === 'socks5' ? '1080' : '8080');
        });

        // Generate
        $('#ppGenerateForm').on('submit', function(e){
            e.preventDefault();
            var btn = $('#ppGenerateBtn');
            var errDiv = $('#ppErrorMsg');
            btn.prop('disabled', true).text('Oluşturuluyor...');
            errDiv.hide();

            var rotation = $('#ppProxyType').val();
            var lifetime = parseInt($('#ppSessionLifetime').val()) || 1;
            if(rotation === 'sticky'){
                var ts = $('#ppSessionTimespan').val();
                if(ts === 'minute') lifetime *= 60;
                else if(ts === 'hour') lifetime *= 3600;
                else if(ts === 'day') lifetime *= 86400;
            }

            $.ajax({
                url: '{{ route("portal.orders.pproxy.generateProxies", ["order" => $order->id]) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    country: $('#ppCountry').val(),
                    state: $('#ppState').val(),
                    city: $('#ppCity').val(),
                    rotation: rotation,
                    proxy_amount: parseInt($('#ppProxyAmount').val()) || 1,
                    format: '{ip}:{port}:{user}:{pass}',
                    lifetime: lifetime,
                    protocol: $('#ppProtocol').val()
                },
                dataType: 'json',
                success: function(res){
                    btn.prop('disabled', false).text('Oluştur');
                    if(res.success && res.data){
                        var txt = '';
                        if(Array.isArray(res.data)) txt = res.data.join('\n');
                        else if(typeof res.data === 'string') txt = res.data;
                        else if(res.data.proxies) txt = Array.isArray(res.data.proxies) ? res.data.proxies.join('\n') : res.data.proxies;
                        $('#ppGeneratedProxies').val(txt);
                        $('#ppTestCard').addClass('pp-active');
                        $('#ppTestResult').hide();
                    } else {
                        errDiv.text(res.message || 'Proxy oluşturulamadı').show();
                    }
                },
                error: function(){
                    btn.prop('disabled', false).text('Oluştur');
                    errDiv.text('Bir hata oluştu').show();
                }
            });
        });

        // Password edit
        $('#ppEditPassBtn').on('click', function(){
            $('#ppPassEditForm').show();
            $('#ppNewPassInput').val('').focus();
            $(this).hide();
        });
        $('#ppCancelPassBtn').on('click', function(){
            $('#ppPassEditForm').hide();
            $('#ppPassError').hide();
            $('#ppEditPassBtn').show();
        });
        $('#ppSavePassBtn').on('click', function(){
            var newPass = $('#ppNewPassInput').val().trim();
            var errDiv = $('#ppPassError');
            errDiv.hide();
            if(!newPass || newPass.length < 4 || newPass.length > 12){
                errDiv.text('Şifre 4-12 karakter arasında olmalıdır.').show();
                return;
            }
            if(!/^[a-zA-Z0-9]+$/.test(newPass)){
                errDiv.text('Şifre sadece harf ve rakam içerebilir.').show();
                return;
            }
            var btn = $(this);
            btn.prop('disabled', true).text('...');
            $.ajax({
                url: '{{ route("portal.orders.pproxy.changePassword", ["order" => $order->id]) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', new_password: newPass },
                dataType: 'json',
                success: function(res){
                    btn.prop('disabled', false).text('✓');
                    if(res.success){
                        $('#ppDispPass').text(res.password);
                        $('#ppPassEditForm').hide();
                        $('#ppEditPassBtn').show();
                        toastr.success(res.message || 'Şifre değiştirildi!');
                    } else {
                        errDiv.text(res.message || 'Hata oluştu.').show();
                    }
                },
                error: function(){
                    btn.prop('disabled', false).text('✓');
                    errDiv.text('Bir hata oluştu.').show();
                }
            });
        });

        // Copy
        $('#ppCopyBtn').on('click', function(){
            var txt = $('#ppGeneratedProxies').val();
            if(txt){
                navigator.clipboard.writeText(txt);
                toastr.success('Proxyler kopyalandı!');
            }
        });

        // Proxy Test
        $('#ppTestBtn').on('click', function(){
            var btn = $(this);
            var protocol = $('#ppProtocol').val() || 'http';
            btn.prop('disabled', true).html('<span class="pp-test-spinner"></span> Test ediliyor...');
            $('#ppTestResult').hide();

            var proxyLine = '';
            var txt = $('#ppGeneratedProxies').val().trim();
            if(txt){
                proxyLine = txt.split('\n')[0].trim();
            }

            $.ajax({
                url: '{{ route("portal.orders.pproxy.testProxy", ["order" => $order->id]) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', protocol: protocol, proxy_line: proxyLine },
                dataType: 'json',
                success: function(res){
                    btn.prop('disabled', false).text('Test Et');
                    if(!res.success){
                        toastr.error(res.message || 'Test başarısız');
                        return;
                    }
                    var resultDiv = $('#ppTestResult');
                    var statusDiv = $('#ppTestStatusBadge');
                    var detailsDiv = $('#ppTestDetails');

                    if(res.online){
                        statusDiv.html('<span class="pp-test-status pp-online"><span class="pp-test-status-dot"></span> ONLINE</span>');
                        detailsDiv.html(
                            '<div class="pp-test-detail-item"><div class="pp-test-detail-icon">⚡</div><div class="pp-test-detail-content"><div class="pp-test-detail-label">Yanıt</div><div class="pp-test-detail-value">' + res.latency + ' ms</div></div></div>' +
                            '<div class="pp-test-detail-item"><div class="pp-test-detail-icon">🔒</div><div class="pp-test-detail-content"><div class="pp-test-detail-label">Protokol</div><div class="pp-test-detail-value">' + res.protocol + '</div></div></div>' +
                            '<div class="pp-test-detail-item"><div class="pp-test-detail-icon">📍</div><div class="pp-test-detail-content"><div class="pp-test-detail-label">Lokasyon</div><div class="pp-test-detail-value">' + res.location + '</div></div></div>' +
                            '<div class="pp-test-detail-item"><div class="pp-test-detail-icon">🌐</div><div class="pp-test-detail-content"><div class="pp-test-detail-label">IP</div><div class="pp-test-detail-value">' + res.ip + '</div></div></div>' +
                            '<div class="pp-test-detail-item"><div class="pp-test-detail-icon">🏢</div><div class="pp-test-detail-content"><div class="pp-test-detail-label">ISP</div><div class="pp-test-detail-value">' + res.isp + '</div></div></div>'
                        );
                    } else {
                        statusDiv.html('<span class="pp-test-status pp-offline"><span class="pp-test-status-dot"></span> OFFLINE</span>');
                        detailsDiv.html(
                            '<div class="pp-test-detail-item" style="grid-column:1/3"><div class="pp-test-detail-icon">⚠️</div><div class="pp-test-detail-content"><div class="pp-test-detail-label">Hata</div><div class="pp-test-detail-value">' + (res.message || 'Bağlantı kurulamadı') + '</div></div></div>'
                        );
                    }
                    resultDiv.show();
                },
                error: function(){
                    btn.prop('disabled', false).text('Test Et');
                    toastr.error('Bir hata oluştu');
                }
            });
        });
    });
    </script>
    @endpush
    @endif
@endif

@if($order->isLocaltonetLikeDelivery() && !$order->isCanDeliveryType('LOCALTONETV4'))
@push("js")
    <script>
        $(document).ready(function () {
            var currentAjaxRequest = null;
            $(document).on('shown.bs.tab', 'a[href="#localtonet_ip_history_tab"]', function () {
                if (currentAjaxRequest) currentAjaxRequest.abort();
                currentAjaxRequest = $.ajax({
                    type: "POST",
                    url: "{{ route($ltPrefix.'getIpHistory', ['order' => $order]) }}",
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    beforeSend: function () {
                        $("[data-np-ip-history='area']").html($("[data-np-ip-history='table-template']").html());
                    },
                    complete: function (xhr, textStatus) {
                        var area = $("[data-np-ip-history='area']");
                        var res = xhr.responseJSON;
                        if (textStatus === 'abort') {
                            return;
                        }
                        if (textStatus === 'error' || !res) {
                            area.find("[data-np-ip-history='loader']").remove();
                            area.find("[data-np-ip-history='items']").append(
                                '<tr><td colspan="2" class="text-center text-danger">IP geçmişi yüklenemedi. SSL/API ayarlarını kontrol edin.</td></tr>'
                            );
                            return;
                        }
                        if (res.success === true) {
                            area.find("[data-np-ip-history='loader']").remove();
                            var rows = Array.isArray(res.data) ? res.data : [];
                            var $table = area.find("[data-np-ip-history='table']");
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable($table)) {
                                $table.DataTable().clear().destroy();
                            }
                            if (rows.length > 0) {
                                rows.forEach(function (item) {
                                    var ip = (item && item.ip != null) ? String(item.ip) : '';
                                    var dt = (item && item.date != null) ? String(item.date) : '';
                                    area.find("[data-np-ip-history='items']").append('<tr><td>' + ip + '</td><td>' + dt + '</td></tr>');
                                });
                                $table.DataTable({
                                    paging: true,
                                    pageLength: 5,
                                    lengthMenu: [5, 10, 25, 50, 75, 100],
                                    displayLength: 5,
                                    searching: false,
                                    info: false,
                                    ordering: false
                                });
                            } else {
                                area.find("[data-np-ip-history='items']").append(
                                    '<tr><td colspan="2" class="text-center text-muted">Sipariş tarihinden sonra kayıtlı IP değişikliği bulunmuyor.</td></tr>'
                                );
                            }
                        } else {
                            area.find("[data-np-ip-history='loader']").remove();
                            area.find("[data-np-ip-history='items']").append(
                                '<tr><td colspan="2" class="text-center text-danger">' + (res.message || 'Liste yüklenemedi.') + '</td></tr>'
                            );
                        }
                    }
                });
            });
        })
    </script>
@endpush
@endif
@if($isAdminContext && $order->isLocaltonetLikeDelivery() && !$order->isCanDeliveryType('LOCALTONETV4'))
@push("js")
    <script>
        $(function () {
            var npLtSetServerPort = @json(route($ltPrefix.'setServerPort', ['order' => $order->id]));
            var npLtSetAutoAirplane = @json(route($ltPrefix.'setAutoAirplaneModeSetting', ['order' => $order->id]));
            var npAdminAdjustUrl = @json(route($ltPrefix.'adminAdjustQuotaDuration', ['order' => $order->id]));

            $(document).on("submit", "#npAdminLocaltonetAdjustForm", function (e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find("button[type='submit']");
                var addGb = form.find("[name='add_gb']").val();
                var addDays = form.find("[name='add_days']").val();
                if ((!addGb || parseFloat(addGb) <= 0) && (!addDays || parseInt(addDays, 10) <= 0)) {
                    Swal.fire({
                        title: "{{__('warning')}}",
                        text: "En az ek kota (GB) veya ek süre (gün) girin.",
                        icon: "warning",
                        confirmButtonText: "{{__('close')}}"
                    });
                    return;
                }
                $.ajax({
                    type: "POST",
                    url: npAdminAdjustUrl,
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}",
                        add_gb: addGb || "",
                        add_days: addDays || ""
                    },
                    beforeSend: function () {
                        propSubmitButton(btn, 1);
                    },
                    complete: function (data) {
                        propSubmitButton(btn, 0);
                        var res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then(function () { window.location.reload(); });
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            });
                        }
                    }
                });
            });

            $(document).on("change", "#localtonet_authorization_tab [name='is_active']", function () {
                var element = $(this), area = $("#localtonet_authorization_tab");
                if (element.is(":checked")) {
                    area.find(".whitelistArea").hide();
                    area.find(".userNamePassArea").fadeIn();
                } else {
                    area.find(".userNamePassArea").hide();
                    area.find(".whitelistArea").fadeIn();
                }
            });
            $("#localtonet_authorization_tab [name='is_active']").trigger("change");

            $(document).on("submit", "#authorizationForm", function (e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    type: 'POST',
                    url: form.attr("action"),
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                        if (typeof alerts !== 'undefined' && alerts.wait) alerts.wait.fire();
                    },
                    complete: function (data) {
                        var res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then(function () { window.location.reload(); });
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            });
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                });
            });

            $(document).on("click", ".proxyChangeStatusBtn", function () {
                var btn = $(this), alertText = btn.data("alert-text"), url = btn.data("action");
                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: alertText,
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then(function (result) {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: { _token: "{{csrf_token()}}" },
                            beforeSend: function () { propSubmitButton(btn, 1); },
                            complete: function (data) {
                                propSubmitButton(btn, 0);
                                var res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then(function () { window.location.reload(); });
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "{{__('form_has_errors')}}",
                                        icon: "error",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("click", ".changePortBtn", function () {
                var btn = $(this);
                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: "Portu düzenlemek istediğinize emin misiniz?",
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then(function (result) {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: npLtSetServerPort,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                server_port: $("[name='server_port']").val() ?? null
                            },
                            beforeSend: function () { propSubmitButton(btn, 1); },
                            complete: function (data) {
                                propSubmitButton(btn, 0);
                                var res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then(function () { window.location.reload(); });
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "{{__('form_has_errors')}}",
                                        icon: "error",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("click", ".changeAirplaneModeBtn", function () {
                if ($(this).hasClass("active")) {
                    if (typeof alerts !== 'undefined' && alerts.confirm) {
                        alerts.confirm.fire({
                            title: "{{__('warning')}}",
                            text: "Otomatik IP yenilemeyi durdurmak istediğinize emin misiniz?",
                            confirmButtonText: "{{__('yes')}}, durdur",
                            cancelButtonText: "{{__('cancel')}}"
                        }).then(function (r) {
                            if (r.isConfirmed === true) {
                                $.ajax({
                                    type: "POST",
                                    url: npLtSetAutoAirplane,
                                    dataType: "json",
                                    data: { _token: "{{csrf_token()}}", stop: true },
                                    complete: function (data) {
                                        var res = data.responseJSON;
                                        if (res && res.success === true) {
                                            Swal.fire({
                                                title: "{{__('success')}}",
                                                text: res?.message ?? "",
                                                icon: "success",
                                                showConfirmButton: 0,
                                                showCancelButton: 1,
                                                cancelButtonText: "{{__('close')}}"
                                            }).then(function () { window.location.reload(); });
                                        } else if (typeof alerts !== 'undefined' && alerts.error) {
                                            alerts.error.fire({ text: res?.message ?? "" });
                                        }
                                    }
                                });
                            }
                        });
                    }
                } else {
                    $("#changeAirplaneModeModal").modal("show");
                }
            });

            $(document).on("submit", "#changeAirplaneModeForm", function (e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    type: 'POST',
                    url: form.attr("action"),
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                    },
                    complete: function (data) {
                        var res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then(function () { window.location.reload(); });
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            });
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                });
            });

            $(document).on("click", ".generateUserNamePassBtn", function () {
                var area = $("#localtonet_authorization_tab");
                function generateRandomString(length) {
                    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    var result = '';
                    for (var i = 0; i < length; i++) {
                        result += characters.charAt(Math.floor(Math.random() * characters.length));
                    }
                    return result;
                }
                area.find("[name='user_name']").val(generateRandomString(6));
                area.find("[name='password']").val(generateRandomString(6));
            });

            $(document).on("click", ".ipChangeBtn, .deviceRestartBtn", function () {
                var btn = $(this), ajaxUrl = btn.data("ajax-url"), swalText = btn.data("swal-text");
                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: swalText,
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then(function (result) {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: ajaxUrl,
                            dataType: "json",
                            data: { _token: "{{csrf_token()}}" },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                if (typeof alerts !== 'undefined' && alerts.wait) alerts.wait.fire();
                            },
                            complete: function (data) {
                                propSubmitButton(btn, 0);
                                var res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then(function () { window.location.reload(); });
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "{{__('form_has_errors')}}",
                                        icon: "error",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    });
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
@endif

@if($order->isLocaltonetLikeDelivery() && $order->isCanDeliveryType('LOCALTONETV4'))
@push('js')
    <script>
        $(function () {
            var v4ConnectivityUrl = @json($v4ConnectivityUrl);
            var v4ToggleProtocolUrl = @json($v4ToggleProtocolUrl);
            var v4Csrf = @json(csrf_token());
            function npLtv4Copy(t) {
                if (!t) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(t).then(function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Kopyalandı', showConfirmButton: false, timer: 1500 });
                        }
                    });
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = t;
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); } catch (e) {}
                    document.body.removeChild(ta);
                }
            }
            function npLtv4Term(idx, line) {
                var el = document.querySelector('[data-np-ltv4-terminal="' + idx + '"]');
                if (!el) return;
                var ts = new Date().toLocaleTimeString();
                var cur = el.textContent.trim();
                if (cur.indexOf('burada görüntülenecek') !== -1 && cur.length < 90) {
                    el.textContent = '';
                    cur = '';
                }
                el.textContent += (cur ? '\n' : '') + '[' + ts + '] ' + line;
                el.scrollTop = el.scrollHeight;
            }
            $(document).on('click', '[data-np-ltv4-copy-bulk]', function () {
                npLtv4Copy($('#np_ltv4_bulk').val() || '');
            });
            $(document).on('click', '[data-np-ltv4-toggle-protocol]', function () {
                var btn = $(this);
                if (btn.prop('disabled')) return;
                btn.prop('disabled', true);
                var tid = btn.attr('data-np-ltv4-tunnel-id');
                var payload = { _token: v4Csrf };
                if (btn.attr('data-np-ltv4-toggle-protocol-bulk')) {
                    payload.bulk = '1';
                } else if (tid) {
                    payload.tunnel_id = tid;
                }
                $.ajax({
                    type: 'POST',
                    url: v4ToggleProtocolUrl,
                    dataType: 'json',
                    data: payload,
                    complete: function (xhr) {
                        btn.prop('disabled', false);
                        var res = xhr.responseJSON;
                        if (res && res.success === true) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: "{{ __('success') }}",
                                    text: res.message || 'Protokol güncellendi.',
                                    icon: 'success',
                                    showConfirmButton: true,
                                    confirmButtonText: "{{ __('close') }}"
                                }).then(function () { window.location.reload(); });
                            } else {
                                window.location.reload();
                            }
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: "{{ __('error') }}",
                                text: res && res.message ? res.message : 'İşlem başarısız',
                                icon: 'error',
                                confirmButtonText: "{{ __('close') }}"
                            });
                        }
                    }
                });
            });
            $(document).on('click', '[data-np-ltv4-copy]', function () {
                npLtv4Copy($(this).attr('data-np-ltv4-copy'));
            });
            function npLtv4RunConnectivity(idx, action, tunnelId) {
                npLtv4Term(idx, 'Sunucu üzerinden test çalışıyor…');
                var pdata = { _token: v4Csrf, action: action };
                if (tunnelId) {
                    pdata.tunnel_id = tunnelId;
                }
                $.ajax({
                    type: 'POST',
                    url: v4ConnectivityUrl,
                    dataType: 'json',
                    data: pdata,
                    complete: function (xhr) {
                        var res = xhr.responseJSON;
                        if (!res || res.success !== true) {
                            npLtv4Term(idx, 'Hata: ' + (res && res.message ? res.message : 'İstek başarısız'));
                            return;
                        }
                        var line = res.line || res.message || 'Tamamlandı.';
                        var prefix = (res.ok === false) ? 'UYARI: ' : '';
                        npLtv4Term(idx, prefix + line);
                    }
                });
            }
            $(document).on('click', '[data-np-ltv4-proxy-test]', function () {
                var idx = String($(this).data('np-ltv4-proxy-test'));
                var tid = $(this).attr('data-np-ltv4-tunnel-id') || '';
                npLtv4RunConnectivity(idx, 'proxy', tid);
            });
            $(document).on('click', '[data-np-ltv4-ping-test]', function () {
                var idx = String($(this).data('np-ltv4-ping-test'));
                var tid = $(this).attr('data-np-ltv4-tunnel-id') || '';
                npLtv4RunConnectivity(idx, 'ping', tid);
            });

            $(document).on('change', '[data-np-ltv4-auth] [name="is_active"]', function () {
                var area = $(this).closest('[data-np-ltv4-auth]');
                if ($(this).is(':checked')) {
                    area.find('.whitelistArea').hide();
                    area.find('.userNamePassArea').fadeIn();
                } else {
                    area.find('.userNamePassArea').hide();
                    area.find('.whitelistArea').fadeIn();
                }
            });
            $('[data-np-ltv4-auth] [name="is_active"]').trigger('change');

            $(document).on('click', '[data-np-ltv4-auth] .generateUserNamePassBtn', function () {
                var area = $(this).closest('[data-np-ltv4-auth]');
                function rnd(len) {
                    var c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', s = '';
                    for (var i = 0; i < len; i++) s += c.charAt(Math.floor(Math.random() * c.length));
                    return s;
                }
                area.find('[name="user_name"]').val(rnd(6));
                area.find('[name="password"]').val(rnd(6));
            });

            $(document).on('submit', '.np-localtonet-v4-root #authorizationForm', function (e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                        if (typeof alerts !== 'undefined' && alerts.wait) alerts.wait.fire();
                    },
                    complete: function (data) {
                        var res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{ __('success') }}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{ __('close') }}"
                            }).then(function () { window.location.reload(); });
                        } else {
                            Swal.fire({
                                title: "{{ __('error') }}",
                                text: res?.message ? res.message : "{{ __('form_has_errors') }}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{ __('close') }}",
                            });
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                });
            });

            @if($isAdminContext)
            var npLtSetServerPortV4 = @json(route($ltPrefix.'setServerPort', ['order' => $order->id]));
            var npAdminAdjustUrlV4 = @json(route($ltPrefix.'adminAdjustQuotaDuration', ['order' => $order->id]));

            $(document).on('submit', '.np-localtonet-v4-root #npAdminLocaltonetAdjustForm', function (e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find("button[type='submit']");
                var addGb = form.find('[name="add_gb"]').val();
                var addDays = form.find('[name="add_days"]').val();
                if ((!addGb || parseFloat(addGb) <= 0) && (!addDays || parseInt(addDays, 10) <= 0)) {
                    Swal.fire({
                        title: "{{ __('warning') }}",
                        text: 'En az ek kota (GB) veya ek süre (gün) girin.',
                        icon: 'warning',
                        confirmButtonText: "{{ __('close') }}"
                    });
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: npAdminAdjustUrlV4,
                    dataType: 'json',
                    data: { _token: "{{ csrf_token() }}", add_gb: addGb || '', add_days: addDays || '' },
                    beforeSend: function () { propSubmitButton(btn, 1); },
                    complete: function (data) {
                        propSubmitButton(btn, 0);
                        var res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{ __('success') }}",
                                text: res?.message ?? '',
                                icon: 'success',
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{ __('close') }}"
                            }).then(function () { window.location.reload(); });
                        } else {
                            Swal.fire({
                                title: "{{ __('error') }}",
                                text: res?.message ?? "{{ __('form_has_errors') }}",
                                icon: 'error',
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{ __('close') }}"
                            });
                        }
                    }
                });
            });

            $(document).on('click', '.np-localtonet-v4-root .proxyChangeStatusBtn', function () {
                var btn = $(this), alertText = btn.data('alert-text'), url = btn.data('action');
                Swal.fire({
                    icon: 'warning',
                    title: "{{ __('warning') }}",
                    text: alertText,
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{ __('close') }}",
                    confirmButtonText: "{{ __('yes') }}",
                }).then(function (result) {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: 'POST',
                            url: url,
                            dataType: 'json',
                            data: { _token: "{{ csrf_token() }}" },
                            beforeSend: function () { propSubmitButton(btn, 1); },
                            complete: function (data) {
                                propSubmitButton(btn, 0);
                                var res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{ __('success') }}",
                                        text: res?.message ?? '',
                                        icon: 'success',
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{ __('close') }}"
                                    }).then(function () { window.location.reload(); });
                                } else {
                                    Swal.fire({
                                        title: "{{ __('error') }}",
                                        text: res?.message ?? "{{ __('form_has_errors') }}",
                                        icon: 'error',
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{ __('close') }}"
                                    });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.np-localtonet-v4-root .changePortBtn', function () {
                var btn = $(this);
                Swal.fire({
                    icon: 'warning',
                    title: "{{ __('warning') }}",
                    text: 'Portu düzenlemek istediğinize emin misiniz?',
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{ __('close') }}",
                    confirmButtonText: "{{ __('yes') }}",
                }).then(function (result) {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: 'POST',
                            url: npLtSetServerPortV4,
                            dataType: 'json',
                            data: {
                                _token: "{{ csrf_token() }}",
                                server_port: $('.np-localtonet-v4-root [name="server_port"]').val() ?? null
                            },
                            beforeSend: function () { propSubmitButton(btn, 1); },
                            complete: function (data) {
                                propSubmitButton(btn, 0);
                                var res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{ __('success') }}",
                                        text: res?.message ?? '',
                                        icon: 'success',
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{ __('close') }}"
                                    }).then(function () { window.location.reload(); });
                                } else {
                                    Swal.fire({
                                        title: "{{ __('error') }}",
                                        text: res?.message ?? "{{ __('form_has_errors') }}",
                                        icon: 'error',
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{ __('close') }}"
                                    });
                                }
                            }
                        });
                    }
                });
            });
            @endif
        });
    </script>
@endpush
@endif
