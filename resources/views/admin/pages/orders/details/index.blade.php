@extends("admin.template")
@section("title", 'Sipariş Detayları')
@section("css") @endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="[__('products') => route('admin.products.index'), 'Sipariş Detayları']"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <div class="row g-10">
        <div class="col-xl-3">
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("status")}}</h2>
                    </div>
                    <!--end::Card title-->
                    <!--begin::Card toolbar-->
                    <div class="card-toolbar">
                        <div class="me-3">
                            {!! $order->drawStatus() !!}
                        </div>
                    </div>
                    <!--begin::Card toolbar-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="separator border-1 border-gray mt-3"></div>
                    <div class="row mt-5 gap-5">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{__("customer")}}:</span>
                            <span>
                                @if($order->user)
                                    <a class="badge badge-light text-hover-primary"
                                       href="{{route('admin.users.show',['user' => $order->user_id])}}">{{$order?->user->full_name}}</a>
                                @endif
                            </span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Sipariş Tarihi:</span>
                            <span class="badge badge-sm badge-secondary">{{$order->created_at?->format(defaultDateTimeFormat()) ?? '-'}}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{__("service_group")}}:</span>
                            <span class="fw-semibold text-end">{{@$order->product_data["category"]["name"]}}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-nowrap me-3">{{__("service_name")}}:</span>
                            <span class="fw-semibold text-end">{{@$order->product_data["name"]}}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{__("payment_period")}}:</span>
                            <span class="fw-semibold">{{$order->getPaymentPeriod()}}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{__("start_date")}}:</span>
                            <span class="badge badge-sm badge-secondary">{{$order->start_date?->format(defaultDateFormat()) ?? '-'}}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{__("end_date")}}:</span>
                            <span class="badge badge-sm badge-secondary">{{$order->end_date?->format(defaultDateFormat()) ?? '-'}}</span>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center fs-3">
                            <span class="fw-bold">{{__("total_amount")}}:</span>
                            <span class="fw-semibold">{{showBalance($order->getTotalAmount() ?? 0, true)}}</span>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <div class="col-xl-9">
            @if($order->delivery_error && $order->delivery_status == 'NOT_DELIVERED')
                <div class="alert alert-danger">
                    <h3>Teslimat Hata Mesajı: <span
                            class="badge badge-lg badge-danger">{{$order->delivery_error}}</span></h3>
                </div>
            @endif
            <!--begin:::Tabs-->
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-6">
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                       href="#np_general_information_tab">{{__("general_information")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 " data-bs-toggle="tab"
                       href="#np_proxy_information_tab">{{__("proxy_information")}}</a>
                </li>
                <!--end:::Tab item-->
                @if($order->isThreeProxyDelivery())
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                       href="#np_proxy_history_tab">Proxy Geçmişi</a>
                </li>
                @endif
            </ul>
            <!--end:::Tabs-->
            <!--begin::Tab content-->
            <div class="tab-content">
                <div class="tab-pane fade show active" id="np_general_information_tab">
                    <div class="row g-5 g-xl-8">
                        <div class="col-xl-6">
                    <!--begin::General options-->
                    <form id="primaryForm"
                          action="{{route('admin.orders.update',['order' => $order->id])}}"
                          class="card card-flush py-4 h-100">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>{{__("general_information")}}</h2>
                            </div>
                            <div class="card-toolbar">
                                @if($order->isLocaltonetLikeDelivery())
                                    @if($order->isCanDeliveryType('LOCALTONETV4'))
                                        <button type="button"
                                                class="btn btn-primary me-3 btn-sm changeDeviceBtn"
                                                data-text="Tüm proxyler silinip yeniden oluşturulacak. Emin misiniz?"
                                                data-url="{{route("admin.orders.changeLocaltonetDevice", ["order" => $order->id])}}">
                                            <i class="fa fa-refresh"></i> Proxyleri Değiştir
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-primary me-3 btn-sm changeDeviceBtn"
                                                data-text="Cihaz değiştirmek istediğinize emin misiniz"
                                                data-url="{{route("admin.orders.changeLocaltonetDevice", ["order" => $order->id])}}">
                                            <i class="fa fa-refresh"></i> Cihaz Değiştir
                                        </button>
                                    @endif
                                @endif
                                @if($order->delivery_status == "DELIVERED")
                                    @php
                                        $__stopLabel = $order->isThreeProxyDelivery() ? 'Tüm proxyleri durdurmak istediğinize emin misiniz?' : 'Tüm tünelleri durdurmak istediğinize emin misiniz?';
                                        $__startLabel = $order->isThreeProxyDelivery() ? 'Tüm proxyleri başlatmak istediğinize emin misiniz?' : 'Tüm tünelleri başlatmak istediğinize emin misiniz?';
                                    @endphp
                                    @if($order->areTunnelsStopped())
                                        <button type="button"
                                                class="btn btn-success btn-sm deliveryBtn me-2"
                                                data-text="{{ $__startLabel }}"
                                                data-url="{{route("admin.orders.startTunnels", ["order" => $order->id])}}">
                                            <i class="fa fa-play"></i> Start
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-warning btn-sm deliveryBtn me-2"
                                                data-text="{{ $__stopLabel }}"
                                                data-url="{{route("admin.orders.stopTunnels", ["order" => $order->id])}}">
                                            <i class="fa fa-stop"></i> Stop
                                        </button>
                                    @endif
                                    <button type="button"
                                            class="btn btn-danger btn-sm deliveryBtn"
                                            data-text="Teslimatı geri almak istediğinize emin misiniz?"
                                            data-url="{{route("admin.orders.removeDelivery", ["order" => $order->id])}}">
                                        <i
                                            class="fa fa-rotate-left"></i> Teslimatı Geri Al
                                    </button>
                                @elseif($order->delivery_status == 'NOT_DELIVERED')
                                    <button type="button"
                                            class="btn btn-primary btn-sm deliveryBtn"
                                            data-text="Teslimatı tamamlamak istediğinize emin misiniz?"
                                            data-url="{{route("admin.orders.completeDelivery", ["order" => $order->id])}}">
                                        <i
                                            class="fa fa-truck"></i> Teslimatı Tamamla
                                    </button>
                                @elseif($order->delivery_status == 'BEING_DELIVERED')
                                        <button type="button"
                                                class="btn btn-primary btn-sm deliveryBtn"
                                                data-text="Teslimatı tamamlamak istediğinize emin misiniz?"
                                                data-url="{{route("admin.orders.completeDelivery", ["order" => $order->id])}}">
                                            <i
                                                class="fa fa-truck"></i> Teslimatı Tamamla
                                        </button>
                                @elseif($order->delivery_status == 'QUEUED')
                                    <span class="badge badge-light-success me-2 align-self-center">Sıraya alındı</span>
                                    @if($order->isLocaltonetLikeDelivery())
                                        <button type="button"
                                                class="btn btn-warning btn-sm deliveryBtn"
                                                data-text="Kuyruk beklemeden teslimatı şimdi çalıştırmak istiyor musunuz?"
                                                data-url="{{ route('admin.orders.processLocaltonetDeliveryNow', ['order' => $order->id]) }}">
                                            <i class="fa fa-bolt"></i> Şimdi işle
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body py-0">
                            <!--begin::Input group-->
                            <div class="row g-5 mb-5">
                                <div class="col-12">
                                    <!--begin::Label-->
                                    <label class="required form-label">{{__("status")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Select2-->
                                    <x-admin.form-elements.select name="status"
                                                                  hideSearch="true"
                                                                  :selectedOption="$order->status ?? 1"
                                                                  :options="[
                                                                ['label' => __('active'), 'value' => 'ACTIVE'],
                                                                ['label' => __('passive'), 'value' => 'PASSIVE'],
                                                                ['label' => 'İptal Edildi', 'value' => 'CANCELLED'],
                                                                ['label' => 'Onay Bekliyor', 'value' => 'PENDING'],
                                                                ]"/>
                                    <!--end::Select2-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="required form-label">Başlangıç Tarihi</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input name="start_date" value="{{$order->start_date ? convertDate($order->start_date) : ''}}"
                                           class="form-control dateInput mb-2"/>
                                    <!--end::Input-->
                                </div>
                                @if(@$order->activeDetail?->price_data["duration_unit"] != "ONE_TIME")
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="required form-label">Bitiş Tarihi</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="end_date"
                                               value="{{$order->end_date ? convertDate($order->end_date) : ''}}"
                                               class="form-control dateInput mb-2"/>
                                        <!--end::Input-->
                                    </div>
                                @endif
                                <div class="col-12 d-none">
                                    <!--begin::Input group-->
                                    @if($order->isCanDeliveryType("STACK"))
                                        <!--begin::Label-->
                                        <label class="form-label">Proxy Listesi</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        @if(isset($order->product_info['proxy_list']))
                                            <textarea name="product_info[proxy_list]" class="form-control"
                                                      rows="8">{!! implode('&#10;',$order->product_info['proxy_list']) !!}</textarea>
                                        @else
                                            <textarea name="product_info[proxy_list]" class="form-control"
                                                      rows="8"></textarea>
                                        @endif
                                        <!--end::Input-->
                                    @elseif(!$order->isThreeProxyDelivery())
                                        <!--begin::Label-->
                                        <label class="form-label fw-bold">Proxy Bilgileri</label>
                                        <!--end::Label-->
                                        <div>
                                            @php
                                                $__plist = $order->proxy_list;
                                            @endphp
                                            @if(is_array($__plist))
                                                {!! nl2br(e(implode("\n", $__plist))) !!}
                                            @elseif(is_string($__plist))
                                                {!! nl2br(e($__plist)) !!}
                                            @endif
                                        </div>
                                    @endif
                                    <!--end::Input group-->
                                </div>
                                <div class="col-12 mt-5">
                                    <button type="submit" class="btn btn-primary w-100" id="form_submit_btn">
                                        <span class="indicator-label">
                                            <span class="d-flex flex-center gap-2">
                                                <i class="ki-duotone ki-triangle fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i> {{__("save")}}
                                            </span>
                                        </span>
                                        <span class="indicator-progress">
                                            {{__("please_wait")}}... <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <!--end::Card header-->
                    </form>
                    <!--end::General options-->
                        </div>
                        <div class="col-xl-6 mt-5 mt-xl-0">
                    <div class="card card-flush py-4 h-100">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>{{__("additional_services")}}</h2>
                            </div>
                        </div>
                        <div class="card-body py-0">
                            <x-proxy-additional-services :order="$order"/>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="np_proxy_information_tab">
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>{{__("proxy_information")}}</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body py-0">
                            @if($order->isLocaltonetLikeDelivery())
                                @if(!$order->isCanDeliveryType('LOCALTONETV4'))
                                <div class="mb-7">
                                    <label class="required form-label">Localtonet Proxy Id</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input value="{{$order->getLocaltonetProxyId()}}"
                                               data-np-edit-localtonet-proxy-id="proxy-id"
                                               class="form-control mw-200px"/>
                                        <button class="btn btn-primary btn-sm"
                                                data-np-edit-localtonet-proxy-id="submit-btn"
                                                data-url="{{route("admin.orders.changeLocaltonetProxyId", ["order" => $order->id])}}"
                                                data-text="Lütfen geçerli bir id girdiğinizden emin olunuz. Localtonet proxy idyi güncellemek istediğiniz emin misiniz?">
                                            {{__("save_changes")}}
                                        </button>
                                    </div>
                                </div>
                                @endif
                                @if(!$order->isCanDeliveryType('LOCALTONETV4'))
                                <div class="separator mb-7"></div>
                                <div class="mb-7">
                                    @php
                                        $proxy = $order->getProxyLocaltonet();
                                        if (@$proxy["hasError"] || !isset($proxy["result"]) || @$proxy["result"]["id"] == 0) $proxy = [];
                                        else $proxy = $proxy["result"] ?? [];
                                    @endphp
                                    <label class="required form-label">Proxy Type</label>
                                    <div class="alert alert-primary">Mevcut proxy silinip, seçilen tipte yeni proxy
                                        oluşturulur.
                                    </div>
                                    <div class="d-flex align-items-center mt-2">
                                        <label class="form-check form-check-custom form-check-solid me-10">
                                            <input class="form-check-input h-25px w-25px" type="radio" name="type"
                                                   {{@$proxy["protocolType"] == "ProxyHttp" ? "checked" : ""}}
                                                   data-np-edit-proxy-type="proxy-type"
                                                   value="HTTP">
                                            <span class="form-check-label fw-semibold">HTTP</span>
                                        </label>
                                        <label class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input h-25px w-25px" type="radio" name="type"
                                                   {{@$proxy["protocolType"] == "ProxySocks" ? "checked" : ""}}
                                                   data-np-edit-proxy-type="proxy-type"
                                                   value="SOCKS">
                                            <span class="form-check-label fw-semibold">Socks5</span>
                                        </label>
                                    </div>
                                    <button class="btn btn-primary btn-sm mt-5" data-np-edit-proxy-type="submit-btn"
                                            data-url="{{route("admin.orders.changeLocaltonetProxyType", ["order" => $order->id])}}"
                                            data-text="Localtonet tarafındaki mevcut tunnel silinip seçilen tipte yeni bir tunnel oluşturulacaktır. Proxy tipini düzenlemek istediğinize emin misiniz?">
                                        {{__("save_changes")}}
                                    </button>
                                </div>
                                @endif
                                <div class="separator my-10"></div>
                                <div class="mb-3">
                                    <span class="fw-bold fs-5 text-gray-800">Müşteri proxy paneli (Localtonet)</span>
                                    <span class="text-muted fs-7 d-block mt-1">Port, durum, yetkilendirme, IP geçmişi ve mobil bağlantı işlemleri.</span>
                                </div>
                                <x-proxy-information :order="$order" context="admin"/>
                            @elseif($order->isCanDeliveryType("STACK"))
                                <div>
                                    <!--begin::Label-->
                                    <label class="required form-label">Proxyler</label>
                                    <!--end::Label-->
                                    <textarea class="form-control" data-np-edit-stack-proxy="proxies" cols="30"
                                              rows="10">{{ implode("\n", $order->proxyList) }}</textarea>
                                </div>
                                <button class="btn btn-primary btn-sm mt-3" data-np-edit-stack-proxy="submit-btn"
                                        data-url="{{route("admin.orders.changeStackProxies", ["order" => $order->id])}}"
                                        data-text="Proxy adreslerini güncellemek istediğinize emin misiniz?">
                                    Proxyleri Güncelle
                                </button>
                            @elseif($order->isThreeProxyDelivery())
                                @php
                                    $tpList = $order->getThreeProxyDisplayList();
                                    $tpInfo = $order->product_info ?? [];
                                @endphp
                                <div class="mb-5">
                                    <label class="form-label fw-bold fs-5">3Proxy Bilgileri</label>
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-4">
                                            <span class="fw-bold d-block mb-1">Kullanıcı Adı:</span>
                                            <code>{{ $tpInfo['three_proxy_username'] ?? '-' }}</code>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="fw-bold d-block mb-1">Şifre:</span>
                                            <code>{{ $tpInfo['three_proxy_password'] ?? '-' }}</code>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="fw-bold d-block mb-1">Bitiş Tarihi:</span>
                                            <code>{{ $tpInfo['three_proxy_expire'] ?? '-' }}</code>
                                        </div>
                                    </div>
                                </div>

                                <div class="separator my-5"></div>
                                <label class="form-label fw-bold fs-5 mb-4">Yönetim İşlemleri</label>
                                <div class="row g-4 mb-6">
                                    <div class="col-md-4">
                                        <div class="card border border-dashed border-warning h-100">
                                            <div class="card-body p-5">
                                                <h6 class="fw-bold text-warning mb-3"><i class="fa fa-redo me-2"></i>Tekrar Kurulum</h6>
                                                <p class="text-muted fs-7 mb-4">Mevcut proxyler silinip aynı ayarlarla yeniden oluşturulur. Tüm {{ count($tpList) }} proxy etkilenir.</p>
                                                <button type="button" class="btn btn-warning btn-sm w-100 np-tp-action-btn"
                                                        data-url="{{ route('admin.orders.threeProxyReinstall', ['order' => $order->id]) }}"
                                                        data-confirm="Tüm proxyler silinip yeniden kurulacak. Bu işlem geri alınamaz. Devam etmek istiyor musunuz?"
                                                        data-method="simple">
                                                    <span class="indicator-label">Tekrar Kur</span>
                                                    <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border border-dashed border-primary h-100">
                                            <div class="card-body p-5">
                                                <h6 class="fw-bold text-primary mb-3"><i class="fa fa-key me-2"></i>Kullanıcı / Şifre Değiştir</h6>
                                                <div class="mb-3">
                                                    <input type="text" class="form-control form-control-sm" id="np_tp_new_username"
                                                           placeholder="Yeni kullanıcı adı" value="{{ $tpInfo['three_proxy_username'] ?? '' }}">
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" class="form-control form-control-sm" id="np_tp_new_password"
                                                           placeholder="Yeni şifre" value="{{ $tpInfo['three_proxy_password'] ?? '' }}">
                                                </div>
                                                <button type="button" class="btn btn-primary btn-sm w-100 np-tp-action-btn"
                                                        data-url="{{ route('admin.orders.threeProxyChangeCredentials', ['order' => $order->id]) }}"
                                                        data-confirm="Tüm {{ count($tpList) }} proxy için kullanıcı adı ve şifre değiştirilecek. Devam?"
                                                        data-method="credentials">
                                                    <span class="indicator-label">Değiştir</span>
                                                    <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border border-dashed border-info h-100">
                                            <div class="card-body p-5">
                                                <h6 class="fw-bold text-info mb-3"><i class="fa fa-network-wired me-2"></i>Port Değiştir</h6>
                                                <div class="mb-3">
                                                    <label class="form-label fs-7 fw-semibold mb-1">HTTP Port</label>
                                                    <input type="number" class="form-control form-control-sm" id="np_tp_new_http_port"
                                                           placeholder="HTTP port" value="{{ $tpList[0]['http_port'] ?? '' }}" min="1" max="65535">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fs-7 fw-semibold mb-1">SOCKS5 Port</label>
                                                    <input type="number" class="form-control form-control-sm" id="np_tp_new_socks_port"
                                                           placeholder="SOCKS5 port" value="{{ $tpList[0]['socks_port'] ?? '' }}" min="1" max="65535">
                                                </div>
                                                <button type="button" class="btn btn-info btn-sm w-100 np-tp-action-btn"
                                                        data-url="{{ route('admin.orders.threeProxyChangePort', ['order' => $order->id]) }}"
                                                        data-confirm="Tüm {{ count($tpList) }} proxy için port değiştirilecek. Proxyler sırayla silinip yeniden oluşturulacak. Devam?"
                                                        data-method="port">
                                                    <span class="indicator-label">Port Değiştir</span>
                                                    <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(count($tpList) > 0)
                                    <div class="separator my-5"></div>
                                    <label class="form-label fw-bold fs-5">Proxy Listesi ({{ count($tpList) }} adet)</label>
                                    <div class="table-responsive">
                                        <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                            <thead>
                                                <tr class="fw-bold text-muted">
                                                    <th>#</th>
                                                    <th>IP</th>
                                                    <th>HTTP Port</th>
                                                    <th>SOCKS Port</th>
                                                    <th>Kullanıcı</th>
                                                    <th>Şifre</th>
                                                    <th>Proxy ID</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tpList as $tpIdx => $tp)
                                                    <tr>
                                                        <td class="text-muted">{{ $tpIdx + 1 }}</td>
                                                        <td><code>{{ $tp['ip'] ?? '-' }}</code></td>
                                                        <td>{{ $tp['http_port'] ?? '-' }}</td>
                                                        <td>{{ $tp['socks_port'] ?? '-' }}</td>
                                                        <td><code>{{ $tp['username'] ?? '-' }}</code></td>
                                                        <td><code>{{ $tp['password'] ?? '-' }}</code></td>
                                                        <td><small class="text-muted">{{ substr($tp['proxy_id'] ?? '', 0, 12) }}...</small></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4">
                                        <label class="form-label fw-bold">Düz Metin (Kopyalanabilir)</label>
                                        <textarea class="form-control" rows="{{ min(count($tpList) + 1, 15) }}" readonly>@foreach($tpList as $tp){{ ($tp['ip'] ?? '') . ':' . ($tp['http_port'] ?? '') . ':' . ($tp['username'] ?? '') . ':' . ($tp['password'] ?? '') }}
@endforeach</textarea>
                                    </div>
                                @else
                                    <div class="alert alert-info mt-5">Henüz proxy teslim edilmemiş.</div>
                                @endif
                            @elseif($order->isPProxyDelivery())
                                @php
                                    $ppPi = $order->product_info ?? [];
                                    $ppUuid = $ppPi['pproxy_uuid'] ?? '';
                                    $ppUsername = $ppPi['pproxy_username'] ?? '';
                                    $ppPassword = $ppPi['pproxy_password'] ?? '';
                                    $ppQuotaGb = $ppPi['pproxy_quota_gb'] ?? '';
                                    $ppDays = $ppPi['pproxy_days'] ?? 30;
                                    $ppRaw = $ppPi['pproxy_raw'] ?? [];
                                    $ppActiveUntil = $ppRaw['active_until'] ?? '';
                                    $ppCreatedAt = $ppRaw['created_at'] ?? '';
                                    $ppThreads = $ppRaw['proxy_information']['threads'] ?? 0;
                                    $ppBpsLimit = $ppRaw['proxy_information']['BpsLimit'] ?? 0;

                                    $ppServerDomain = 'tr.saglamproxy.com';
                                    $ppServerPort = '8080';
                                    $ppProduct = $order->product;
                                    $ppProductDomain = $ppProduct?->delivery_items['pproxy_server_domain'] ?? null;
                                    if ($ppProductDomain && trim($ppProductDomain) !== '') {
                                        $ppServerDomain = trim($ppProductDomain);
                                    } else {
                                        $ppSettings = \App\Models\PProxySettings::first();
                                        if ($ppSettings && $ppSettings->server_domain && trim($ppSettings->server_domain) !== '') {
                                            $ppServerDomain = trim($ppSettings->server_domain);
                                        }
                                    }

                                    $ppSubUser = null;
                                    try {
                                        $ppSubUser = $order->getPProxySubUserData();
                                    } catch (\Throwable $e) {}

                                    $ppTrafficUsed = $ppSubUser['traffic_used'] ?? 0;
                                    $ppBandwidth = $ppSubUser['bandwidth'] ?? 0;
                                    $ppLiveActiveUntil = $ppSubUser['active_until'] ?? $ppActiveUntil;
                                    $ppLiveThreads = $ppSubUser['threads'] ?? $ppThreads;

                                    $ppQuotaBytes = $ppQuotaGb ? $ppQuotaGb * 1073741824 : 0;
                                    $ppTrafficPercent = $ppQuotaBytes > 0 ? min(100, round(($ppTrafficUsed / $ppQuotaBytes) * 100, 1)) : 0;

                                    $ppTrafficUsedFormatted = $ppTrafficUsed >= 1073741824
                                        ? number_format($ppTrafficUsed / 1073741824, 2) . ' GB'
                                        : ($ppTrafficUsed >= 1048576
                                            ? number_format($ppTrafficUsed / 1048576, 2) . ' MB'
                                            : number_format($ppTrafficUsed / 1024, 2) . ' KB');

                                    $ppBandwidthFormatted = $ppBandwidth >= 1073741824
                                        ? number_format($ppBandwidth / 1073741824, 2) . ' GB'
                                        : ($ppBandwidth >= 1048576
                                            ? number_format($ppBandwidth / 1048576, 2) . ' MB'
                                            : number_format($ppBandwidth / 1024, 2) . ' KB');

                                    $ppProgressColor = $ppTrafficPercent < 60 ? 'success' : ($ppTrafficPercent < 85 ? 'warning' : 'danger');
                                @endphp

                                @if($order->delivery_status !== 'DELIVERED')
                                    <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                                        <i class="fa fa-exclamation-triangle fs-2 me-4 text-warning"></i>
                                        <div>
                                            <h6 class="mb-0">PProxy henüz teslim edilmemiş.</h6>
                                            <span class="text-muted fs-7">Teslimat durumu: <strong>{{ $order->delivery_status }}</strong></span>
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-5">
                                        <label class="form-label fw-bold fs-5">PProxy Bağlantı Bilgileri</label>
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-3">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                        <i class="fa fa-server text-primary me-1"></i>Proxy Adresi
                                                    </span>
                                                    <code class="fs-6">{{ $ppServerDomain }}</code>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                        <i class="fa fa-plug text-info me-1"></i>Port
                                                    </span>
                                                    <code class="fs-6">{{ $ppServerPort }}</code>
                                                    <span class="text-muted fs-8 d-block">HTTP: 8080 / SOCKS5: 1080</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                        <i class="fa fa-user text-success me-1"></i>Kullanıcı Adı
                                                    </span>
                                                    <code class="fs-6">{{ $ppUsername ?: '-' }}</code>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                        <i class="fa fa-key text-warning me-1"></i>Şifre
                                                    </span>
                                                    <code class="fs-6">{{ $ppPassword ?: '-' }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-5"></div>

                                    <div class="mb-5">
                                        <label class="form-label fw-bold fs-5">Kullanım & Abonelik Bilgileri</label>
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-4">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                        <i class="fa fa-database text-primary me-1"></i>Trafik Kullanımı
                                                    </span>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="fw-bold fs-6">{{ $ppTrafficUsedFormatted }}</span>
                                                        <span class="text-muted fs-7">/ {{ $ppQuotaGb ? $ppQuotaGb . ' GB' : 'Limitsiz' }}</span>
                                                    </div>
                                                    @if($ppQuotaGb)
                                                    <div class="progress h-8px">
                                                        <div class="progress-bar bg-{{ $ppProgressColor }}" role="progressbar"
                                                             style="width: {{ $ppTrafficPercent }}%"
                                                             aria-valuenow="{{ $ppTrafficPercent }}" aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted fs-8 mt-1 d-block">%{{ $ppTrafficPercent }} kullanıldı</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                        <i class="fa fa-exchange-alt text-info me-1"></i>Bant Genişliği
                                                    </span>
                                                    <span class="fw-bold fs-6">{{ $ppBandwidthFormatted }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                        <i class="fa fa-layer-group text-success me-1"></i>Thread
                                                    </span>
                                                    <span class="fw-bold fs-6">{{ $ppLiveThreads }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-4">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                        <i class="fa fa-calendar-alt text-danger me-1"></i>Bitiş Tarihi
                                                    </span>
                                                    <span class="fw-bold fs-6">
                                                        @if($ppLiveActiveUntil)
                                                            {{ \Carbon\Carbon::parse($ppLiveActiveUntil)->format('d.m.Y H:i') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                        <i class="fa fa-clock text-warning me-1"></i>Süre (Gün)
                                                    </span>
                                                    <span class="fw-bold fs-6">{{ $ppDays }} gün</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                    <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                        <i class="fa fa-fingerprint text-secondary me-1"></i>UUID
                                                    </span>
                                                    <code class="fs-8" style="word-break: break-all">{{ $ppUuid ?: '-' }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-5"></div>

                                    <div class="mb-5">
                                        <label class="form-label fw-bold fs-5 mb-3">Proxy Bağlantı Formatı</label>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control form-control-solid" id="ppProxyString" readonly
                                                   value="{{ $ppServerDomain }}:{{ $ppServerPort }}:{{ $ppUsername }}:{{ $ppPassword }}">
                                            <button class="btn btn-light-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('ppProxyString').value).then(()=>toastr.success('Kopyalandı!'))">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Format: ADRES:PORT:USER:PASS</small>
                                    </div>
                                @endif
                            @elseif($order->isPProxyUDelivery())
                                @php
                                    $ppuPi = $order->product_info ?? [];
                                    $ppuPoolIp = $ppuPi['pproxyu_pool_ip'] ?? '';
                                    $ppuPoolPort = $ppuPi['pproxyu_pool_port'] ?? '';
                                    $ppuPoolUser = $ppuPi['pproxyu_pool_user'] ?? '';
                                    $ppuPoolPass = $ppuPi['pproxyu_pool_pass'] ?? '';
                                    $ppuUsername = $ppuPi['pproxyu_username'] ?? '';
                                    $ppuPassword = $ppuPi['pproxyu_password'] ?? '';
                                    $ppuDays = $ppuPi['pproxyu_days'] ?? 30;
                                    $ppuActiveUntil = $ppuPi['pproxyu_active_until'] ?? '';
                                    $ppuPoolList = \App\Models\PProxyUPool::where('is_active', true)->get();
                                @endphp

                                @if($order->delivery_status !== 'DELIVERED')
                                    <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                                        <i class="fa fa-exclamation-triangle fs-2 me-4 text-warning"></i>
                                        <div>
                                            <h6 class="mb-0">PProxyU henüz teslim edilmemiş.</h6>
                                            <span class="text-muted fs-7">Teslimat durumu: <strong>{{ $order->delivery_status }}</strong></span>
                                        </div>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <label class="form-label fw-bold fs-5 mb-0">PProxyU Bağlantı Bilgileri</label>
                                    <button type="button" class="btn btn-sm btn-light-primary" id="ppuEditToggleBtn">
                                        <i class="fa fa-pen me-1"></i>Düzenle
                                    </button>
                                </div>

                                {{-- Display Mode --}}
                                <div id="ppuDisplayMode">
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-3">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                    <i class="fa fa-server text-primary me-1"></i>Proxy Adresi
                                                </span>
                                                <code class="fs-6">{{ $ppuPoolIp }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                    <i class="fa fa-plug text-info me-1"></i>Port
                                                </span>
                                                <code class="fs-6">{{ $ppuPoolPort }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                    <i class="fa fa-user text-success me-1"></i>Kullanıcı Adı
                                                </span>
                                                <code class="fs-6">{{ $ppuPoolUser }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                    <i class="fa fa-key text-warning me-1"></i>Şifre
                                                </span>
                                                <code class="fs-6">{{ $ppuPoolPass }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-5"></div>

                                    <label class="form-label fw-bold fs-5">Müşteri Kimlik Bilgileri</label>
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                    <i class="fa fa-id-badge text-info me-1"></i>Müşteri Kullanıcı Adı
                                                </span>
                                                <code class="fs-6">{{ $ppuUsername }}</code>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-1">
                                                    <i class="fa fa-lock text-danger me-1"></i>Müşteri Şifresi
                                                </span>
                                                <code class="fs-6">{{ $ppuPassword }}</code>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-5"></div>

                                    <label class="form-label fw-bold fs-5">Abonelik Bilgileri</label>
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-4">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                    <i class="fa fa-calendar-alt text-danger me-1"></i>Bitiş Tarihi
                                                </span>
                                                <span class="fw-bold fs-6">
                                                    @if($ppuActiveUntil)
                                                        {{ \Carbon\Carbon::parse($ppuActiveUntil)->format('d.m.Y H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                    <i class="fa fa-clock text-warning me-1"></i>Süre (Gün)
                                                </span>
                                                <span class="fw-bold fs-6">{{ $ppuDays }} gün</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border border-dashed border-gray-300 rounded p-4 h-100">
                                                <span class="text-muted fw-semibold d-block fs-8 mb-2">
                                                    <i class="fa fa-database text-primary me-1"></i>Kota
                                                </span>
                                                <span class="fw-bold fs-6 text-success">Sınırsız</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-5"></div>

                                    <label class="form-label fw-bold fs-5 mb-3">Proxy Bağlantı Formatı</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control form-control-solid" id="ppuProxyString" readonly
                                               value="{{ $ppuPoolIp }}:{{ $ppuPoolPort }}:{{ $ppuPoolUser }}:{{ $ppuPoolPass }}">
                                        <button class="btn btn-light-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('ppuProxyString').value).then(()=>toastr.success('Kopyalandı!'))">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Format: ADRES:PORT:USER:PASS</small>
                                </div>

                                {{-- Edit Mode --}}
                                <div id="ppuEditMode" style="display:none;">
                                    <form id="ppuEditForm">
                                        @csrf
                                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mb-5">
                                            <i class="fa fa-info-circle fs-3 text-primary me-3 mt-1"></i>
                                            <div class="fs-7">
                                                <strong>Havuzdan Proxy Seç:</strong> Mevcut havuzdan bir proxy seçerek bağlantı bilgilerini otomatik doldurun.
                                                <div class="mt-2">
                                                    <select id="ppuPoolSelect" class="form-select form-select-sm form-select-solid" style="max-width:500px;">
                                                        <option value="">-- Manuel giriş yapacağım --</option>
                                                        @foreach($ppuPoolList as $pool)
                                                            <option value="{{ $pool->id }}"
                                                                    data-ip="{{ $pool->ip }}"
                                                                    data-port="{{ $pool->port }}"
                                                                    data-user="{{ $pool->username }}"
                                                                    data-pass="{{ $pool->password }}"
                                                                    {{ ($ppuPi['pproxyu_pool_id'] ?? '') == $pool->id ? 'selected' : '' }}>
                                                                {{ $pool->ip }}:{{ $pool->port }} ({{ $pool->label ?? $pool->username }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <label class="form-label fw-bold fs-5 mb-3">Havuz Proxy Bilgileri</label>
                                        <div class="row g-4 mb-5">
                                            <div class="col-md-3">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-server text-primary me-1"></i>Proxy Adresi
                                                </label>
                                                <input type="text" name="pproxyu_pool_ip" class="form-control form-control-solid" value="{{ $ppuPoolIp }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-plug text-info me-1"></i>Port
                                                </label>
                                                <input type="number" name="pproxyu_pool_port" class="form-control form-control-solid" value="{{ $ppuPoolPort }}" required min="1" max="65535">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-user text-success me-1"></i>Kullanıcı Adı
                                                </label>
                                                <input type="text" name="pproxyu_pool_user" class="form-control form-control-solid" value="{{ $ppuPoolUser }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-key text-warning me-1"></i>Şifre
                                                </label>
                                                <input type="text" name="pproxyu_pool_pass" class="form-control form-control-solid" value="{{ $ppuPoolPass }}" required>
                                            </div>
                                        </div>

                                        <div class="separator my-5"></div>

                                        <label class="form-label fw-bold fs-5 mb-3">Müşteri Kimlik Bilgileri</label>
                                        <div class="row g-4 mb-5">
                                            <div class="col-md-6">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-id-badge text-info me-1"></i>Müşteri Kullanıcı Adı
                                                </label>
                                                <input type="text" name="pproxyu_username" class="form-control form-control-solid" value="{{ $ppuUsername }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-lock text-danger me-1"></i>Müşteri Şifresi
                                                </label>
                                                <input type="text" name="pproxyu_password" class="form-control form-control-solid" value="{{ $ppuPassword }}">
                                            </div>
                                        </div>

                                        <div class="separator my-5"></div>

                                        <label class="form-label fw-bold fs-5 mb-3">Abonelik Bilgileri</label>
                                        <div class="row g-4 mb-5">
                                            <div class="col-md-4">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-clock text-warning me-1"></i>Süre (Gün)
                                                </label>
                                                <input type="number" name="pproxyu_days" class="form-control form-control-solid" value="{{ $ppuDays }}" min="1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-calendar-alt text-danger me-1"></i>Bitiş Tarihi
                                                </label>
                                                <input type="datetime-local" name="pproxyu_active_until" class="form-control form-control-solid"
                                                       value="{{ $ppuActiveUntil ? \Carbon\Carbon::parse($ppuActiveUntil)->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fs-7 fw-semibold">
                                                    <i class="fa fa-database text-primary me-1"></i>Kota
                                                </label>
                                                <input type="text" class="form-control form-control-solid" value="Sınırsız" disabled>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-3 mt-5">
                                            <button type="button" class="btn btn-light" id="ppuEditCancelBtn">
                                                <i class="fa fa-times me-1"></i>İptal
                                            </button>
                                            <button type="submit" class="btn btn-primary" id="ppuEditSaveBtn">
                                                <i class="fa fa-check me-1"></i>Kaydet
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <!--end::Card header-->
                    </div>
                </div>
                @if($order->isThreeProxyDelivery())
                <div class="tab-pane fade" id="np_proxy_history_tab">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Proxy Geçmişi</h2>
                            </div>
                        </div>
                        <div class="card-body py-0">
                            @php
                                $proxyLogs = $order->threeProxyLogs;
                            @endphp
                            @if($proxyLogs->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                        <thead>
                                            <tr class="fw-bold text-muted bg-light">
                                                <th class="ps-4 rounded-start">Tarih</th>
                                                <th>İşlem</th>
                                                <th>IP Listesi</th>
                                                <th>Proxy Sayısı</th>
                                                <th>Kullanıcı/Şifre</th>
                                                <th>Süre</th>
                                                <th class="rounded-end">Detay</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($proxyLogs as $log)
                                                <tr>
                                                    <td class="ps-4">
                                                        <span class="text-dark fw-semibold d-block fs-7">{{ $log->created_at->format('d.m.Y') }}</span>
                                                        <span class="text-muted d-block fs-8">{{ $log->created_at->format('H:i:s') }}</span>
                                                    </td>
                                                    <td>{!! $log->action_badge !!}</td>
                                                    <td>
                                                        @if($log->ip_list && count($log->ip_list) > 0)
                                                            @foreach(array_slice($log->ip_list, 0, 3) as $ip)
                                                                <code class="d-block fs-8">{{ $ip }}</code>
                                                            @endforeach
                                                            @if(count($log->ip_list) > 3)
                                                                <span class="text-muted fs-8">+{{ count($log->ip_list) - 3 }} daha</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-light-dark">{{ $log->proxy_count }}</span>
                                                    </td>
                                                    <td>
                                                        @if($log->username)
                                                            <code class="fs-8">{{ $log->username }}</code>
                                                            @if($log->password)
                                                                <br><code class="fs-8 text-muted">{{ $log->password }}</code>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($log->duration_human)
                                                            <span class="badge badge-light-info">{{ $log->duration_human }}</span>
                                                        @elseif($log->started_at && !$log->ended_at)
                                                            <span class="badge badge-light-success">Aktif</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($log->metadata)
                                                            <button type="button" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="popover"
                                                                    data-bs-trigger="hover" data-bs-html="true" data-bs-placement="left"
                                                                    data-bs-content="<pre class='mb-0 fs-8'>{{ e(json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</pre>">
                                                                <i class="fa fa-info-circle"></i>
                                                            </button>
                                                        @elseif($log->proxy_data && count($log->proxy_data) > 0)
                                                            <button type="button" class="btn btn-sm btn-light-info btn-icon" data-bs-toggle="popover"
                                                                    data-bs-trigger="hover" data-bs-html="true" data-bs-placement="left"
                                                                    data-bs-content="@foreach($log->proxy_data as $pd)<code class='d-block fs-8'>{{ ($pd['ip'] ?? '') . ':' . ($pd['http_port'] ?? '') . ':' . ($pd['socks_port'] ?? '') }}</code>@endforeach">
                                                                <i class="fa fa-list"></i>
                                                            </button>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">Henüz kayıt bulunmuyor.</div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <!--end::Tab content-->
        </div>
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="popover"]').popover({ container: 'body' });

            $(document).on('change', '[name="product[delivery_type]"]', function () {
                if ($(this).val() == 'STACK') {
                    $('.show-on-stack').show(500);
                } else {
                    $('.show-on-stack').hide(500);
                }
            })

            $('[name="product[delivery_type]"]:checked').trigger('change');
            const isCreate = "{{!isset($product)}}";

            $(document).on("change", "[name='product[status]']", function () {
                let icon = $("#np_add_product_status");
                console.log("aa")
                if (icon.hasClass("bg-success")) {
                    icon.removeClass("bg-success").addClass("bg-danger")
                } else {
                    icon.removeClass("bg-danger").addClass("bg-success")
                }
            })

            $(document).on('blur', '[data-np-price="price"]', function () {
                if ($(this).val() && (/\d/.test($(this).val()))) {
                    $(this).val(priceFormat.to(priceFormat.from($(this).val())))
                } else {
                    $(this).val("")
                }
            })

            $(document).on("click", "[data-np-price='add-item']", function () {
                let table = $("[data-np-price='items']");
                table.append($("[data-np-price='item-template'] tbody").html())

                table.find("tbody tr:last select").select2();
            })

            $(document).on("click", "[data-np-price='remove-item']", function () {
                let item = $(this);
                item.closest("tr").remove();
            })

            $(document).on("submit", "#primaryForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    url = form.attr("action");

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton(form.find("[type='submit']"), 0);
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => {
                                if (isCreate) {
                                    window.location.href = res.redirectUrl;
                                } else {
                                    window.location.reload();
                                }
                            })
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                    }
                })
            })

            $(document).on("click", ".deliveryBtn", function () {
                let btn = $(this),
                    url = btn.data("url"),
                    text = btn.data("text");
                alerts.confirm.fire({
                    text: text
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                alerts.wait.fire();
                            },
                            complete: function (data, status) {
                                propSubmitButton(btn, 0);
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload())
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })
            $(document).on("click", ".changeDeviceBtn", function () {
                let btn = $(this),
                    url = btn.data("url"),
                    text = btn.data("text");
                alerts.confirm.fire({
                    text: text
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                alerts.wait.fire();
                            },
                            complete: function (data, status) {
                                propSubmitButton(btn, 0);
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload())
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })

            $(document).on("click", "[data-np-edit-localtonet-proxy-id='submit-btn']", function () {
                let btn = $(this),
                    url = btn.data("url"),
                    text = btn.data("text");
                alerts.confirm.fire({
                    text: text
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                proxyId: $("[data-np-edit-localtonet-proxy-id='proxy-id']").val()
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                alerts.wait.fire();
                            },
                            complete: function (data, status) {
                                propSubmitButton(btn, 0);
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload())
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })

            $(document).on("click", "[data-np-edit-stack-proxy='submit-btn']", function () {
                let btn = $(this),
                    url = btn.data("url"),
                    text = btn.data("text");
                alerts.confirm.fire({
                    text: text
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                proxies: $("[data-np-edit-stack-proxy='proxies']").val()
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                alerts.wait.fire();
                            },
                            complete: function (data, status) {
                                propSubmitButton(btn, 0);
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload())
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })
            $(document).on("click", ".np-tp-action-btn", function () {
                let btn = $(this),
                    url = btn.data("url"),
                    confirmText = btn.data("confirm"),
                    method = btn.data("method");

                let postData = { _token: "{{csrf_token()}}" };

                if (method === "credentials") {
                    let u = $("#np_tp_new_username").val(),
                        p = $("#np_tp_new_password").val();
                    if (!u || u.length < 3) {
                        alerts.error.fire({ text: "Kullanıcı adı en az 3 karakter olmalıdır." });
                        return;
                    }
                    if (!p || p.length < 4) {
                        alerts.error.fire({ text: "Şifre en az 4 karakter olmalıdır." });
                        return;
                    }
                    postData.username = u;
                    postData.password = p;
                } else if (method === "port") {
                    let hp = $("#np_tp_new_http_port").val(),
                        sp = $("#np_tp_new_socks_port").val();
                    if (!hp || parseInt(hp) < 1 || parseInt(hp) > 65535) {
                        alerts.error.fire({ text: "Geçerli bir HTTP port giriniz (1-65535)." });
                        return;
                    }
                    postData.http_port = parseInt(hp);
                    if (sp && parseInt(sp) >= 1 && parseInt(sp) <= 65535) {
                        postData.socks_port = parseInt(sp);
                    }
                }

                alerts.confirm.fire({ text: confirmText }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: postData,
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                alerts.wait.fire();
                            },
                            complete: function (data) {
                                propSubmitButton(btn, 0);
                                let res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then(() => window.location.reload());
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? ""
                                    });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("click", "[data-np-edit-proxy-type='submit-btn']", function () {
                let btn = $(this),
                    url = btn.data("url"),
                    text = btn.data("text"),
                    proxyType = $("[data-np-edit-proxy-type='proxy-type']:checked").val();

                if (!proxyType) {
                    alerts.error.fire({
                        "text": "{{__("custom_field_is_required", ["name" => "Proxy Type"])}}"
                    })
                    return false;
                }

                alerts.confirm.fire({
                    text: text
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                type: proxyType
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                                alerts.wait.fire();
                            },
                            complete: function (data, status) {
                                propSubmitButton(btn, 0);
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload())
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })

            @if($order->isPProxyUDelivery())
            $('#ppuEditToggleBtn').on('click', function(){
                $('#ppuDisplayMode').hide();
                $('#ppuEditMode').show();
                $(this).hide();
            });
            $('#ppuEditCancelBtn').on('click', function(){
                $('#ppuEditMode').hide();
                $('#ppuDisplayMode').show();
                $('#ppuEditToggleBtn').show();
            });
            $('#ppuPoolSelect').on('change', function(){
                var opt = $(this).find(':selected');
                if(opt.val()){
                    $('[name="pproxyu_pool_ip"]').val(opt.data('ip'));
                    $('[name="pproxyu_pool_port"]').val(opt.data('port'));
                    $('[name="pproxyu_pool_user"]').val(opt.data('user'));
                    $('[name="pproxyu_pool_pass"]').val(opt.data('pass'));
                }
            });
            $('#ppuEditForm').on('submit', function(e){
                e.preventDefault();
                var btn = $('#ppuEditSaveBtn');
                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.orders.pproxyuUpdateInfo", ["order" => $order->id]) }}',
                    dataType: 'json',
                    data: $(this).serialize(),
                    beforeSend: function(){
                        propSubmitButton(btn, 1);
                        alerts.wait.fire();
                    },
                    complete: function(data){
                        propSubmitButton(btn, 0);
                        var res = data.responseJSON;
                        if(res && res.success === true){
                            alerts.success.fire({
                                title: '{{ __("success") }}',
                                text: res?.message ?? ''
                            }).then(function(){ window.location.reload(); });
                        } else {
                            alerts.error.fire({
                                title: '{{ __("error") }}',
                                text: res?.message ?? ''
                            });
                        }
                    }
                });
            });
            @endif
        })
    </script>
@endsection
