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
                    <a class="nav-link pb-4 active" data-bs-toggle="tab" href="#system_settings_site_tab">
                        <i class="fa fa-globe me-2 text-primary"></i>Site Ayarları
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_status_tab">
                        <i class="fa fa-heartbeat me-2 text-danger"></i>Sistem Durumu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_general_tab">Genel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_localtonet_tab">Localtonet</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_sms_mail_tab">
                        <i class="fa fa-envelope me-2"></i>SMS ve Mail
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_templates_tab">
                        <i class="fa fa-file-alt me-2"></i>SMS ve Mail Şablonları
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_campaigns_tab">
                        <i class="fa fa-bullhorn me-2"></i>Kampanya Gönderimi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_parasut_tab">
                        <i class="fa fa-file-invoice me-2 text-success"></i>Paraşüt
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_telegram_tab">
                        <i class="fa fa-paper-plane me-2"></i>Telegram
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="systemSettingsTabs">
                {{-- Site Ayarları Tab --}}
                <div class="tab-pane fade show active" id="system_settings_site_tab" role="tabpanel">
                    <div class="mb-6">
                        <h3 class="fw-bold mb-1">Site Ayarları</h3>
                        <span class="text-muted fs-7">Sitenizin temel bilgilerini ve görsellerini buradan yönetin.</span>
                    </div>
                    <form id="siteSettingsForm">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <div class="card card-flush border border-dashed h-100">
                                    <div class="card-header pt-5 pb-0">
                                        <h4 class="card-title fw-bold fs-5"><i class="fa fa-info-circle text-primary me-2"></i>Genel Bilgiler</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold required">Site Adı</label>
                                            <input type="text" name="brand_name" class="form-control" value="{{ brand('name') }}" placeholder="Proxynetic" />
                                        </div>
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Müşteri Paneli Başlığı</label>
                                            <input type="text" name="brand_clientarea_title" class="form-control" value="{{ brand('clientarea_title') }}" placeholder="Müşteri Paneli" />
                                        </div>
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Site URL</label>
                                            <input type="url" name="brand_base_url" class="form-control" value="{{ brand('base_url') }}" placeholder="https://my.proxynetic.com" />
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Web Sitesi</label>
                                            <input type="url" name="brand_website" class="form-control" value="{{ brand('contact_info.website') }}" placeholder="https://www.proxynetic.com" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-flush border border-dashed h-100">
                                    <div class="card-header pt-5 pb-0">
                                        <h4 class="card-title fw-bold fs-5"><i class="fa fa-image text-info me-2"></i>Görseller</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Logo (Açık Tema)</label>
                                            <input type="text" name="brand_logo" class="form-control" value="{{ brand('logo') }}" placeholder="assets/images/logo/logo.png" />
                                            @if(brand('logo'))
                                            <div class="mt-2 p-3 bg-white border rounded text-center">
                                                <img src="{{ url(brand('logo')) }}" alt="Logo" style="max-height:50px" />
                                            </div>
                                            @endif
                                        </div>
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Logo (Koyu Tema)</label>
                                            <input type="text" name="brand_logo_dark" class="form-control" value="{{ brand('logo_dark') }}" placeholder="assets/images/logo/logo-dark.png" />
                                            @if(brand('logo_dark'))
                                            <div class="mt-2 p-3 bg-dark border rounded text-center">
                                                <img src="{{ url(brand('logo_dark')) }}" alt="Logo Dark" style="max-height:50px" />
                                            </div>
                                            @endif
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Favicon</label>
                                            <input type="text" name="brand_favicon" class="form-control" value="{{ brand('favicon') }}" placeholder="assets/images/brand/favicon.ico" />
                                            @if(brand('favicon'))
                                            <div class="mt-2 d-flex align-items-center">
                                                <img src="{{ url(brand('favicon')) }}" alt="Favicon" style="max-height:32px" class="me-2" />
                                                <span class="text-muted fs-8">Mevcut favicon</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-flush border border-dashed h-100">
                                    <div class="card-header pt-5 pb-0">
                                        <h4 class="card-title fw-bold fs-5"><i class="fa fa-address-book text-success me-2"></i>İletişim Bilgileri</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Telefon</label>
                                            <input type="text" name="brand_phone" class="form-control" value="{{ brand('contact_info.phone_number') }}" placeholder="0501 123 1212" />
                                        </div>
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">E-posta</label>
                                            <input type="email" name="brand_email" class="form-control" value="{{ brand('contact_info.email') }}" placeholder="info@sirket.com" />
                                        </div>
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Adres Satır 1</label>
                                            <input type="text" name="brand_address1" class="form-control" value="{{ brand('contact_info.address_line_1') }}" />
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Adres Satır 2</label>
                                            <input type="text" name="brand_address2" class="form-control" value="{{ brand('contact_info.address_line_2') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-flush border border-dashed h-100">
                                    <div class="card-header pt-5 pb-0">
                                        <h4 class="card-title fw-bold fs-5"><i class="fab fa-telegram text-info me-2"></i>Sosyal Medya</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold"><i class="fab fa-facebook text-primary me-1"></i>Facebook</label>
                                            <input type="url" name="brand_facebook" class="form-control form-control-sm" value="{{ brand('social_media.facebook') }}" />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold"><i class="fab fa-twitter text-info me-1"></i>Twitter / X</label>
                                            <input type="url" name="brand_twitter" class="form-control form-control-sm" value="{{ brand('social_media.twitter') }}" />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold"><i class="fab fa-instagram text-danger me-1"></i>Instagram</label>
                                            <input type="url" name="brand_instagram" class="form-control form-control-sm" value="{{ brand('social_media.instagram') }}" />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold"><i class="fab fa-linkedin text-primary me-1"></i>LinkedIn</label>
                                            <input type="url" name="brand_linkedin" class="form-control form-control-sm" value="{{ brand('social_media.linkedin') }}" />
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold"><i class="fab fa-youtube text-danger me-1"></i>YouTube</label>
                                            <input type="url" name="brand_youtube" class="form-control form-control-sm" value="{{ brand('social_media.youtube') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-8">
                            <button type="submit" class="btn btn-primary" id="saveSiteSettingsBtn">
                                <i class="fa fa-save me-2"></i>Kaydet
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sistem Durumu Tab --}}
                <div class="tab-pane fade" id="system_status_tab" role="tabpanel">
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
                                    <a href="javascript:;" class="fs-6 fw-bold text-primary mt-1 text-hover-dark text-decoration-underline" id="queuePendingText" data-bs-toggle="modal" data-bs-target="#pendingJobsModal">{{ $systemStatus['queue_pending'] }} bekleyen</a>
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

                <div class="tab-pane fade" id="system_settings_general_tab" role="tabpanel">
                    <form action="{{ route('admin.updateSettings') }}" method="POST">
                        @csrf
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
                            <hr class="my-8">
                            <div class="w-100">
                                <div class="d-flex align-items-center mb-5">
                                    <i class="fa fa-file-invoice text-primary fs-2 me-3"></i>
                                    <h3 class="mb-0 fw-bold">Otomatik Fatura Ayarları</h3>
                                </div>
                                <p class="text-gray-600 mb-6">Sipariş bitiş tarihi yaklaşan müşteriler için yenileme faturası otomatik oluşturulur. Aşağıdan süreleri ve davranışı ayarlayabilirsiniz.</p>

                                <div class="row">
                                    <div class="col-md-6 mb-5">
                                        <label class="form-label fw-semibold">Otomatik Yenileme Faturası</label>
                                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                            <input type="hidden" name="auto_invoice[auto_renew_enabled]" value="0" />
                                            <input class="form-check-input" type="checkbox" name="auto_invoice[auto_renew_enabled]" value="1"
                                                   id="autoRenewEnabledSwitch" {{ ($autoInvoiceSettings['auto_renew_enabled'] ?? true) ? 'checked' : '' }}/>
                                            <label class="form-check-label" for="autoRenewEnabledSwitch">Aktif</label>
                                        </div>
                                        <div class="form-text text-gray-500 mt-1">Kapatıldığında otomatik yenileme faturası oluşturulmaz.</div>
                                    </div>

                                    <div class="col-md-6 mb-5">
                                        <label class="form-label fw-semibold">Ödenmemiş Faturada Hizmet Durdurma</label>
                                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                            <input type="hidden" name="auto_invoice[stop_service_on_unpaid]" value="0" />
                                            <input class="form-check-input" type="checkbox" name="auto_invoice[stop_service_on_unpaid]" value="1"
                                                   id="stopServiceUnpaidSwitch" {{ ($autoInvoiceSettings['stop_service_on_unpaid'] ?? true) ? 'checked' : '' }}/>
                                            <label class="form-check-label" for="stopServiceUnpaidSwitch">Aktif</label>
                                        </div>
                                        <div class="form-text text-gray-500 mt-1">Son ödeme tarihinde ödenmemişse hizmet otomatik durdurulur.</div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-4 mb-5">
                                        <label class="form-label fw-semibold required">Aylık/Yıllık Siparişler İçin (gün önce)</label>
                                        <input type="number" name="auto_invoice[renew_days_before_monthly]" min="1" max="30"
                                               class="form-control form-control-solid"
                                               value="{{ $autoInvoiceSettings['renew_days_before_monthly'] ?? 7 }}" />
                                        <div class="form-text text-gray-500">Bitiş tarihinden kaç gün önce fatura oluşturulsun?</div>
                                    </div>

                                    <div class="col-md-4 mb-5">
                                        <label class="form-label fw-semibold required">Haftalık Siparişler İçin (gün önce)</label>
                                        <input type="number" name="auto_invoice[renew_days_before_weekly]" min="1" max="7"
                                               class="form-control form-control-solid"
                                               value="{{ $autoInvoiceSettings['renew_days_before_weekly'] ?? 2 }}" />
                                        <div class="form-text text-gray-500">Haftalık siparişlerde kaç gün önce?</div>
                                    </div>

                                    <div class="col-md-4 mb-5">
                                        <label class="form-label fw-semibold required">Günlük Siparişler İçin (gün önce)</label>
                                        <input type="number" name="auto_invoice[renew_days_before_daily]" min="0" max="3"
                                               class="form-control form-control-solid"
                                               value="{{ $autoInvoiceSettings['renew_days_before_daily'] ?? 1 }}" />
                                        <div class="form-text text-gray-500">0 = günlük siparişlerde fatura oluşturulmaz.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-5">
                                        <label class="form-label fw-semibold required">Hatırlatma Süresi (gün önce)</label>
                                        <input type="number" name="auto_invoice[reminder_days_before]" min="1" max="14"
                                               class="form-control form-control-solid"
                                               value="{{ $autoInvoiceSettings['reminder_days_before'] ?? 3 }}" />
                                        <div class="form-text text-gray-500">Son ödeme tarihinden kaç gün önce hatırlatma gönderilsin?</div>
                                    </div>
                                </div>

                                <hr class="my-5">
                                <div class="d-flex align-items-center mb-4">
                                    <i class="fa fa-clock text-warning fs-3 me-3"></i>
                                    <h5 class="mb-0 fw-bold">Çalışma Frekansı</h5>
                                </div>
                                <div class="alert alert-light-info border border-info border-dashed d-flex align-items-center p-5">
                                    <i class="fa fa-info-circle text-info fs-3 me-3"></i>
                                    <div>
                                        <span class="fw-bold d-block mb-1">Tüm otomatik görevler her 5 dakikada bir kontrol edilir.</span>
                                        <span class="text-gray-600">Yenileme faturası oluşturma, ödeme hatırlatma ve hizmet durdurma işlemleri sürekli kontrol edilir. Aynı sipariş/fatura için mükerrer işlem yapılmaz.</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
                        </div>
                    </form>

                    <hr class="my-10">

                    <div class="w-75 mx-auto">
                        <div class="d-flex align-items-center justify-content-between mb-5">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-list-alt text-info fs-2 me-3"></i>
                                <h3 class="mb-0 fw-bold">Otomatik Oluşturulan Yenileme Faturaları</h3>
                            </div>
                            <span class="badge badge-light-info fs-7">Son 100 kayıt</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-200 align-middle gy-4 gs-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4 rounded-start">#</th>
                                        <th>Fatura No</th>
                                        <th>Müşteri</th>
                                        <th>Ürün/Hizmet</th>
                                        <th class="text-end">Tutar</th>
                                        <th>Son Ödeme</th>
                                        <th>Durum</th>
                                        <th class="text-center rounded-end">Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($renewInvoices as $rInv)
                                        @php
                                            $renewItem = $rInv->items->first();
                                            $statusColors = [
                                                'PENDING' => 'warning',
                                                'PAID' => 'success',
                                                'CANCELLED' => 'danger',
                                                'REFUNDED' => 'info',
                                            ];
                                            $statusLabels = [
                                                'PENDING' => 'Bekliyor',
                                                'PAID' => 'Ödendi',
                                                'CANCELLED' => 'İptal',
                                                'REFUNDED' => 'İade',
                                            ];
                                        @endphp
                                        <tr>
                                            <td class="ps-4">{{ $rInv->id }}</td>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $rInv->id) }}" class="text-primary fw-semibold text-hover-dark">
                                                    {{ $rInv->invoice_number ?? '-' }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($rInv->user)
                                                    <a href="{{ route('admin.users.show', $rInv->user_id) }}" class="text-gray-800 text-hover-primary">
                                                        {{ $rInv->user->full_name ?? '' }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Silinmiş</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-gray-700 fs-7">{{ $renewItem->name ?? '-' }}</span>
                                            </td>
                                            <td class="text-end fw-bold">
                                                {{ number_format($rInv->total_price_with_vat ?? 0, 2, ',', '.') }} ₺
                                            </td>
                                            <td>
                                                @if($rInv->due_date)
                                                    <span class="{{ $rInv->due_date->isPast() && $rInv->status === 'PENDING' ? 'text-danger fw-bold' : 'text-gray-700' }}">
                                                        {{ $rInv->due_date->format('d.m.Y') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-light-{{ $statusColors[$rInv->status] ?? 'secondary' }} fw-semibold">
                                                    {{ $statusLabels[$rInv->status] ?? $rInv->status }}
                                                </span>
                                            </td>
                                            <td class="text-center text-gray-600 fs-7">
                                                {{ $rInv->created_at?->format('d.m.Y H:i') ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-8">
                                                <i class="fa fa-inbox fs-1 text-gray-300 d-block mb-2"></i>
                                                Henüz otomatik yenileme faturası oluşturulmamış.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="system_settings_localtonet_tab" role="tabpanel">
                    <form action="{{ route('admin.updateSettings') }}" method="POST">
                        @csrf
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
                    </form>
                </div>

                {{-- SMS ve Mail Şablonları Tab --}}
                <div class="tab-pane fade" id="system_settings_templates_tab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h3 class="fw-bold mb-1">Bildirim Şablonları</h3>
                            <span class="text-muted fs-7">SMS ve e-posta bildirim içeriklerini özelleştirin</span>
                        </div>
                    </div>

                    @if(isset($notificationTemplates) && $notificationTemplates->count() > 0)
                        @foreach(['genel', 'fatura', 'siparis', 'destek'] as $categoryKey)
                            @if(isset($notificationTemplates[$categoryKey]))
                                @php
                                    $catLabel = \App\Models\NotificationTemplate::getCategoryLabel($categoryKey);
                                    $catIcon = \App\Models\NotificationTemplate::getCategoryIcon($categoryKey);
                                    $catColor = \App\Models\NotificationTemplate::getCategoryColor($categoryKey);
                                    $templates = $notificationTemplates[$categoryKey];
                                @endphp
                                <div class="card card-flush border border-dashed mb-5">
                                    <div class="card-header pt-4 pb-3 min-h-auto cursor-pointer" data-bs-toggle="collapse" data-bs-target="#templateCategory_{{ $categoryKey }}">
                                        <div class="card-title d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-light-{{ $catColor }}" style="width:38px;height:38px;">
                                                <i class="fa {{ $catIcon }} fs-5 text-{{ $catColor }}"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold fs-5 d-block">{{ $catLabel }}</span>
                                                <span class="text-muted fs-8">{{ $templates->count() }} şablon</span>
                                            </div>
                                        </div>
                                        <div class="card-toolbar">
                                            <i class="fa fa-chevron-down text-muted fs-7"></i>
                                        </div>
                                    </div>
                                    <div class="collapse {{ $categoryKey === 'genel' ? 'show' : '' }}" id="templateCategory_{{ $categoryKey }}">
                                        <div class="card-body pt-0">
                                            <div class="table-responsive">
                                                <table class="table table-row-bordered table-row-gray-200 align-middle fs-7 gy-3 mb-0">
                                                    <thead>
                                                        <tr class="text-muted fw-semibold">
                                                            <th>Şablon</th>
                                                            <th class="text-center" style="width:80px">SMS</th>
                                                            <th class="text-center" style="width:80px">E-Posta</th>
                                                            <th class="text-center" style="width:80px">Durum</th>
                                                            <th class="text-end" style="width:80px">İşlem</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($templates as $tpl)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-semibold">{{ $tpl->title }}</span>
                                                                <span class="text-muted fs-8 d-block">{{ $tpl->key }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($tpl->sms_enabled)
                                                                    <span class="badge badge-light-success">Aktif</span>
                                                                @else
                                                                    <span class="badge badge-light-danger">Pasif</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($tpl->mail_enabled)
                                                                    <span class="badge badge-light-success">Aktif</span>
                                                                @else
                                                                    <span class="badge badge-light-danger">Pasif</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                                                                    <input class="form-check-input templateToggle" type="checkbox"
                                                                           data-id="{{ $tpl->id }}" {{ $tpl->is_active ? 'checked' : '' }}/>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <button type="button" class="btn btn-sm btn-icon btn-light-primary templateEditBtn"
                                                                        data-id="{{ $tpl->id }}">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-5">
                            <i class="fa fa-exclamation-triangle fs-2 text-warning me-4"></i>
                            <div>
                                <h5 class="fw-bold mb-1">Şablon bulunamadı</h5>
                                <p class="text-muted fs-7 mb-0">Bildirim şablonları henüz oluşturulmamış. Lütfen veritabanı seeder'ını çalıştırın.</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- SMS ve Mail Ayarları Tab --}}
                <div class="tab-pane fade" id="system_settings_sms_mail_tab" role="tabpanel">
                    <form action="{{ route('admin.updateSettings') }}" method="POST">
                        @csrf
                        <div class="w-75 mx-auto">
                            {{-- SMS Ayarları --}}
                            <div class="card card-flush border border-dashed mb-8">
                                <div class="card-header pt-5 pb-3 min-h-auto">
                                    <div class="card-title d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-light-success" style="width:42px;height:42px;">
                                            <i class="fa fa-sms fs-4 text-success"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold fs-5 d-block">SMS Ayarları</span>
                                            <span class="text-muted fs-8">SMS gönderim sağlayıcı yapılandırması</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row">
                                        <div class="col-12 mb-5">
                                            <label class="form-label fw-semibold">SMS Durumu</label>
                                            <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                                <input type="hidden" name="sms_mail[sms_enabled]" value="0"/>
                                                <input class="form-check-input" type="checkbox" name="sms_mail[sms_enabled]" value="1"
                                                       id="smsEnabledSwitch" {{ $smsMailConfig['sms_enabled'] ? 'checked' : '' }}/>
                                                <label class="form-check-label" for="smsEnabledSwitch">SMS gönderimini aktif et</label>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-5">
                                            <label class="form-label fw-semibold required">SMS Sağlayıcı</label>
                                            <select name="sms_mail[sms_provider]" id="smsProviderSelect" class="form-select form-select-solid">
                                                <option value="iletimerkezi" {{ $smsMailConfig['sms_provider'] === 'iletimerkezi' ? 'selected' : '' }}>İleti Merkezi</option>
                                                <option value="mutlucell" {{ $smsMailConfig['sms_provider'] === 'mutlucell' ? 'selected' : '' }}>Mutlucell</option>
                                            </select>
                                        </div>

                                        {{-- İleti Merkezi Ayarları --}}
                                        <div id="smsIletimerkeziFields" class="{{ $smsMailConfig['sms_provider'] !== 'iletimerkezi' ? 'd-none' : '' }}">
                                            <div class="separator separator-dashed my-5"></div>
                                            <h6 class="fw-bold text-gray-700 mb-4"><i class="fa fa-cog me-2"></i>İleti Merkezi Ayarları</h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">API Key</label>
                                                    <input type="text" name="sms_mail[iletimerkezi_key]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['iletimerkezi_key'] }}" placeholder="API Key"/>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">API Secret</label>
                                                    <input type="text" name="sms_mail[iletimerkezi_secret]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['iletimerkezi_secret'] }}" placeholder="API Secret"/>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">Gönderici Adı (Origin)</label>
                                                    <input type="text" name="sms_mail[iletimerkezi_origin]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['iletimerkezi_origin'] }}" placeholder="Gönderici adı"/>
                                                </div>
                                                <div class="col-md-3 mb-5">
                                                    <label class="form-label fw-semibold">Debug Modu</label>
                                                    <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                                        <input type="hidden" name="sms_mail[iletimerkezi_debug]" value="0"/>
                                                        <input class="form-check-input" type="checkbox" name="sms_mail[iletimerkezi_debug]" value="1"
                                                               {{ $smsMailConfig['iletimerkezi_debug'] ? 'checked' : '' }}/>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mb-5">
                                                    <label class="form-label fw-semibold">Sandbox Modu</label>
                                                    <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                                        <input type="hidden" name="sms_mail[iletimerkezi_sandbox]" value="0"/>
                                                        <input class="form-check-input" type="checkbox" name="sms_mail[iletimerkezi_sandbox]" value="1"
                                                               {{ $smsMailConfig['iletimerkezi_sandbox'] ? 'checked' : '' }}/>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div id="iletimerkeziBalanceCard" class="border border-dashed rounded p-3 bg-light-success text-center">
                                                        <div id="iletimerkeziBalanceContent">
                                                            <i class="fa fa-spinner fa-spin text-muted"></i>
                                                            <span class="text-muted ms-2 fs-7">Bakiye sorgulanıyor...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mutlucell Ayarları --}}
                                        <div id="smsMutlucellFields" class="{{ $smsMailConfig['sms_provider'] !== 'mutlucell' ? 'd-none' : '' }}">
                                            <div class="separator separator-dashed my-5"></div>
                                            <h6 class="fw-bold text-gray-700 mb-4"><i class="fa fa-cog me-2"></i>Mutlucell Ayarları</h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">Kullanıcı Adı</label>
                                                    <input type="text" name="sms_mail[mutlucell_username]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['mutlucell_username'] }}" placeholder="Mutlucell kullanıcı adı"/>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">Şifre</label>
                                                    <input type="password" name="sms_mail[mutlucell_password]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['mutlucell_password'] }}" placeholder="Mutlucell şifre"/>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">Varsayılan Gönderici</label>
                                                    <input type="text" name="sms_mail[mutlucell_sender]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['mutlucell_sender'] }}" placeholder="Gönderici adı"/>
                                                </div>
                                                <div class="col-md-6 mb-5 d-flex align-items-end">
                                                    <div id="mutlucellBalanceCard" class="w-100 border border-dashed rounded p-3 bg-light-success text-center">
                                                        <div id="mutlucellBalanceContent">
                                                            <i class="fa fa-spinner fa-spin text-muted"></i>
                                                            <span class="text-muted ms-2 fs-7">Bakiye sorgulanıyor...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- SMS Test Alanı --}}
                                        <div class="separator separator-dashed my-5"></div>
                                        <h6 class="fw-bold text-gray-700 mb-4"><i class="fa fa-vial me-2"></i>SMS Test</h6>
                                        <div class="row">
                                            <div class="col-md-5 mb-5">
                                                <label class="form-label fw-semibold">Test Telefon Numarası</label>
                                                <input type="text" id="smsTestNumber" class="form-control form-control-solid"
                                                       placeholder="905xxxxxxxxx"/>
                                                <div class="form-text text-gray-500">Ülke kodu ile birlikte (905...)</div>
                                            </div>
                                            <div class="col-md-5 mb-5">
                                                <label class="form-label fw-semibold">Test Mesajı</label>
                                                <input type="text" id="smsTestMessage" class="form-control form-control-solid"
                                                       value="Bu bir test SMS mesajıdır." placeholder="Test mesajı"/>
                                            </div>
                                            <div class="col-md-2 mb-5 d-flex align-items-end gap-2">
                                                <button type="button" class="btn btn-sm btn-light-info w-100" id="smsTestConnectionBtn">
                                                    <i class="fa fa-plug me-1"></i>Bağlantı
                                                </button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <button type="button" class="btn btn-sm btn-light-success" id="smsTestSendBtn">
                                                    <i class="fa fa-paper-plane me-1"></i>Test SMS Gönder
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <div id="smsTestResult" class="d-none"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Mail Ayarları --}}
                            <div class="card card-flush border border-dashed mb-8">
                                <div class="card-header pt-5 pb-3 min-h-auto">
                                    <div class="card-title d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-light-primary" style="width:42px;height:42px;">
                                            <i class="fa fa-at fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold fs-5 d-block">Mail Ayarları</span>
                                            <span class="text-muted fs-8">E-posta gönderim sağlayıcı yapılandırması</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="row">
                                        <div class="col-md-6 mb-5">
                                            <label class="form-label fw-semibold required">Mail Sağlayıcı</label>
                                            <select name="sms_mail[mail_provider]" id="mailProviderSelect" class="form-select form-select-solid">
                                                <option value="smtp" {{ $smsMailConfig['mail_provider'] === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                <option value="mailjet" {{ $smsMailConfig['mail_provider'] === 'mailjet' ? 'selected' : '' }}>Mailjet</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-5">
                                            <label class="form-label fw-semibold required">Gönderici E-posta</label>
                                            <input type="email" name="sms_mail[mail_from_address]" class="form-control form-control-solid"
                                                   value="{{ $smsMailConfig['mail_from_address'] }}" placeholder="ornek@domain.com"/>
                                        </div>
                                        <div class="col-md-3 mb-5">
                                            <label class="form-label fw-semibold required">Gönderici Adı</label>
                                            <input type="text" name="sms_mail[mail_from_name]" class="form-control form-control-solid"
                                                   value="{{ $smsMailConfig['mail_from_name'] }}" placeholder="Şirket adı"/>
                                        </div>

                                        {{-- SMTP Ayarları --}}
                                        <div id="mailSmtpFields" class="{{ $smsMailConfig['mail_provider'] !== 'smtp' ? 'd-none' : '' }}">
                                            <div class="separator separator-dashed my-5"></div>
                                            <h6 class="fw-bold text-gray-700 mb-4"><i class="fa fa-server me-2"></i>SMTP Ayarları</h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">SMTP Host</label>
                                                    <input type="text" name="sms_mail[smtp_host]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['smtp_host'] }}" placeholder="smtp.example.com"/>
                                                </div>
                                                <div class="col-md-3 mb-5">
                                                    <label class="form-label fw-semibold required">Port</label>
                                                    <input type="number" name="sms_mail[smtp_port]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['smtp_port'] }}" placeholder="587"/>
                                                </div>
                                                <div class="col-md-3 mb-5">
                                                    <label class="form-label fw-semibold required">Şifreleme</label>
                                                    <select name="sms_mail[smtp_encryption]" class="form-select form-select-solid">
                                                        <option value="tls" {{ $smsMailConfig['smtp_encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                                                        <option value="ssl" {{ $smsMailConfig['smtp_encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                                                        <option value="" {{ empty($smsMailConfig['smtp_encryption']) ? 'selected' : '' }}>Yok</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">SMTP Kullanıcı Adı</label>
                                                    <input type="text" name="sms_mail[smtp_username]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['smtp_username'] }}" placeholder="kullanici@domain.com"/>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">SMTP Şifre</label>
                                                    <input type="password" name="sms_mail[smtp_password]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['smtp_password'] }}" placeholder="SMTP şifresi"/>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mailjet Ayarları --}}
                                        <div id="mailMailjetFields" class="{{ $smsMailConfig['mail_provider'] !== 'mailjet' ? 'd-none' : '' }}">
                                            <div class="separator separator-dashed my-5"></div>
                                            <h6 class="fw-bold text-gray-700 mb-4"><i class="fa fa-paper-plane me-2"></i>Mailjet Ayarları</h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">API Key</label>
                                                    <input type="text" name="sms_mail[mailjet_apikey]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['mailjet_apikey'] }}" placeholder="Mailjet API Key"/>
                                                </div>
                                                <div class="col-md-6 mb-5">
                                                    <label class="form-label fw-semibold required">API Secret</label>
                                                    <input type="text" name="sms_mail[mailjet_apisecret]" class="form-control form-control-solid"
                                                           value="{{ $smsMailConfig['mailjet_apisecret'] }}" placeholder="Mailjet API Secret"/>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mail Test Alanı --}}
                                        <div class="separator separator-dashed my-5"></div>
                                        <h6 class="fw-bold text-gray-700 mb-4"><i class="fa fa-vial me-2"></i>Mail Test</h6>
                                        <div class="row">
                                            <div class="col-md-5 mb-5">
                                                <label class="form-label fw-semibold">Test E-posta Adresi</label>
                                                <input type="email" id="mailTestAddress" class="form-control form-control-solid"
                                                       placeholder="test@example.com"/>
                                            </div>
                                            <div class="col-md-5 mb-5">
                                                <label class="form-label fw-semibold">Test Konusu</label>
                                                <input type="text" id="mailTestSubject" class="form-control form-control-solid"
                                                       value="Test E-postası" placeholder="E-posta konusu"/>
                                            </div>
                                            <div class="col-md-2 mb-5 d-flex align-items-end gap-2">
                                                <button type="button" class="btn btn-sm btn-light-info w-100" id="mailTestConnectionBtn">
                                                    <i class="fa fa-plug me-1"></i>Bağlantı
                                                </button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <button type="button" class="btn btn-sm btn-light-success" id="mailTestSendBtn">
                                                    <i class="fa fa-paper-plane me-1"></i>Test Mail Gönder
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <div id="mailTestResult" class="d-none"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-save me-2"></i>Değişiklikleri Kaydet
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Kampanya Gönderimi Tab --}}
                <div class="tab-pane fade" id="system_settings_campaigns_tab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h3 class="fw-bold mb-1"><i class="fa fa-bullhorn text-primary me-2"></i>Kampanya SMS ve Mail Gönderimi</h3>
                            <span class="text-muted fs-7">Toplu SMS ve e-posta kampanyaları oluşturun, hedef kitle belirleyin ve gönderin</span>
                        </div>
                        <button type="button" class="btn btn-primary" id="newCampaignBtn">
                            <i class="fa fa-plus me-2"></i>Yeni Kampanya
                        </button>
                    </div>

                    {{-- Kampanya Listesi --}}
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-200 align-middle fs-7 gy-3" id="campaignsTable">
                            <thead>
                                <tr class="text-muted fw-semibold">
                                    <th>#</th>
                                    <th>Kampanya Adı</th>
                                    <th>Kanal</th>
                                    <th>Hedef Kitle</th>
                                    <th class="text-center">Alıcı</th>
                                    <th class="text-center">SMS</th>
                                    <th class="text-center">E-Posta</th>
                                    <th class="text-center">Durum</th>
                                    <th>Tarih</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns ?? [] as $camp)
                                <tr data-id="{{ $camp->id }}">
                                    <td>{{ $camp->id }}</td>
                                    <td><span class="fw-semibold">{{ $camp->name }}</span></td>
                                    <td>
                                        @if($camp->channel === 'sms')
                                            <span class="badge badge-light-success">SMS</span>
                                        @elseif($camp->channel === 'mail')
                                            <span class="badge badge-light-info">E-Posta</span>
                                        @else
                                            <span class="badge badge-light-primary">SMS + E-Posta</span>
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\Campaign::getTargetTypeLabel($camp->target_type) }}</td>
                                    <td class="text-center">{{ $camp->total_recipients }}</td>
                                    <td class="text-center">{{ $camp->sent_sms }}</td>
                                    <td class="text-center">{{ $camp->sent_mail }}</td>
                                    <td class="text-center">
                                        @if($camp->status === 'draft')
                                            <span class="badge badge-light-warning">Taslak</span>
                                        @elseif($camp->status === 'sending')
                                            <span class="badge badge-light-info">Gönderiliyor</span>
                                        @elseif($camp->status === 'sent')
                                            <span class="badge badge-light-success">Gönderildi</span>
                                        @else
                                            <span class="badge badge-light-danger">Hata</span>
                                        @endif
                                    </td>
                                    <td class="fs-8 text-muted">{{ $camp->sent_at ? $camp->sent_at->format('d/m/Y H:i') : $camp->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        @if($camp->status === 'draft')
                                            <button class="btn btn-sm btn-icon btn-light-primary campaignEditBtn" data-id="{{ $camp->id }}" title="Düzenle">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-icon btn-light-success campaignSendBtn" data-id="{{ $camp->id }}" title="Gönder">
                                                <i class="fa fa-paper-plane"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-icon btn-light-info campaignDuplicateBtn" data-id="{{ $camp->id }}" title="Kopyala">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                        <button class="btn btn-sm btn-icon btn-light-danger campaignDeleteBtn" data-id="{{ $camp->id }}" title="Sil">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="campaignEmptyRow">
                                    <td colspan="10" class="text-center text-muted py-8">
                                        <i class="fa fa-bullhorn fs-2 text-muted d-block mb-3"></i>
                                        Henüz kampanya oluşturulmamış
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Tab content-->

            {{-- Kampanya Oluşturma/Düzenleme Modal --}}
            <div class="modal fade" id="pendingJobsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                <i class="fa fa-clock text-primary me-2"></i>Bekleyen Kuyruk İşleri
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body py-5">
                            <div id="pendingJobsLoading" class="text-center py-10">
                                <span class="spinner-border spinner-border-sm text-primary me-2"></span> Yükleniyor...
                            </div>
                            <div id="pendingJobsEmpty" class="text-center py-10 d-none">
                                <i class="fa fa-check-circle text-success fs-1 d-block mb-3"></i>
                                <span class="text-muted fs-5">Bekleyen iş bulunmuyor.</span>
                            </div>
                            <div class="table-responsive d-none" id="pendingJobsTableWrap">
                                <table class="table table-row-bordered table-row-gray-200 align-middle gy-3 gs-3">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="ps-3 rounded-start min-w-40px">ID</th>
                                            <th class="min-w-200px">Açıklama</th>
                                            <th>Detay</th>
                                            <th class="text-center">Deneme</th>
                                            <th>Oluşturulma</th>
                                            <th class="rounded-end text-center">Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingJobsBody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="campaignModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="fw-bold" id="campaignModalTitle">Yeni Kampanya</h3>
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <i class="fa fa-times fs-4"></i>
                            </div>
                        </div>
                        <div class="modal-body py-8">
                            <form id="campaignForm">
                                @csrf
                                <input type="hidden" name="campaign_id" id="campaignId" value="">

                                <div class="row">
                                    {{-- Sol Kolon: Temel Bilgiler --}}
                                    <div class="col-lg-5">
                                        <div class="card card-flush border border-dashed mb-5">
                                            <div class="card-header pt-4 pb-2 min-h-auto">
                                                <div class="card-title"><i class="fa fa-cog text-primary me-2"></i>Kampanya Ayarları</div>
                                            </div>
                                            <div class="card-body pt-2">
                                                <div class="mb-5">
                                                    <label class="form-label fw-semibold required">Kampanya Adı</label>
                                                    <input type="text" name="name" id="campName" class="form-control form-control-solid" placeholder="Örn: Yılbaşı Kampanyası"/>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="form-label fw-semibold required">Gönderim Kanalı</label>
                                                    <select name="channel" id="campChannel" class="form-select form-select-solid">
                                                        <option value="both">SMS + E-Posta</option>
                                                        <option value="sms">Yalnızca SMS</option>
                                                        <option value="mail">Yalnızca E-Posta</option>
                                                    </select>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="form-label fw-semibold required">Hedef Kitle</label>
                                                    <select name="target_type" id="campTargetType" class="form-select form-select-solid">
                                                        <option value="all">Tüm Müşteriler</option>
                                                        <option value="user_group">Müşteri Grubuna Göre</option>
                                                        <option value="product_category">Ürün Kategorisine Göre</option>
                                                        <option value="product">Belirli Ürünü Alanlara</option>
                                                        <option value="active_orders">Aktif Siparişi Olanlara</option>
                                                        <option value="no_service">Hizmeti Olmayanlara</option>
                                                        <option value="custom">Manuel Seçim</option>
                                                    </select>
                                                </div>

                                                <div id="campFilterUserGroup" class="mb-5 d-none">
                                                    <label class="form-label fw-semibold">Müşteri Grupları</label>
                                                    <select name="user_group_ids[]" id="campUserGroupIds" class="form-select form-select-solid" multiple>
                                                        @foreach($userGroups ?? [] as $ug)
                                                            <option value="{{ $ug->id }}">{{ $ug->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div id="campFilterCategory" class="mb-5 d-none">
                                                    <label class="form-label fw-semibold">Ürün Kategorileri</label>
                                                    <select name="category_ids[]" id="campCategoryIds" class="form-select form-select-solid" multiple>
                                                        @foreach($productCategories ?? [] as $pc)
                                                            <option value="{{ $pc->id }}">{{ $pc->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div id="campFilterProduct" class="mb-5 d-none">
                                                    <label class="form-label fw-semibold">Ürünler</label>
                                                    <select name="product_ids[]" id="campProductIds" class="form-select form-select-solid" multiple>
                                                        @foreach($products ?? [] as $pr)
                                                            <option value="{{ $pr->id }}">{{ $pr->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div id="campFilterCustom" class="mb-5 d-none">
                                                    <label class="form-label fw-semibold">Müşteri ID'leri (virgülle ayırın)</label>
                                                    <input type="text" name="user_ids_text" id="campUserIdsText" class="form-control form-control-solid" placeholder="1,2,3,15"/>
                                                </div>

                                                <button type="button" class="btn btn-sm btn-light-primary w-100" id="campPreviewBtn">
                                                    <i class="fa fa-eye me-2"></i>Alıcıları Önizle
                                                </button>
                                                <div id="campPreviewResult" class="mt-3 d-none">
                                                    <div class="border border-dashed rounded p-3 bg-light">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="fw-bold fs-7">Toplam Alıcı:</span>
                                                            <span class="badge badge-primary" id="campPreviewCount">0</span>
                                                        </div>
                                                        <div id="campPreviewList" class="fs-8 text-muted" style="max-height:200px;overflow-y:auto;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sağ Kolon: İçerikler --}}
                                    <div class="col-lg-7">
                                        {{-- SMS İçeriği --}}
                                        <div class="card card-flush border border-dashed mb-5" id="campSmsCard">
                                            <div class="card-header pt-4 pb-2 min-h-auto">
                                                <div class="card-title"><i class="fa fa-sms text-success me-2"></i>SMS İçeriği</div>
                                            </div>
                                            <div class="card-body pt-2">
                                                <textarea name="sms_content" id="campSmsContent" class="form-control form-control-solid" rows="4" placeholder="Merhaba @{{ad}}, kampanya mesajınız..."></textarea>
                                                <div class="mt-2">
                                                    <span class="text-muted fs-8">Kullanılabilir değişkenler: </span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="ad">@{{ad}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="soyad">@{{soyad}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="email">@{{email}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="site_url">@{{site_url}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="site_adi">@{{site_adi}}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mail İçeriği --}}
                                        <div class="card card-flush border border-dashed mb-5" id="campMailCard">
                                            <div class="card-header pt-4 pb-2 min-h-auto">
                                                <div class="card-title"><i class="fa fa-envelope text-info me-2"></i>E-Posta İçeriği</div>
                                            </div>
                                            <div class="card-body pt-2">
                                                <div class="mb-4">
                                                    <label class="form-label fw-semibold">Konu</label>
                                                    <input type="text" name="mail_subject" id="campMailSubject" class="form-control form-control-solid" placeholder="E-posta konu satırı..."/>
                                                </div>
                                                <div>
                                                    <label class="form-label fw-semibold">İçerik (HTML)</label>
                                                    <textarea name="mail_content" id="campMailContent" class="form-control" rows="12"></textarea>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="text-muted fs-8">Kullanılabilir değişkenler: </span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="ad">@{{ad}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="soyad">@{{soyad}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="email">@{{email}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="site_url">@{{site_url}}</span>
                                                    <span class="badge badge-light-info cursor-pointer campVar" data-var="site_adi">@{{site_adi}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-3 pt-3 border-top mt-3">
                                    <button type="reset" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                                    <button type="submit" class="btn btn-primary" id="campaignSaveBtn">
                                        <span class="indicator-label"><i class="fa fa-save me-1"></i>Kaydet</span>
                                        <span class="indicator-progress">Kaydediliyor...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kampanya Gönder Onay Modal --}}
            <div class="modal fade" id="campaignSendConfirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-light-warning">
                            <h4 class="fw-bold"><i class="fa fa-exclamation-triangle text-warning me-2"></i>Kampanya Gönderimi</h4>
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <i class="fa fa-times fs-4"></i>
                            </div>
                        </div>
                        <div class="modal-body py-6">
                            <p class="fs-6">Bu kampanyayı göndermek istediğinize emin misiniz?</p>
                            <p class="text-muted fs-7">Gönderim başladıktan sonra geri alınamaz. Tüm hedef alıcılara SMS ve/veya e-posta gönderilecektir.</p>
                            <input type="hidden" id="sendConfirmCampaignId" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                            <button type="button" class="btn btn-warning" id="campaignSendConfirmBtn">
                                <span class="indicator-label"><i class="fa fa-paper-plane me-1"></i>Gönder</span>
                                <span class="indicator-progress">Gönderiliyor...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

                {{-- Paraşüt Tab --}}
                <div class="tab-pane fade" id="system_settings_parasut_tab" role="tabpanel">
                    <form id="parasutSettingsForm">
                        @csrf
                        <div class="w-75 mx-auto">
                            <div class="d-flex align-items-center mb-6">
                                <i class="fa fa-file-invoice fs-2 text-success me-3"></i>
                                <div>
                                    <h3 class="fw-bold mb-0">Paraşüt E-Fatura Ayarları</h3>
                                    <span class="text-muted fs-7">Paraşüt API bağlantı bilgileri ve e-fatura resmileştirme ayarları</span>
                                </div>
                            </div>

                            <div class="separator my-5"></div>
                            <h5 class="fw-bold text-gray-800 mb-4"><i class="fa fa-key me-2"></i>API Bağlantı Bilgileri</h5>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Client ID</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="parasut_client_id" class="form-control form-control-solid"
                                           value="{{ config('parasut.connection.client_id') }}" placeholder="Client ID" autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Client Secret</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="password" name="parasut_client_secret" class="form-control form-control-solid"
                                           value="{{ config('parasut.connection.client_secret') }}" placeholder="Client Secret" autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Firma ID</label>
                                    <div class="text-muted fs-8">Paraşüt Company ID</div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="parasut_company_id" class="form-control form-control-solid"
                                           value="{{ config('parasut.connection.company_id') }}" placeholder="656302">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">E-Posta</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="email" name="parasut_username" class="form-control form-control-solid"
                                           value="{{ config('parasut.connection.username') }}" placeholder="mail@sirket.com" autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Parola</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="password" name="parasut_password" class="form-control form-control-solid"
                                           value="{{ config('parasut.connection.password') }}" placeholder="••••••••" autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Redirect URI</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="parasut_redirect_uri" class="form-control form-control-solid"
                                           value="{{ config('parasut.connection.redirect_uri', 'urn:ietf:wg:oauth:2.0:oob') }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Test Modu</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="parasut_is_stage" value="1"
                                               {{ config('parasut.connection.is_stage') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold">Stage/Test ortamı</label>
                                    </div>
                                </div>
                            </div>

                            <div class="separator my-5"></div>
                            <h5 class="fw-bold text-gray-800 mb-4"><i class="fa fa-cog me-2"></i>Resmileştirme Ayarları</h5>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Faturaları Resmileştir</label>
                                    <div class="text-muted fs-8">Fatura ödenme tarihi itibari ile kaç gün sonra otomatik olarak resmileştirilsin?</div>
                                </div>
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center gap-4 mb-3">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="radio" name="parasut_auto_formalize" value="1"
                                                   id="parasutAutoOn" {{ config('parasut.auto_formalize') ? 'checked' : '' }}
                                                   onchange="document.getElementById('parasutFormalizeDaysRow').style.display='flex'">
                                            <label class="form-check-label fw-semibold" for="parasutAutoOn">Otomatik</label>
                                        </div>
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="radio" name="parasut_auto_formalize" value="0"
                                                   id="parasutAutoOff" {{ !config('parasut.auto_formalize') ? 'checked' : '' }}
                                                   onchange="document.getElementById('parasutFormalizeDaysRow').style.display='none'">
                                            <label class="form-check-label fw-semibold" for="parasutAutoOff">Manuel</label>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3" id="parasutFormalizeDaysRow"
                                         style="{{ config('parasut.auto_formalize') ? '' : 'display:none' }}">
                                        <span class="text-gray-600 fw-semibold">Otomatik Resmileştirme Günü</span>
                                        <input type="number" name="parasut_formalize_days" class="form-control form-control-solid"
                                               style="width:80px;" min="0" max="30"
                                               value="{{ config('parasut.formalize_days', 3) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Kdv Muafiyet Kodu</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="parasut_vat_exemption_code" class="form-control form-control-solid"
                                           value="{{ config('parasut.vat_exemption_code', '335') }}" placeholder="335">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Kasa ID</label>
                                    <div class="text-muted fs-8">Paraşüt ödeme kaydı için kasa hesap ID</div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="parasut_account_id" class="form-control form-control-solid"
                                           value="{{ config('parasut.account_id') }}" placeholder="1000230432">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Fatura ID Ön Ek</label>
                                    <div class="text-muted fs-8">Paraşüt fatura serisi (invoice_series)</div>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="parasut_invoice_series" class="form-control form-control-solid"
                                           value="{{ config('parasut.invoice_series', 'AIBC') }}" placeholder="AIBC">
                                </div>
                            </div>

                            <div class="separator my-5"></div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-light-success" id="parasutTestBtn">
                                    <i class="fa fa-plug me-1"></i>Bağlantıyı Test Et
                                </button>
                                <button type="submit" class="btn btn-primary" id="parasutSaveBtn">
                                    <span class="indicator-label"><i class="fa fa-save me-1"></i>Kaydet</span>
                                    <span class="indicator-progress">Kaydediliyor...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Telegram Tab --}}
                <div class="tab-pane fade" id="system_settings_telegram_tab" role="tabpanel">
                    <form id="telegramSettingsForm">
                        @csrf
                        <div class="w-75 mx-auto">
                            <div class="d-flex align-items-center mb-6">
                                <i class="fa fa-paper-plane fs-2 text-info me-3"></i>
                                <div>
                                    <h3 class="fw-bold mb-0">Telegram Bildirim Ayarları</h3>
                                    <span class="text-muted fs-7">Yeni destek talebi oluşturulduğunda Telegram üzerinden anlık bildirim alın</span>
                                </div>
                            </div>

                            <div class="separator my-5"></div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Bot Token</label>
                                    <div class="text-muted fs-8 mb-1">@BotFather ile oluşturduğunuz bot token</div>
                                </div>
                                <div class="col-md-8">
                                    <input type="password" name="telegram_bot_token" class="form-control form-control-solid"
                                           value="{{ config('services.telegram.bot_token') }}"
                                           placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11" autocomplete="off">
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold required">Chat ID</label>
                                    <div class="text-muted fs-8 mb-1">Bildirimlerin gönderileceği chat ID</div>
                                </div>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" name="telegram_chat_id" class="form-control form-control-solid"
                                               value="{{ config('services.telegram.chat_id') }}"
                                               placeholder="123456789" id="telegramChatIdInput">
                                        <button type="button" class="btn btn-light-info" id="telegramFindChatId" title="Bot'a /start gönderdikten sonra tıklayın">
                                            <i class="fa fa-search me-1"></i>Chat ID Bul
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Bildirimler</label>
                                    <div class="text-muted fs-8 mb-1">Telegram bildirimlerini aktif/pasif yapın</div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="telegram_enabled" id="telegramEnabled"
                                               value="1" {{ config('services.telegram.enabled') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="telegramEnabled">Aktif</label>
                                    </div>
                                </div>
                            </div>

                            <div class="separator my-5"></div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-light-primary" id="telegramTestBtn">
                                    <i class="fa fa-paper-plane me-1"></i>Test Mesajı Gönder
                                </button>
                                <button type="submit" class="btn btn-primary" id="telegramSaveBtn">
                                    <span class="indicator-label"><i class="fa fa-save me-1"></i>Kaydet</span>
                                    <span class="indicator-progress">Kaydediliyor...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    {{-- Template Edit Modal --}}
    <div class="modal fade" id="templateEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="templateEditTitle">Şablon Düzenle</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-5 px-8">
                    <form id="templateEditForm">
                        @csrf
                        <input type="hidden" id="templateEditId" name="id"/>

                        {{-- Variables Info --}}
                        <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-4 mb-5">
                            <i class="fa fa-info-circle fs-3 text-info me-3 mt-1"></i>
                            <div>
                                <span class="fw-bold fs-7 d-block mb-1">Kullanılabilir Değişkenler</span>
                                <div id="templateVariablesList" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Durum ve Kanallar --}}
                            <div class="col-12 mb-5">
                                <div class="d-flex flex-wrap gap-5">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="tplIsActive" value="1"/>
                                        <label class="form-check-label fw-semibold" for="tplIsActive">Şablon Aktif</label>
                                    </div>
                                    <div class="separator-vertical border-gray-300 mx-1 d-none d-sm-block" style="height:24px"></div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="sms_enabled" id="tplSmsEnabled" value="1"/>
                                        <label class="form-check-label fw-semibold" for="tplSmsEnabled">SMS Gönderimi</label>
                                    </div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="mail_enabled" id="tplMailEnabled" value="1"/>
                                        <label class="form-check-label fw-semibold" for="tplMailEnabled">E-Posta Gönderimi</label>
                                    </div>
                                    <div class="separator-vertical border-gray-300 mx-1 d-none d-sm-block" style="height:24px"></div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input bg-warning" type="checkbox" name="admin_sms_enabled" id="tplAdminSmsEnabled" value="1"/>
                                        <label class="form-check-label fw-semibold text-warning" for="tplAdminSmsEnabled">Admin SMS</label>
                                    </div>
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input bg-warning" type="checkbox" name="admin_mail_enabled" id="tplAdminMailEnabled" value="1"/>
                                        <label class="form-check-label fw-semibold text-warning" for="tplAdminMailEnabled">Admin E-Posta</label>
                                    </div>
                                </div>
                            </div>

                            {{-- SMS İçeriği --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fw-semibold">
                                    <i class="fa fa-sms text-success me-1"></i>SMS İçeriği
                                </label>
                                <textarea name="sms_content" id="tplSmsContent" class="form-control form-control-solid" rows="3"
                                          placeholder="SMS mesaj içeriği..."></textarea>
                                <div class="form-text text-gray-500">Değişkenleri çift süslü parantez ile kullanın: <code>{<!-- -->{degisken_adi}<!-- -->}</code></div>
                            </div>

                            {{-- Mail Konu --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fw-semibold">
                                    <i class="fa fa-at text-primary me-1"></i>E-Posta Konusu
                                </label>
                                <input type="text" name="mail_subject" id="tplMailSubject" class="form-control form-control-solid"
                                       placeholder="E-posta konu satırı..."/>
                            </div>

                            {{-- Mail İçeriği (TinyMCE) --}}
                            <div class="col-12 mb-5">
                                <label class="form-label fw-semibold">
                                    <i class="fa fa-code text-primary me-1"></i>E-Posta İçeriği (HTML)
                                </label>
                                <textarea name="mail_content" id="tplMailContent" class="form-control" rows="12"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-3">
                            <button type="reset" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-primary" id="templateSaveBtn">
                                <span class="indicator-label"><i class="fa fa-save me-1"></i>Kaydet</span>
                                <span class="indicator-progress">Kaydediliyor...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
    <script src="{{ assetAdmin('plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        $(document).ready(function(){
            // === Notification Templates ===
            var tplEditor = null;

            function initTinyMCE() {
                if (tplEditor) {
                    tplEditor.destroy();
                    tplEditor = null;
                }
                tinymce.init({
                    selector: '#tplMailContent',
                    height: 350,
                    menubar: true,
                    plugins: 'code table lists link image preview fullscreen',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code fullscreen preview',
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
                    branding: false,
                    promotion: false,
                    setup: function(editor) {
                        tplEditor = editor;
                    }
                });
            }

            $(document).on('click', '.templateEditBtn', function(){
                var id = $(this).data('id');
                $('#templateEditForm')[0].reset();

                $.ajax({
                    url: '/netAdmin/notification-template/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            var d = res.data;
                            $('#templateEditId').val(d.id);
                            $('#templateEditTitle').text('Şablon Düzenle: ' + d.title);
                            $('#tplIsActive').prop('checked', d.is_active);
                            $('#tplSmsEnabled').prop('checked', d.sms_enabled);
                            $('#tplMailEnabled').prop('checked', d.mail_enabled);
                            $('#tplAdminSmsEnabled').prop('checked', d.admin_sms_enabled);
                            $('#tplAdminMailEnabled').prop('checked', d.admin_mail_enabled);
                            $('#tplSmsContent').val(d.sms_content || '');
                            $('#tplMailSubject').val(d.mail_subject || '');

                            var varsHtml = '';
                            if (d.variables && d.variables.length > 0) {
                                d.variables.forEach(function(v) {
                                    varsHtml += '<span class="badge badge-light-primary cursor-pointer templateVarBadge" data-var="{{' + v + '}}" title="Kopyalamak için tıklayın">{{' + v + '}}</span>';
                                });
                            } else {
                                varsHtml = '<span class="text-muted fs-8">Bu şablon için değişken tanımlanmamış</span>';
                            }
                            $('#templateVariablesList').html(varsHtml);

                            $('#templateEditModal').modal('show');

                            setTimeout(function(){
                                initTinyMCE();
                                setTimeout(function(){
                                    if (tplEditor) {
                                        tplEditor.setContent(d.mail_content || '');
                                    }
                                }, 300);
                            }, 200);
                        }
                    },
                    error: function() {
                        toastr.error('Şablon yüklenirken hata oluştu.');
                    }
                });
            });

            $(document).on('click', '.templateVarBadge', function(){
                var varText = $(this).data('var');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(varText);
                    toastr.info(varText + ' kopyalandı');
                }
            });

            $('#templateEditForm').on('submit', function(e){
                e.preventDefault();
                var id = $('#templateEditId').val();
                var btn = $('#templateSaveBtn');

                if (tplEditor) {
                    tplEditor.save();
                }

                var formData = {
                    _token: '{{ csrf_token() }}',
                    is_active: $('#tplIsActive').is(':checked') ? 1 : 0,
                    sms_enabled: $('#tplSmsEnabled').is(':checked') ? 1 : 0,
                    mail_enabled: $('#tplMailEnabled').is(':checked') ? 1 : 0,
                    admin_sms_enabled: $('#tplAdminSmsEnabled').is(':checked') ? 1 : 0,
                    admin_mail_enabled: $('#tplAdminMailEnabled').is(':checked') ? 1 : 0,
                    sms_content: $('#tplSmsContent').val(),
                    mail_subject: $('#tplMailSubject').val(),
                    mail_content: $('#tplMailContent').val(),
                };

                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.ajax({
                    url: '/netAdmin/notification-template/' + id,
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#templateEditModal').modal('hide');
                            setTimeout(function(){ window.location.reload(); }, 800);
                        } else {
                            toastr.error(res.message || 'Hata oluştu');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Kaydetme hatası');
                    },
                    complete: function() {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
            });

            $(document).on('change', '.templateToggle', function(){
                var id = $(this).data('id');
                var toggle = $(this);
                $.ajax({
                    url: '/netAdmin/notification-template/' + id + '/toggle',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                        } else {
                            toggle.prop('checked', !toggle.is(':checked'));
                            toastr.error(res.message || 'Hata oluştu');
                        }
                    },
                    error: function() {
                        toggle.prop('checked', !toggle.is(':checked'));
                        toastr.error('İşlem sırasında hata oluştu');
                    }
                });
            });

            $('#templateEditModal').on('hidden.bs.modal', function(){
                if (tplEditor) {
                    tplEditor.destroy();
                    tplEditor = null;
                }
            });

            // === Existing System Settings JS ===
            var $ltHidden = $("#localtonetHttpVerifyHidden");
            var $ltSwitch = $("#localtonetHttpVerifySwitch");
            function syncLocaltonetVerifyHidden() {
                if (!$ltHidden.length || !$ltSwitch.length) return;
                $ltHidden.prop("disabled", $ltSwitch.is(":checked"));
            }
            $ltSwitch.on("change", syncLocaltonetVerifyHidden);
            syncLocaltonetVerifyHidden();

            $('#smsProviderSelect').on('change', function(){
                var val = $(this).val();
                $('#smsIletimerkeziFields').toggleClass('d-none', val !== 'iletimerkezi');
                $('#smsMutlucellFields').toggleClass('d-none', val !== 'mutlucell');
                fetchSmsBalance();
            });

            $('#mailProviderSelect').on('change', function(){
                var val = $(this).val();
                $('#mailSmtpFields').toggleClass('d-none', val !== 'smtp');
                $('#mailMailjetFields').toggleClass('d-none', val !== 'mailjet');
            });

            function showTestResult(container, success, message, details) {
                var cls = success ? 'alert-success' : 'alert-danger';
                var icon = success ? 'fa-check-circle' : 'fa-times-circle';
                var html = '<div class="alert ' + cls + ' d-flex align-items-start py-3 px-4 mb-0">'
                    + '<i class="fa ' + icon + ' me-3 mt-1"></i>'
                    + '<div><strong>' + message + '</strong>';
                if (details) {
                    html += '<br><small class="text-muted">' + details + '</small>';
                }
                html += '</div></div>';
                $(container).html(html).removeClass('d-none');
            }

            function collectSmsFormData() {
                var form = $('#system_settings_sms_mail_tab form');
                var data = {};
                form.find('[name^="sms_mail"]').each(function(){
                    var name = $(this).attr('name').replace('sms_mail[', '').replace(']', '');
                    if ($(this).is(':checkbox')) {
                        if ($(this).is(':checked')) data[name] = $(this).val();
                    } else if ($(this).attr('type') === 'hidden' && $(this).attr('name').indexOf('sms_mail') >= 0) {
                        if (!data.hasOwnProperty(name)) data[name] = $(this).val();
                    } else {
                        data[name] = $(this).val();
                    }
                });
                return data;
            }

            function renderBalanceCard(container, card, success, balance, errorMsg) {
                if (success) {
                    $(card).removeClass('bg-light-danger').addClass('bg-light-success');
                    $(container).html(
                        '<div class="d-flex align-items-center justify-content-center gap-3">'
                        + '<div><i class="fa fa-wallet fs-3 text-success"></i></div>'
                        + '<div class="text-start">'
                        + '<span class="text-muted fs-8 d-block">Bakiye Bilgisi</span>'
                        + '<span class="fw-bold fs-6">Hesabınızda <span class="text-success">' + balance + '</span> kredi bulunmaktadır.</span>'
                        + '</div>'
                        + '</div>'
                    );
                } else {
                    $(card).removeClass('bg-light-success').addClass('bg-light-danger');
                    $(container).html(
                        '<div class="d-flex align-items-center justify-content-center gap-2">'
                        + '<i class="fa fa-exclamation-circle text-danger"></i>'
                        + '<span class="text-danger fs-7">' + (errorMsg || 'Bakiye sorgulanamadı') + '</span>'
                        + '</div>'
                    );
                }
            }

            function fetchSmsBalance() {
                var provider = $('#smsProviderSelect').val();
                var formData = collectSmsFormData();

                if (provider === 'mutlucell') {
                    var username = formData.mutlucell_username || '';
                    var password = formData.mutlucell_password || '';
                    if (!username || !password) {
                        renderBalanceCard('#mutlucellBalanceContent', '#mutlucellBalanceCard', false, 0, 'Kullanıcı adı ve şifre giriniz');
                        return;
                    }
                    $('#mutlucellBalanceContent').html('<i class="fa fa-spinner fa-spin text-muted"></i><span class="text-muted ms-2 fs-7">Bakiye sorgulanıyor...</span>');
                    $.ajax({
                        url: '{{ route("admin.testSmsConnection") }}',
                        type: 'POST',
                        dataType: 'json',
                        data: { _token: '{{ csrf_token() }}', config: formData },
                        success: function(res) {
                            if (res.success && res.details) {
                                var match = res.details.match(/([\d.]+)/);
                                var bal = match ? match[1] : res.details;
                                renderBalanceCard('#mutlucellBalanceContent', '#mutlucellBalanceCard', true, bal, '');
                            } else {
                                renderBalanceCard('#mutlucellBalanceContent', '#mutlucellBalanceCard', false, 0, res.message || 'Bakiye sorgulanamadı');
                            }
                        },
                        error: function() {
                            renderBalanceCard('#mutlucellBalanceContent', '#mutlucellBalanceCard', false, 0, 'Bağlantı hatası');
                        }
                    });
                }

                if (provider === 'iletimerkezi') {
                    var key = formData.iletimerkezi_key || '';
                    var secret = formData.iletimerkezi_secret || '';
                    if (!key || !secret) {
                        renderBalanceCard('#iletimerkeziBalanceContent', '#iletimerkeziBalanceCard', false, 0, 'API Key ve Secret giriniz');
                        return;
                    }
                    $('#iletimerkeziBalanceContent').html('<i class="fa fa-spinner fa-spin text-muted"></i><span class="text-muted ms-2 fs-7">Bakiye sorgulanıyor...</span>');
                    $.ajax({
                        url: '{{ route("admin.testSmsConnection") }}',
                        type: 'POST',
                        dataType: 'json',
                        data: { _token: '{{ csrf_token() }}', config: formData },
                        success: function(res) {
                            if (res.success && res.details) {
                                var match = res.details.match(/([\d.]+)/);
                                var bal = match ? match[1] : res.details;
                                renderBalanceCard('#iletimerkeziBalanceContent', '#iletimerkeziBalanceCard', true, bal, '');
                            } else {
                                renderBalanceCard('#iletimerkeziBalanceContent', '#iletimerkeziBalanceCard', false, 0, res.message || 'Bakiye sorgulanamadı');
                            }
                        },
                        error: function() {
                            renderBalanceCard('#iletimerkeziBalanceContent', '#iletimerkeziBalanceCard', false, 0, 'Bağlantı hatası');
                        }
                    });
                }
            }

            setTimeout(fetchSmsBalance, 500);

            $('#smsTestConnectionBtn').on('click', function(){
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Test...');
                var formData = collectSmsFormData();
                $.ajax({
                    url: '{{ route("admin.testSmsConnection") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', config: formData },
                    success: function(res){
                        showTestResult('#smsTestResult', res.success, res.message, res.details || '');
                        fetchSmsBalance();
                    },
                    error: function(xhr){
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Bağlantı hatası';
                        showTestResult('#smsTestResult', false, msg, '');
                    },
                    complete: function(){
                        btn.prop('disabled', false).html('<i class="fa fa-plug me-1"></i>Bağlantı');
                    }
                });
            });

            $('#smsTestSendBtn').on('click', function(){
                var number = $('#smsTestNumber').val();
                var message = $('#smsTestMessage').val();
                if (!number) {
                    showTestResult('#smsTestResult', false, 'Lütfen bir telefon numarası girin.', '');
                    return;
                }
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Gönderiliyor...');
                var formData = collectSmsFormData();
                $.ajax({
                    url: '{{ route("admin.testSmsSend") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', config: formData, number: number, message: message },
                    success: function(res){
                        showTestResult('#smsTestResult', res.success, res.message, res.details || '');
                    },
                    error: function(xhr){
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gönderim hatası';
                        showTestResult('#smsTestResult', false, msg, '');
                    },
                    complete: function(){
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i>Test SMS Gönder');
                    }
                });
            });

            $('#mailTestConnectionBtn').on('click', function(){
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Test...');
                var formData = collectSmsFormData();
                $.ajax({
                    url: '{{ route("admin.testMailConnection") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', config: formData },
                    success: function(res){
                        showTestResult('#mailTestResult', res.success, res.message, res.details || '');
                    },
                    error: function(xhr){
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Bağlantı hatası';
                        showTestResult('#mailTestResult', false, msg, '');
                    },
                    complete: function(){
                        btn.prop('disabled', false).html('<i class="fa fa-plug me-1"></i>Bağlantı');
                    }
                });
            });

            $('#mailTestSendBtn').on('click', function(){
                var email = $('#mailTestAddress').val();
                var subject = $('#mailTestSubject').val();
                if (!email) {
                    showTestResult('#mailTestResult', false, 'Lütfen bir e-posta adresi girin.', '');
                    return;
                }
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Gönderiliyor...');
                var formData = collectSmsFormData();
                $.ajax({
                    url: '{{ route("admin.testMailSend") }}',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', config: formData, email: email, subject: subject },
                    success: function(res){
                        showTestResult('#mailTestResult', res.success, res.message, res.details || '');
                    },
                    error: function(xhr){
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gönderim hatası';
                        showTestResult('#mailTestResult', false, msg, '');
                    },
                    complete: function(){
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i>Test Mail Gönder');
                    }
                });
            });

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
                            $('#queuePendingText').text(d.queue_pending + ' bekleyen').attr('data-count', d.queue_pending);
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
                            setTimeout(refreshStatus, 6000);
                            setTimeout(refreshStatus, 12000);
                        } else {
                            toastr.error(res.message || 'Hata oluştu');
                        }
                    },
                    error: function(){
                        toastr.error('İşlem sırasında hata oluştu');
                    },
                    complete: function(){
                        setTimeout(function(){ btn.prop('disabled', false); }, 3000);
                    }
                });
            });

            // ── Kampanya Yönetimi ──
            var campTinyInstance = null;

            function initCampaignTinyMCE(){
                if(campTinyInstance) { try { campTinyInstance.destroy(); } catch(e){} campTinyInstance = null; }
                tinymce.init({
                    selector: '#campMailContent',
                    height: 350,
                    menubar: true,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code fullscreen',
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
                    setup: function(editor){ campTinyInstance = editor; }
                });
            }

            function destroyCampaignTinyMCE(){
                if(campTinyInstance){ try { campTinyInstance.destroy(); } catch(e){} campTinyInstance = null; }
            }

            function resetCampaignForm(){
                $('#campaignId').val('');
                $('#campName').val('');
                $('#campChannel').val('both');
                $('#campTargetType').val('all').trigger('change');
                $('#campSmsContent').val('');
                $('#campMailSubject').val('');
                $('#campMailContent').val('');
                $('#campPreviewResult').addClass('d-none');
                $('#campaignModalTitle').text('Yeni Kampanya');
            }

            $('#campTargetType').on('change', function(){
                var v = $(this).val();
                $('#campFilterUserGroup, #campFilterCategory, #campFilterProduct, #campFilterCustom').addClass('d-none');
                if(v === 'user_group') $('#campFilterUserGroup').removeClass('d-none');
                else if(v === 'product_category') $('#campFilterCategory').removeClass('d-none');
                else if(v === 'product') $('#campFilterProduct').removeClass('d-none');
                else if(v === 'custom') $('#campFilterCustom').removeClass('d-none');
            });

            $('#campChannel').on('change', function(){
                var v = $(this).val();
                if(v === 'sms'){ $('#campMailCard').addClass('d-none'); $('#campSmsCard').removeClass('d-none'); }
                else if(v === 'mail'){ $('#campSmsCard').addClass('d-none'); $('#campMailCard').removeClass('d-none'); }
                else { $('#campSmsCard, #campMailCard').removeClass('d-none'); }
            });

            $('#newCampaignBtn').on('click', function(){
                resetCampaignForm();
                initCampaignTinyMCE();
                $('#campaignModal').modal('show');
            });

            $('#campaignModal').on('hidden.bs.modal', function(){ destroyCampaignTinyMCE(); });

            $(document).on('click', '.campVar', function(){
                var v = $(this).data('var');
                var varName = String.fromCharCode(123,123) + v + String.fromCharCode(125,125);
                navigator.clipboard.writeText(varName);
                toastr.info('Değişken kopyalandı: ' + varName);
            });

            $('#campPreviewBtn').on('click', function(){
                var btn = $(this);
                var targetType = $('#campTargetType').val();
                var filters = {};
                if(targetType === 'user_group') filters.user_group_ids = $('#campUserGroupIds').val();
                else if(targetType === 'product_category') filters.category_ids = $('#campCategoryIds').val();
                else if(targetType === 'product') filters.product_ids = $('#campProductIds').val();
                else if(targetType === 'custom'){
                    var ids = $('#campUserIdsText').val().split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                    filters.user_ids = ids;
                }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Yükleniyor...');
                $.ajax({
                    url: '/netAdmin/campaigns/preview-recipients',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', target_type: targetType, target_filters: filters, channel: $('#campChannel').val() },
                    success: function(res){
                        if(res.success){
                            $('#campPreviewCount').text(res.count);
                            var html = '';
                            if(res.recipients && res.recipients.length > 0){
                                res.recipients.forEach(function(r){
                                    html += '<div class="d-flex justify-content-between border-bottom py-1">';
                                    html += '<span>' + r.name + '</span>';
                                    html += '<span class="text-muted">' + (r.email || '') + ' | ' + (r.phone || '') + '</span>';
                                    html += '</div>';
                                });
                                if(res.count > 50) html += '<div class="text-center text-muted mt-2">...ve ' + (res.count - 50) + ' kişi daha</div>';
                            } else {
                                html = '<div class="text-center text-muted">Alıcı bulunamadı</div>';
                            }
                            $('#campPreviewList').html(html);
                            $('#campPreviewResult').removeClass('d-none');
                        } else {
                            toastr.error(res.message || 'Hata');
                        }
                    },
                    error: function(){ toastr.error('Önizleme hatası'); },
                    complete: function(){ btn.prop('disabled', false).html('<i class="fa fa-eye me-2"></i>Alıcıları Önizle'); }
                });
            });

            $('#campaignForm').on('submit', function(e){
                e.preventDefault();
                if(campTinyInstance) campTinyInstance.triggerSave();
                var btn = $('#campaignSaveBtn');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                var campId = $('#campaignId').val();
                var url = campId ? '/netAdmin/campaigns/' + campId + '/update' : '/netAdmin/campaigns/store';

                var formData = {
                    _token: '{{ csrf_token() }}',
                    name: $('#campName').val(),
                    channel: $('#campChannel').val(),
                    target_type: $('#campTargetType').val(),
                    sms_content: $('#campSmsContent').val(),
                    mail_subject: $('#campMailSubject').val(),
                    mail_content: $('#campMailContent').val()
                };

                var tt = formData.target_type;
                if(tt === 'user_group') formData.user_group_ids = $('#campUserGroupIds').val();
                else if(tt === 'product_category') formData.category_ids = $('#campCategoryIds').val();
                else if(tt === 'product') formData.product_ids = $('#campProductIds').val();
                else if(tt === 'custom'){
                    formData.user_ids = $('#campUserIdsText').val().split(',').map(function(s){ return s.trim(); }).filter(Boolean);
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: formData,
                    success: function(res){
                        if(res.success){
                            toastr.success(res.message);
                            $('#campaignModal').modal('hide');
                            setTimeout(function(){ location.reload(); }, 800);
                        } else {
                            toastr.error(res.message || 'Hata');
                        }
                    },
                    error: function(xhr){
                        var msg = 'Kayıt hatası';
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        toastr.error(msg);
                    },
                    complete: function(){ btn.removeAttr('data-kt-indicator').prop('disabled', false); }
                });
            });

            $(document).on('click', '.campaignEditBtn', function(){
                var id = $(this).data('id');
                resetCampaignForm();
                $.get('/netAdmin/campaigns/' + id, function(res){
                    if(res.success){
                        var d = res.data;
                        $('#campaignId').val(d.id);
                        $('#campaignModalTitle').text('Kampanya Düzenle');
                        $('#campName').val(d.name);
                        $('#campChannel').val(d.channel).trigger('change');
                        $('#campTargetType').val(d.target_type).trigger('change');
                        var f = d.target_filters || {};
                        if(d.target_type === 'user_group' && f.user_group_ids) $('#campUserGroupIds').val(f.user_group_ids);
                        if(d.target_type === 'product_category' && f.category_ids) $('#campCategoryIds').val(f.category_ids);
                        if(d.target_type === 'product' && f.product_ids) $('#campProductIds').val(f.product_ids);
                        if(d.target_type === 'custom' && f.user_ids) $('#campUserIdsText').val(f.user_ids.join(','));
                        $('#campSmsContent').val(d.sms_content || '');
                        $('#campMailSubject').val(d.mail_subject || '');
                        $('#campMailContent').val(d.mail_content || '');
                        initCampaignTinyMCE();
                        $('#campaignModal').modal('show');
                    }
                });
            });

            $(document).on('click', '.campaignSendBtn', function(){
                var id = $(this).data('id');
                $('#sendConfirmCampaignId').val(id);
                $('#campaignSendConfirmModal').modal('show');
            });

            $('#campaignSendConfirmBtn').on('click', function(){
                var btn = $(this);
                var id = $('#sendConfirmCampaignId').val();
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.ajax({
                    url: '/netAdmin/campaigns/' + id + '/send',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res){
                        if(res.success){
                            toastr.success(res.message);
                            $('#campaignSendConfirmModal').modal('hide');
                            setTimeout(function(){ location.reload(); }, 800);
                        } else {
                            toastr.error(res.message || 'Gönderim hatası');
                        }
                    },
                    error: function(){ toastr.error('Gönderim sırasında hata oluştu'); },
                    complete: function(){ btn.removeAttr('data-kt-indicator').prop('disabled', false); }
                });
            });

            $(document).on('click', '.campaignDuplicateBtn', function(){
                var id = $(this).data('id');
                $.ajax({
                    url: '/netAdmin/campaigns/' + id + '/duplicate',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res){
                        if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 500); }
                        else toastr.error(res.message || 'Hata');
                    },
                    error: function(){ toastr.error('Kopyalama hatası'); }
                });
            });

            $('#pendingJobsModal').on('show.bs.modal', function(){
                $('#pendingJobsLoading').removeClass('d-none');
                $('#pendingJobsEmpty').addClass('d-none');
                $('#pendingJobsTableWrap').addClass('d-none');
                $('#pendingJobsBody').empty();

                $.get('/netAdmin/pending-jobs-ajax', function(res){
                    $('#pendingJobsLoading').addClass('d-none');
                    if(!res.success || !res.jobs || res.jobs.length === 0){
                        $('#pendingJobsEmpty').removeClass('d-none');
                        return;
                    }
                    var html = '';
                    res.jobs.forEach(function(j){
                        var statusBadge = j.reserved_at
                            ? '<span class="badge badge-light-warning">İşleniyor</span>'
                            : '<span class="badge badge-light-info">Bekliyor</span>';
                        html += '<tr>';
                        html += '<td class="ps-3 fw-semibold text-gray-500">' + j.id + '</td>';
                        html += '<td>';
                        html += '<span class="fw-semibold text-gray-800 d-block">' + (j.description || j.job_name) + '</span>';
                        html += '<span class="text-muted fs-8">' + j.job_name + '</span>';
                        html += '</td>';
                        html += '<td>';
                        if(j.detail) html += '<span class="text-gray-700 fs-7">' + j.detail + '</span>';
                        else html += '<span class="text-muted fs-8">-</span>';
                        html += '</td>';
                        html += '<td class="text-center">' + j.attempts + '</td>';
                        html += '<td class="text-gray-600 fs-7 text-nowrap">' + j.created_at + '</td>';
                        html += '<td class="text-center">' + statusBadge + '</td>';
                        html += '</tr>';
                    });
                    $('#pendingJobsBody').html(html);
                    $('#pendingJobsTableWrap').removeClass('d-none');
                }).fail(function(){
                    $('#pendingJobsLoading').addClass('d-none');
                    $('#pendingJobsEmpty').removeClass('d-none').find('span').text('Veriler yüklenirken hata oluştu.');
                });
            });

            $(document).on('click', '.campaignDeleteBtn', function(){
                var id = $(this).data('id');
                if(!confirm('Bu kampanyayı silmek istediğinize emin misiniz?')) return;
                $.ajax({
                    url: '/netAdmin/campaigns/' + id + '/delete',
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res){
                        if(res.success){ toastr.success(res.message); $('tr[data-id="'+id+'"]').fadeOut(300, function(){ $(this).remove(); }); }
                        else toastr.error(res.message || 'Hata');
                    },
                    error: function(){ toastr.error('Silme hatası'); }
                });
            });
        });

        // Paraşüt Settings
        $('#parasutSettingsForm').on('submit', function(e){
            e.preventDefault();
            var btn = document.getElementById('parasutSaveBtn');
            btn.setAttribute('data-kt-indicator', 'on');
            btn.disabled = true;

            $.ajax({
                url: '{{ route("netAdmin.settings.parasutSave") }}',
                type: 'POST',
                data: $(this).serialize(),
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                success: function(res){
                    btn.removeAttribute('data-kt-indicator');
                    btn.disabled = false;
                    if(res.success){
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr){
                    btn.removeAttribute('data-kt-indicator');
                    btn.disabled = false;
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Bir hata oluştu.';
                    toastr.error(msg);
                }
            });
        });

        $('#parasutTestBtn').on('click', function(){
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Test ediliyor...');
            $.ajax({
                url: '{{ route("netAdmin.settings.parasutTest") }}',
                type: 'POST',
                data: $('#parasutSettingsForm').serialize(),
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                success: function(res){
                    btn.prop('disabled', false).html('<i class="fa fa-plug me-1"></i>Bağlantıyı Test Et');
                    if(res.success){
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr){
                    btn.prop('disabled', false).html('<i class="fa fa-plug me-1"></i>Bağlantıyı Test Et');
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Bir hata oluştu.';
                    toastr.error(msg);
                }
            });
        });

        // Telegram Settings
        $('#telegramSettingsForm').on('submit', function(e){
            e.preventDefault();
            var btn = $('#telegramSaveBtn');
            btn.attr('data-kt-indicator', 'on').prop('disabled', true);
            $.ajax({
                url: '{{ route("admin.telegramSave") }}',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res){
                    if(res.success) toastr.success(res.message);
                    else toastr.error(res.message || 'Hata');
                },
                error: function(xhr){
                    toastr.error(xhr.responseJSON?.message || 'Kaydetme hatası');
                },
                complete: function(){
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                }
            });
        });

        $('#telegramTestBtn').on('click', function(){
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Gönderiliyor...');
            var form = $('#telegramSettingsForm');
            $.ajax({
                url: '{{ route("admin.telegramTest") }}',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(res){
                    if(res.success) toastr.success(res.message);
                    else toastr.error(res.message);
                },
                error: function(xhr){
                    toastr.error(xhr.responseJSON?.message || 'Bağlantı hatası');
                },
                complete: function(){
                    btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i>Test Mesajı Gönder');
                }
            });
        });

        $('#telegramFindChatId').on('click', function(){
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Aranıyor...');
            var token = $('input[name="telegram_bot_token"]').val();
            $.ajax({
                url: '{{ route("admin.telegramFindChatId") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', telegram_bot_token: token },
                dataType: 'json',
                success: function(res){
                    if(res.success){
                        $('#telegramChatIdInput').val(res.chat_id);
                        toastr.success('Chat ID bulundu: ' + res.chat_id);
                    } else {
                        toastr.warning(res.message);
                    }
                },
                error: function(xhr){
                    toastr.error(xhr.responseJSON?.message || 'Bağlantı hatası');
                },
                complete: function(){
                    btn.prop('disabled', false).html('<i class="fa fa-search me-1"></i>Chat ID Bul');
                }
            });
        });

        // === Site Settings ===
        $(document).on('submit', '#siteSettingsForm', function(e){
            e.preventDefault();
            var btn = $('#saveSiteSettingsBtn');
            $.ajax({
                url: "{{ route('admin.siteSave') }}",
                type: 'POST',
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                dataType: 'json',
                beforeSend: function(){
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Kaydediliyor...');
                },
                success: function(res){
                    if(res.success){
                        toastr.success(res.message, 'Başarılı');
                    } else {
                        toastr.error(res.message || 'Bir hata oluştu.', 'Hata');
                    }
                },
                error: function(xhr){
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Sunucu hatası.';
                    toastr.error(msg, 'Hata');
                },
                complete: function(){
                    btn.prop('disabled', false).html('<i class="fa fa-save me-2"></i>Kaydet');
                }
            });
        });
    </script>
@endsection
