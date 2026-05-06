@extends("admin.template")
@section("title", $user->full_name)
@section("css")
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
    <style>
        .phoneArea .iti {
            width: 100%;
        }
    </style>
@endsection
@section("description", "")
@section("breadcrumb")
    <x-admin.bread-crumb :data="[$user->fullName, __('customers') => route('admin.users.index')]"/>
@endsection
@section("keywords", "")
@section("master")
    @if($user->is_banned)
        <div class="alert alert-danger d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <!--begin::Icon-->
                <i class="ki-duotone ki-notification-bing fs-2hx text-danger me-4 mb-5 mb-sm-0"><span
                        class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <!--end::Icon-->

                <div class="fw-semibold fs-5">
                    Yasaklı kullanıcı
                </div>
            </div>
            <button class="btn btn-danger btn-sm userBanBtn"
                    data-text="Devam etmeniz halinde kullanıcı yasağı kaldırılacaktır. Devam etmek istediğinize emin misiniz?"
                    data-url="{{route("admin.users.unbanAccount", ["user" => $user->id])}}"><i
                    class="fa fa-ban"></i>Yasaklamayı Kaldır
            </button>
        </div>
    @endif
    <!--begin::Navbar-->
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div>
                <!--begin::Details-->
                <div class="d-flex flex-wrap flex-sm-nowrap">
                    <!--begin: Pic-->
                    <div class="me-7 mb-4">
                        <div class="symbol symbol-175px symbol-lg-160px symbol-fixed position-relative">
                            <div class="symbol-label fs-3hx bg-light-secondary text-black text-uppercase">
                                {{ mb_substr($user->first_name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                    <!--end::Pic-->
                    <!--begin::Info-->
                    <div class="flex-grow-1">
                        <!--begin::Title-->
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                            <!--begin::User-->
                            <div class="d-flex flex-column">
                                <!--begin::Name-->
                                <div class="d-flex align-items-center mb-2">
                                    <div href="#" class="text-gray-900 fs-2 fw-bold me-1">
                                        {{$user->full_name}}
                                    </div>
                                </div>
                                <!--end::Name-->
                                <!--begin::Info-->
                                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                    <div class="d-flex align-items-center text-gray-500 mb-2">
                                        <i class="ki-duotone ki-sms fs-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>{{$user->email}}</div>
                                </div>
                                <!--end::Info-->
                            </div>
                            <!--end::User-->
                            <!--begin::Actions-->
                            <div class="d-flex my-4 gap-1">

                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"><i
                                        class="fa fa-edit"></i>{{__("edit")}}</button>
                                @if(!$user->is_banned)
                                    <button class="btn btn-danger btn-sm userBanBtn"
                                            data-text="Devam etmeniz halinde kullanıcı hesabına giriş yapamaz. Kullanıcıyı yasaklamak istediğinize emin misiniz?"
                                            data-url="{{route("admin.users.banAccount", ["user" => $user->id])}}"><i
                                            class="fa fa-ban"></i>Yasakla
                                    </button>
                                @endif
                                <button class="btn btn-danger btn-sm deleteBtn"
                                        data-url="{{route('admin.users.delete', ['user' => $user->id])}}"><i
                                        class="fa fa-trash"></i>{{__("delete")}}</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Title-->
                        <!--begin::Stats-->
                        <div class="d-flex flex-wrap flex-stack">
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-column flex-grow-1 pe-8">
                                <!--begin::Stats-->
                                <div class="d-flex flex-wrap">
                                    <!--begin::Stat-->
                                    <div
                                        class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-3 mb-3">
                                        <!--begin::Number-->
                                        <div class="d-flex align-items-center">
                                            <div class="fs-3 fw-bold">
                                                {{showBalance($user->balance, true)}}
                                            </div>
                                        </div>
                                        <!--end::Number-->
                                        <!--begin::Label-->
                                        <div
                                            class="fw-semibold fs-6 text-gray-500">{{__("credit_balance")}}</div>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Stat-->
                                    <!--begin::Stat-->
                                    <div
                                        class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-3 mb-3">
                                        <!--begin::Number-->
                                        <div class="d-flex align-items-center">
                                            <div class="fs-3 fw-bold">
                                                {{showBalance($stats["totalCollectAmount"], true)}}
                                            </div>
                                        </div>
                                        <!--end::Number-->
                                        <!--begin::Label-->
                                        <div
                                            class="fw-semibold fs-6 text-gray-500">{{__("total_collection_amount")}}</div>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Stat-->
                                    <!--begin::Stat-->
                                    <div
                                        class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-3 mb-3">
                                        <!--begin::Number-->
                                        <div class="d-flex align-items-center">
                                            <div class="fs-3 fw-bold">{{$stats["joinDate"]}}</div>
                                        </div>
                                        <!--end::Number-->
                                        <!--begin::Label-->
                                        <div
                                            class="fw-semibold fs-6 text-gray-500">{{__("join_date")}}</div>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Stat-->
                                    <!--begin::Stat-->
                                    <div
                                        class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-3 mb-3">
                                        <!--begin::Number-->
                                        <div class="d-flex align-items-center">
                                            <div class="fs-6 fw-bold">{{$stats["lastSeenAt"]}}</div>
                                        </div>
                                        <!--end::Number-->
                                        <!--begin::Label-->
                                        <div
                                            class="fw-semibold fs-6 text-gray-500">{{__("last_seen_at")}}</div>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Stat-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->
                <div class="d-flex justify-content-between gap-10 flex-wrap">
                    <div class="d-flex gap-1 flex-wrap">
                        <div>
                            <div class="bg-light-info p-4 rounded-2">
                                <label class="form-label fw-bolder mb-2">{{__("customer_no")}}</label>
                                <div class="text-gray-500 fw-semibold fs-6">ID - {{$user->id}}</div>
                            </div>
                            <a href="{{route('admin.orders.index',['user_id' => $user->id])}}"><button class="btn btn-sm btn-light-primary mt-3"><i class="fa fa-plus fs-2"></i> Sipariş Oluştur</button></a>
                            <a href="{{route('admin.invoices.create',['user_id' => $user->id])}}"><button  class="btn btn-sm btn-light-success mt-3"><i class="fa fa-plus fs-2"></i> Fatura Oluştur</button></a>


                        </div>
                        <div>
                            <div class="bg-light-info p-4 rounded-2">
                                <label class="form-label fw-bolder mb-2">
                                    {{__("tc_identity_number")}}
                                </label>
                                <div class="text-gray-500 fw-semibold fs-6">
                                    <div class="d-flex align-items-center">
                                        {{$user->identity_number ?? ""}}
                                        @if($user->identity_number_verified_at)
                                            <i class="fa fa-circle-check fs-1 ms-2 cursor-default text-success"
                                               data-bs-toggle="tooltip" title="{{__("verified")}}"></i>
                                        @else
                                            <i class="fa fa-circle-xmark fs-1 ms-2 cursor-default text-danger"
                                               data-bs-toggle="tooltip" title="{{__("not_verified")}}"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="bg-light-info p-4 rounded-2">
                                <label class="form-label fw-bolder mb-2">
                                    KYC
                                </label>
                                <div class="text-gray-500 fw-semibold fs-6">
                                    @if($user->kyc?->verified_at)
                                        <span class="badge badge-sm badge-success">{{__($user->kyc->status)}}</span>
                                    @else
                                        <span
                                            class="badge badge-sm badge-danger">{{__($user->kyc->status ?? __("not_verified"))}}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="bg-light-info p-4 rounded-2">
                                <label class="form-label fw-bolder mb-2">{{__("phone_number")}}</label>
                                <div
                                    class="text-gray-500 fw-semibold fs-6">{{phoneMask($user->phone) ?: "-"}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <button class="btn btn-light-success btn-sm mb-1 primaryWalletBalanceBtn"><i
                                class="fa fa-wallet"></i> {{__("add_:name", ["name" => __("credit_balance")])}}
                        </button>
                        <button class="btn btn-light-danger btn-sm mb-1 resetPassBtn"><i
                                class="fa fa-lock"></i> {{__("reset_password")}}
                        </button>
                        <button class="btn btn-secondary btn-sm accountLoginBtn"><i
                                class="fa fa-sign-in"></i> {{__("login")}}
                        </button>
                    </div>
                </div>
            </div>
            <!--begin:::Tabs-->
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold gap-5 mt-10">
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4 active" data-bs-toggle="tab"
                       href="#user_info_tab">{{__("customer_information")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#security_tab">{{__("security")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item position-relative">
                    <a class="nav-link mx-0 text-active-primary pb-4 " data-bs-toggle="tab"
                       href="#orders_tab">{{__("orders")}}

                    </a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item position-relative">
                    <a class="nav-link mx-0 text-active-primary pb-4 " data-bs-toggle="tab"
                       href="#checkouts_tab">{{__("checkouts")}}

                    </a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#invoices_tab">{{__("invoices")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#addresses_tab">{{__("addresses")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#activity_logs_tab">Kullanıcı Hareketleri</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#email_history_tab">{{__("email_history")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#sms_history_tab">{{__("sms_history")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#kyc_tab">KYC</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#balance_activity_tab">Bakiye Hareketleri</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#sessions_tab">Oturum Kayıtları</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4" data-bs-toggle="tab"
                       href="#proxy_logs_tab">Proxy Logları</a>
                </li>
                <!--end:::Tab item-->
            </ul>
            <!--end:::Tabs-->
        </div>
    </div>
    <!--end::Navbar-->
    <!--begin:::Tab content-->
    <div class="tab-content" id="myTabContent">
        <!--begin:::Tab pane-->
        <div class="tab-pane fade show active" id="user_info_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9 bg-light-">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("customer_information")}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0 pb-10">
                    <div class="row g-5">
                        <div class="col-xl-6">
                            <!--begin::Label-->
                            <label class="form-label required">{{__("first_name")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" required class="form-control form-control-lg" name="first_name"
                                   value="{{$user->first_name}}" disabled>
                            <!--end::Input-->
                        </div>
                        <div class="col-xl-6">
                            <!--begin::Label-->
                            <label class="form-label required">{{__("last_name")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" required class="form-control form-control-lg" name="last_name"
                                   value="{{$user->last_name}}" disabled>
                            <!--end::Input-->
                        </div>
                        <div class="col-xl-6">
                            <!--begin::Label-->
                            <label class="form-label">{{__("birth_date")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <x-portal.form-elements.date-input name="birth_date"
                                                               attr="disabled"
                                                               :value="$user->birth_date"/>
                            <!--end::Input-->
                        </div>
                        <div class="col-xl-6">
                            <!--begin::Label-->
                            <label class="form-label">{{__("tc_identity_number")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control form-control-lg" name="identity_number"
                                   disabled
                                   value="{{$user->identity_number}}">
                            <!--end::Input-->
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="security_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9 bg-light-">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("security")}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0 pb-10">
                    <form id="securityForm" action="{{route("admin.users.updateSecurity", ["user" => $user->id])}}"
                          class="row g-5">
                        <div class="col-12">
                            <div class="separator separator-dashed"></div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-xl-6 d-flex align-items-center">
                                    <!--begin::Label-->
                                    <div class="me-5">
                                        <!--begin::Label-->
                                        <div class="me-5">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-semibold">Proxy/VPN Engeli</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <div class="fs-7 fw-semibold text-muted">
                                                Proxy VPN ile girişleri engeller.
                                            </div>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Label-->
                                    </div>
                                    <!--end::Label-->
                                </div>
                                <div class="col-xl-6 d-flex">
                                    <!--begin::Switch-->
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <!--begin::Input-->
                                        <input class="form-check-input" name="security[is_cant_vpn]"
                                               type="checkbox" value="1"
                                            {{$user?->security?->is_cant_vpn == 1 ? "checked" : ""}}>
                                        <!--end::Input-->

                                        <!--begin::Label-->
                                        <span class="form-check-label fw-semibold text-muted">
                                            {{__("active")}}
                                        </span>
                                        <!--end::Label-->
                                    </label>
                                    <!--end::Switch-->
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="separator separator-dashed"></div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <div class="me-5">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold">Ödeme Yöntemlerini Engelle</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <div class="fs-7 fw-semibold text-muted">Sadece seçeceğiniz yöntemleri
                                            kullanarak ödeme yapabilir.
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Label-->
                                </div>
                                <div class="col-xl-6 d-flex flex-column justify-content-center gap-5">
                                    <div>
                                        <!--begin::Switch-->
                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                            <!--begin::Input-->
                                            <input class="form-check-input" name="security[is_limit_payment_methods]"
                                                   type="checkbox" value="1"
                                                {{$user?->security?->is_limit_payment_methods == 1 ? "checked" : ""}}>
                                            <!--end::Input-->

                                            <!--begin::Label-->
                                            <span class="form-check-label fw-semibold text-muted">
                                            {{__("active")}}
                                        </span>
                                            <!--end::Label-->
                                        </label>
                                        <!--end::Switch-->
                                    </div>
                                    <div data-np-security="payment-methods-area"
                                         style="{{$user?->security?->is_limit_payment_methods == 0 ? "display:none" : ""}}">
                                        <label class="form-label fs-7">İzin vermek istediğiniz ödeme yöntemlerini
                                            seçiniz</label>
                                        <x-admin.form-elements.payment-methods-select name="security[payment_methods][]"
                                                                                      :selectedOption="$user->security->payment_methods"
                                                                                      customClass="form-select-sm mw-350px"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="separator separator-dashed"></div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <div class="me-5">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold">Destek Talebi Sınırlandırma</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <div class="fs-7 fw-semibold text-muted">Mevcut destek talepleri çözümlenmeden
                                            yeni talep açamaz.
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Label-->
                                </div>
                                <div class="col-xl-6 d-flex">
                                    <!--begin::Switch-->
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <!--begin::Input-->
                                        <input class="form-check-input" name="security[is_limited_support]"
                                               type="checkbox" value="1"
                                            {{$user?->security?->is_limited_support == 1 ? "checked" : ""}}>
                                        <!--end::Input-->

                                        <!--begin::Label-->
                                        <span class="form-check-label fw-semibold text-muted">
                                            {{__("active")}}
                                        </span>
                                        <!--end::Label-->
                                    </label>
                                    <!--end::Switch-->
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="separator separator-dashed"></div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <div class="me-5">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold">Destek Talebi Engeli</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <div class="fs-7 fw-semibold text-muted">Yeni destek talebi oluşturamaz.</div>
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Label-->
                                </div>
                                <div class="col-xl-6 d-flex">
                                    <!--begin::Switch-->
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <!--begin::Input-->
                                        <input class="form-check-input" name="security[is_no_support]"
                                               type="checkbox" value="1"
                                            {{$user?->security?->is_no_support == 1 ? "checked" : ""}}>
                                        <!--end::Input-->

                                        <!--begin::Label-->
                                        <span class="form-check-label fw-semibold text-muted">
                                            {{__("active")}}
                                        </span>
                                        <!--end::Label-->
                                    </label>
                                    <!--end::Switch-->
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="separator separator-dashed mb-5"></div>
                            <button type="submit" class="btn btn-primary">
                                <!--begin::Indicator label-->
                                <span class="indicator-label"><i class="fa fa-floppy-disk me-1"></i>{{__("save_changes")}}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                    </form>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="orders_tab" role="tabpanel">
            <!--begin::Navbar-->
            <div class="card mb-2">
                <div class="card-body py-0">
                    <!--begin:::Tabs-->
                    <ul id="header-nav"
                        class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mt-3 gap-8">
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 orderStatusTab active"
                               data-bs-toggle="tab"
                               data-key=""
                               href="javascript:void(0);">{{__("all")}}</a>
                        </li>
                        <!--end:::Tab item-->
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 orderStatusTab"
                               data-bs-toggle="tab"
                               data-key="ACTIVE"
                               href="javascript:void(0);">Aktif</a>
                        </li>
                        <!--end:::Tab item-->
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 orderStatusTab"
                               data-bs-toggle="tab"
                               data-key="CANCELLED"
                               href="javascript:void(0);">İptal Edildi</a>
                        </li>
                        <!--end:::Tab item-->
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 orderStatusTab"
                               data-bs-toggle="tab"
                               data-key="PENDING"
                               href="javascript:void(0);">Onay Bekliyor</a>
                        </li>
                        <!--end:::Tab item-->
                    </ul>
                    <!--end:::Tabs-->
                </div>
            </div>
            <!--end::Navbar-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <div class="card-header border-0">
                    <div class="card-title">
                        <h2>{{__("orders")}}</h2>
                    </div>
                    <div class="card-toolbar">
                        <div id="orderBulkBar" class="d-none d-flex align-items-center gap-2">
                            <span class="fw-semibold text-gray-700 me-1"><span id="orderSelectedCount">0</span> seçili</span>
                            <button class="btn btn-sm btn-light-success order-bulk-btn" data-action="mark_active">
                                <i class="fa fa-check me-1"></i>Aktif Yap
                            </button>
                            <button class="btn btn-sm btn-light-secondary order-bulk-btn" data-action="mark_cancelled">
                                <i class="fa fa-ban me-1"></i>İptal Et
                            </button>
                            <button class="btn btn-sm btn-light-danger order-bulk-btn" data-action="delete">
                                <i class="fa fa-trash me-1"></i>Sil
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body py-0">
                    <table id="ordersTable"
                           class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="orderCheckAll" />
                                </div>
                            </th>
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("product")}}</th>
                            <th class="min-w-125px">{{__("amount")}}</th>
                            <th class="min-w-125px">{{__("date")}}</th>
                            <th class="min-w-125px">{{__('status')}}</th>
                            <th class="min-w-125px">{{__("action")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="checkouts_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("checkouts")}}</h2>
                        <!--start::Info-->
                        <div class="ms-7 d-flex flex-wrap gap-5">
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div
                                    class="border border-1 border-gray-400 w-15px h-15px bg-light-primary me-1"></div>
                                {{__("new")}}
                            </div>
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div
                                    class="border border-1 border-gray-400 w-15px h-15px bg-light-warning me-1"></div>
                                {{__("waiting")}}
                            </div>
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div
                                    class="border border-1 border-gray-400 w-15px h-15px bg-light-info me-1"></div>
                                3D
                            </div>
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div
                                    class="border border-1 border-gray-400 w-15px h-15px bg-light-success me-1"></div>
                                {{__("completed")}}
                            </div>
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div
                                    class="border border-1 border-gray-400 w-15px h-15px bg-light-danger me-1"></div>
                                {{__("failed")}}
                            </div>
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div
                                    class="border border-1 border-gray-400 w-15px h-15px bg-secondary me-1"></div>
                                {{__("cancelled")}}
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body py-0">
                    <!--begin::Table-->
                    <table id="checkoutsTable"
                           class="table align-middle table-row-dashed table-hover cursor-pointer fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("payment_type")}}</th>
                            <th class="min-w-125px">{{__("payment_date")}}</th>
                            <th class="min-w-125px">{{__("amount")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->

            <!--end::Modals-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="invoices_tab" role="tabpanel">
            <div class="card pt-4 mb-6 mb-xl-9">
                <div class="card-header border-0">
                    <div class="card-title">
                        <h2>{{__("invoices")}}</h2>
                    </div>
                    <div class="card-toolbar">
                        <div id="invBulkBar" class="d-none d-flex align-items-center gap-2">
                            <span class="fw-semibold text-gray-700 me-1"><span id="invSelectedCount">0</span> seçili</span>
                            <button class="btn btn-sm btn-light-success inv-bulk-btn" data-action="mark_paid">
                                <i class="fa fa-check me-1"></i>Ödendi Yap
                            </button>
                            <button class="btn btn-sm btn-light-warning inv-bulk-btn" data-action="mark_pending">
                                <i class="fa fa-clock me-1"></i>Bekliyor Yap
                            </button>
                            <button class="btn btn-sm btn-light-secondary inv-bulk-btn" data-action="mark_cancelled">
                                <i class="fa fa-ban me-1"></i>İptal Et
                            </button>
                            <button class="btn btn-sm btn-light-danger inv-bulk-btn" data-action="delete">
                                <i class="fa fa-trash me-1"></i>Sil
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body py-0">
                    <table id="invoiceTable"
                           class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="invCheckAll" />
                                </div>
                            </th>
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("invoice_date")}}</th>
                            <th class="min-w-125px">{{__("amount")}}</th>
                            <th class="min-w-125px">Paraşüt</th>
                            <th class="min-w-125px">{{__("action")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="sales_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("sale_invoices")}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body py-0">
                    <!--begin::Table-->
                    <table id="salesTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("status")}}</th>
                            <th class="min-w-125px">{{__("amount")}}</th>
                            <th class="min-w-125px">{{__("date")}}</th>
                            <th class="min-w-125px">{{__("process")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                    <div class="h-200px"></div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="addresses_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("addresses")}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-xxl-6">
                            <a href="javascript:void(0);"
                               data-url="{{route("admin.users.addresses.store", ["user" => $user->id])}}"
                               class="card card-dashed h-xl-100 fs-5 fw-bold  p-6 bg-light-primary d-flex flex-center addAddressBtn">
                                <div>
                                    <i class="fa fa-plus text-gray-900"></i> {{__("add_:name", ["name" => __("address")])}}
                                </div>
                            </a>
                        </div>
                        @foreach($user->addresses as $address)
                            <div class="col-xxl-6">
                                <div class="card card-dashed h-xl-100 p-6">
                                    <div class="d-flex justify-content-between">
                                        <div class="fs-5 fw-bold d-flex align-items-center">
                                            {{$address->title}}
                                            @if($address->is_default_invoice_address || $address->is_default_delivery_address)
                                                @php
                                                    $text = "";
                                                    if ($address->is_default_invoice_address && $address->is_default_delivery_address) $text = "Varsayılan fatura ve teslimat adresi";
                                                    else if($address->is_default_invoice_address) $text = "Varsayılan fatura adresi";
                                                    else if($address->is_default_delivery_address) $text = "Varsayılan teslimat adresi";
                                                @endphp
                                                <span class="ms-1" data-bs-toggle="tooltip"
                                                      title="{{$text}}">⭐</span>
                                            @endif
                                            @if($address->is_primary)
                                                <span
                                                    class="badge badge-light-success ms-3">{{__("selected")}}</span>
                                            @endif
                                        </div>
                                        <!--begin::Actions-->
                                        <div class="d-flex align-items-center py-2">
                                            <!--begin::Edit-->
                                            <div
                                                class="btn btn-icon btn-sm btn-color-gray-500 btn-active-icon-danger me-2 deleteAddressBtn"
                                                data-url="{{route("admin.users.addresses.delete", ["address" => $address->id])}}"
                                                data-bs-toggle="tooltip" data-bs-dismiss="click"
                                                title="{{__("delete")}}">
                                                <i class="ki-duotone ki-trash fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>
                                            </div>
                                            <!--end::Edit-->
                                            <!--begin::Edit-->
                                            <div
                                                class="btn btn-icon btn-sm btn-color-gray-500 btn-active-icon-primary editAddressBtn"
                                                data-update-url="{{route("admin.users.addresses.update", ["address" => $address->id])}}"
                                                data-find-url="{{route("admin.users.addresses.find", ["address" => $address->id])}}"
                                                data-bs-toggle="tooltip" data-bs-dismiss="click"
                                                title="{{__("edit")}}">
                                                <i class="ki-duotone ki-pencil fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Edit-->
                                        </div>
                                        <!--end::Actions-->
                                    </div>
                                    @if($address->invoice_type == "CORPORATE")
                                        <div>
                                            {!! $address->drawInvoiceType("badge-sm mb-2") !!}
                                        </div>
                                    @endif
                                    <div class="fs-7 fw-semibold text-gray-600">
                                        {!! nl2br($address->address) !!}
                                        <br>
                                        {{$address->district?->title}} / {{$address->city?->title}}
                                        <br>
                                        {{$address->country?->title}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->
            <div class="modal fade" id="primaryAddressModal" data-bs-backdrop="static"
                 data-bs-keyboard="false" tabindex="-1"
                 aria-hidden="true">
                <!--begin::Modal dialog-->
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <!--begin::Modal content-->
                    <div class="modal-content">
                        <!--begin::Modal header-->
                        <div class="modal-header" id="primaryAddressModal_header"
                             data-add-text="{{__("add_:name", ["name" => __("address")])}}"
                             data-edit-text="{{__("edit_:name", ["name" => __("address")])}}">
                            <!--begin::Modal title-->
                            <h2></h2>
                            <!--begin::Close-->
                            <div class="btn btn-sm btn-icon btn-active-color-primary"
                                 data-bs-dismiss="modal">
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
                            <form id="primaryAddressForm">
                                @csrf
                                <!--begin::Scroll-->
                                <div class="scroll-y me-n7 pe-7" id="primaryAddressModal_scroll"
                                     data-kt-scroll="true"
                                     data-kt-scroll-activate="{default: false, lg: true}"
                                     data-kt-scroll-max-height="auto"
                                     data-kt-scroll-dependencies="#primaryAddressModal_header"
                                     data-kt-scroll-wrappers="#primaryAddressModal_scroll"
                                     data-kt-scroll-offset="300px">
                                    <div class="row g-3">
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_invoice_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_invoice_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_delivery_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_delivery_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("address_title")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text"
                                                   class="form-control form-control-lg "
                                                   name="title" required>
                                            <!--end::Input-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("city")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.city-select name="city_id"
                                                                               dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("district")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.district-select name="district_id"
                                                                                   dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("address")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Textarea-->
                                            <textarea name="address" cols="30" rows="3"
                                                      class="form-control "
                                                      required></textarea>
                                            <!--end::Textarea-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("invoice_type")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Radio group-->
                                            <div class="btn-group w-100" data-kt-buttons="true"
                                                 data-kt-buttons-target="[data-kt-button]">
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea active"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type" checked
                                                           value="INDIVIDUAL"/>
                                                    <!--end::Input-->
                                                    {{__("individual")}}
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type"
                                                           value="CORPORATE"/>
                                                    <!--end::Input-->
                                                    {{__("corporate")}}
                                                </label>
                                                <!--end::Radio-->
                                            </div>
                                            <!--end::Radio group-->
                                        </div>
                                        <div class="col-xl-6 individual-area" style="">
                                            <!--begin::Label-->
                                            <label class="form-label required">TC Kimlik Numarası</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="identity_number" required
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_number")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_number"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_office")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_office"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("company_name")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="company_name"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                    </div>
                                </div>
                                <!--end::Scroll-->
                                <!--begin::Actions-->
                                <div class="d-flex flex-center flex-row-fluid pt-12">
                                    <button type="reset" class="btn btn-light me-3"
                                            data-bs-dismiss="modal">{{__("cancel")}}</button>
                                    <button type="submit" class="btn btn-primary">
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
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="activity_logs_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>Kullanıcı Hareketleri</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <table id="activityLogsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("action")}}</th>
                            <th class="min-w-125px">Response</th>
                            <th class="min-w-125px">İşlem Tarihi</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="email_history_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("email_history")}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <table id="emailLogTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">Konu</th>
                            <th class="min-w-125px">{{__("email")}}</th>
                            <th class="min-w-125px">{{__("date")}}</th>
                            <th class="min-w-125px">{{__("status")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="sms_history_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{__("sms_history")}}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <table id="smsLogTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">Mesaj</th>
                            <th class="min-w-125px">{{__("phone_number")}}</th>
                            <th class="min-w-125px">{{__("date")}}</th>
                            <th class="min-w-125px">{{__("status")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="kyc_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>KYC</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    @if($user->is_force_kyc)
                        <div class="row g-8">
                            <div class="col-12">
                                <button class="btn btn-primary forceKycBtn"
                                        data-text="KYC zorunluluğunu geri almak istediğinize emin misiniz?"
                                        data-url="{{route("admin.users.forceKycPassive", ["user" => $user->id])}}"><i
                                        class="fa fa-rotate-left fs-3"></i> Zorunluluğu geri al
                                </button>
                            </div>
                            <div class="col-12">
                                <div class="mb-2">
                                    <span class="fw-bolder text-gray-800 fs-5">Durum:</span>
                                    <span class="badge badge-secondary badge-lg">{{__($user->kyc->status)}}</span>
                                </div>
                                @if($user->kyc->status != "WAITING_FOR_DOCS")
                                    <div class="row g-5 mt-8">
                                        <div class="col-xl-4">
                                            <!--begin::Label-->
                                            <label class="form-label fw-bold required">Ön Yüz</label>
                                            <!--end::Label-->
                                            <div>
                                                <div>
                                                    <a target="_blank"
                                                       href="{{route('admin.users.kyc.images.cardFrontSide', ['user' => $user->id, 'is_url' => true])}}"
                                                       class="btn btn-sm btn-primary mb-5"><i
                                                            class="fa fa-eye me-1"></i>{{__("view")}}</a>
                                                </div>
                                                <img class="mh-200px mw-200px" alt="Ön Yüz"
                                                     src="{{ route('admin.users.kyc.images.cardFrontSide', ['user' => $user->id]) }}">

                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <!--begin::Label-->
                                            <label class="form-label fw-bold required">Arka Yüz</label>
                                            <!--end::Label-->
                                            <div>
                                                <div>
                                                    <a target="_blank"
                                                       href="{{route('admin.users.kyc.images.cardBackSide', ['user' => $user->id])}}"
                                                       class="btn btn-sm btn-primary mb-5"><i
                                                            class="fa fa-eye me-1"></i>{{__("view")}}</a>
                                                </div>
                                                <img class="mh-200px mw-200px" alt="Arka Yüz"
                                                     src="{{ route('admin.users.kyc.images.cardBackSide', ['user' => $user->id]) }}">
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <!--begin::Label-->
                                            <label class="form-label fw-bold required">Özçekim</label>
                                            <!--end::Label-->
                                            <div>
                                                <div>
                                                    <a target="_blank"
                                                       href="{{route('admin.users.kyc.images.selfie', ['user' => $user->id])}}"
                                                       class="btn btn-sm btn-primary mb-5"><i
                                                            class="fa fa-eye me-1"></i>{{__("view")}}</a>
                                                </div>
                                                <img class="mh-200px mw-200px" alt="Özçekim"
                                                     src="{{ route('admin.users.kyc.images.selfie', ['user' => $user->id]) }}">
                                            </div>
                                        </div>
                                        <div class="col-12 text-center mt-15">
                                            @if($user->kyc->status != "CONFIRMED")
                                                <button class="btn btn-primary checkKycBtn"
                                                        data-text="KYC onaylandı olarak kayededilecektir, devam etmek istediğinize emin misiniz?"
                                                        data-url="{{route("admin.users.confirmedKyc", ["user" => $user->id])}}">
                                                    Onayla
                                                </button>
                                            @endif
                                            @if($user->kyc->status != "NOT_CONFIRMED")
                                                <button class="btn btn-danger checkKycBtn"
                                                        data-text="KYC reddedildi olarak kayededilecektir, devam etmek istediğinize emin misiniz?"
                                                        data-url="{{route("admin.users.notConfirmedKyc", ["user" => $user->id])}}">
                                                    Reddet
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <button class="btn btn-primary forceKycBtn"
                                data-text="KYC doğrulamasını zorunlu tutmak istediğinize emin misiniz?"
                                data-url="{{route("admin.users.forceKycActive", ["user" => $user->id])}}">Doğrulamaya
                            Zorla
                        </button>
                    @endif
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->
            <div class="modal fade" id="primaryAddressModal" data-bs-backdrop="static"
                 data-bs-keyboard="false" tabindex="-1"
                 aria-hidden="true">
                <!--begin::Modal dialog-->
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <!--begin::Modal content-->
                    <div class="modal-content">
                        <!--begin::Modal header-->
                        <div class="modal-header" id="primaryAddressModal_header"
                             data-add-text="{{__("add_:name", ["name" => __("address")])}}"
                             data-edit-text="{{__("edit_:name", ["name" => __("address")])}}">
                            <!--begin::Modal title-->
                            <h2></h2>
                            <!--begin::Close-->
                            <div class="btn btn-sm btn-icon btn-active-color-primary"
                                 data-bs-dismiss="modal">
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
                            <form id="primaryAddressForm">
                                @csrf
                                <!--begin::Scroll-->
                                <div class="scroll-y me-n7 pe-7" id="primaryAddressModal_scroll"
                                     data-kt-scroll="true"
                                     data-kt-scroll-activate="{default: false, lg: true}"
                                     data-kt-scroll-max-height="auto"
                                     data-kt-scroll-dependencies="#primaryAddressModal_header"
                                     data-kt-scroll-wrappers="#primaryAddressModal_scroll"
                                     data-kt-scroll-offset="300px">
                                    <div class="row g-3">
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_invoice_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_invoice_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_delivery_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_delivery_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("address_title")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text"
                                                   class="form-control form-control-lg "
                                                   name="title" required>
                                            <!--end::Input-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("city")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.city-select name="city_id"
                                                                               dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("district")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.district-select name="district_id"
                                                                                   dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("address")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Textarea-->
                                            <textarea name="address" cols="30" rows="3"
                                                      class="form-control "
                                                      required></textarea>
                                            <!--end::Textarea-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("invoice_type")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Radio group-->
                                            <div class="btn-group w-100" data-kt-buttons="true"
                                                 data-kt-buttons-target="[data-kt-button]">
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea active"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type" checked
                                                           value="INDIVIDUAL"/>
                                                    <!--end::Input-->
                                                    {{__("individual")}}
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type"
                                                           value="CORPORATE"/>
                                                    <!--end::Input-->
                                                    {{__("corporate")}}
                                                </label>
                                                <!--end::Radio-->
                                            </div>
                                            <!--end::Radio group-->
                                        </div>
                                        <div class="col-xl-6 individual-area" style="">
                                            <!--begin::Label-->
                                            <label class="form-label required">TC Kimlik Numarası</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="identity_number" required
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_number")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_number"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_office")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_office"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("company_name")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="company_name"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                    </div>
                                </div>
                                <!--end::Scroll-->
                                <!--begin::Actions-->
                                <div class="d-flex flex-center flex-row-fluid pt-12">
                                    <button type="reset" class="btn btn-light me-3"
                                            data-bs-dismiss="modal">{{__("cancel")}}</button>
                                    <button type="submit" class="btn btn-primary">
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
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="balance_activity_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>Bakiye Hareketleri</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <table id="balanceActivityTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("amount")}}</th>
                            <th class="min-w-125px">{{__("date")}}</th>
                            <th class="min-w-125px">{{__("action")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->
            <div class="modal fade" id="primaryAddressModal" data-bs-backdrop="static"
                 data-bs-keyboard="false" tabindex="-1"
                 aria-hidden="true">
                <!--begin::Modal dialog-->
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <!--begin::Modal content-->
                    <div class="modal-content">
                        <!--begin::Modal header-->
                        <div class="modal-header" id="primaryAddressModal_header"
                             data-add-text="{{__("add_:name", ["name" => __("address")])}}"
                             data-edit-text="{{__("edit_:name", ["name" => __("address")])}}">
                            <!--begin::Modal title-->
                            <h2></h2>
                            <!--begin::Close-->
                            <div class="btn btn-sm btn-icon btn-active-color-primary"
                                 data-bs-dismiss="modal">
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
                            <form id="primaryAddressForm">
                                @csrf
                                <!--begin::Scroll-->
                                <div class="scroll-y me-n7 pe-7" id="primaryAddressModal_scroll"
                                     data-kt-scroll="true"
                                     data-kt-scroll-activate="{default: false, lg: true}"
                                     data-kt-scroll-max-height="auto"
                                     data-kt-scroll-dependencies="#primaryAddressModal_header"
                                     data-kt-scroll-wrappers="#primaryAddressModal_scroll"
                                     data-kt-scroll-offset="300px">
                                    <div class="row g-3">
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_invoice_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_invoice_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_delivery_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_delivery_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("address_title")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text"
                                                   class="form-control form-control-lg "
                                                   name="title" required>
                                            <!--end::Input-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("city")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.city-select name="city_id"
                                                                               dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("district")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.district-select name="district_id"
                                                                                   dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("address")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Textarea-->
                                            <textarea name="address" cols="30" rows="3"
                                                      class="form-control "
                                                      required></textarea>
                                            <!--end::Textarea-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("invoice_type")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Radio group-->
                                            <div class="btn-group w-100" data-kt-buttons="true"
                                                 data-kt-buttons-target="[data-kt-button]">
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea active"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type" checked
                                                           value="INDIVIDUAL"/>
                                                    <!--end::Input-->
                                                    {{__("individual")}}
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type"
                                                           value="CORPORATE"/>
                                                    <!--end::Input-->
                                                    {{__("corporate")}}
                                                </label>
                                                <!--end::Radio-->
                                            </div>
                                            <!--end::Radio group-->
                                        </div>
                                        <div class="col-xl-6 individual-area" style="">
                                            <!--begin::Label-->
                                            <label class="form-label required">TC Kimlik Numarası</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="identity_number" required
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_number")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_number"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_office")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_office"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("company_name")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="company_name"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                    </div>
                                </div>
                                <!--end::Scroll-->
                                <!--begin::Actions-->
                                <div class="d-flex flex-center flex-row-fluid pt-12">
                                    <button type="reset" class="btn btn-light me-3"
                                            data-bs-dismiss="modal">{{__("cancel")}}</button>
                                    <button type="submit" class="btn btn-primary">
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
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="sessions_tab" role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" data-sessions-table-action="search"
                                   class="form-control  w-250px ps-13"
                                   placeholder="{{__("search_in_table")}}"/>
                        </div>
                        <!--end::Search-->
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <table id="sessionsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px">#</th>
                            <th class="min-w-125px">IP</th>
                            <th class="min-w-125px">Oturum Açılış Tarihi</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->
            <div class="modal fade" id="primaryAddressModal" data-bs-backdrop="static"
                 data-bs-keyboard="false" tabindex="-1"
                 aria-hidden="true">
                <!--begin::Modal dialog-->
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <!--begin::Modal content-->
                    <div class="modal-content">
                        <!--begin::Modal header-->
                        <div class="modal-header" id="primaryAddressModal_header"
                             data-add-text="{{__("add_:name", ["name" => __("address")])}}"
                             data-edit-text="{{__("edit_:name", ["name" => __("address")])}}">
                            <!--begin::Modal title-->
                            <h2></h2>
                            <!--begin::Close-->
                            <div class="btn btn-sm btn-icon btn-active-color-primary"
                                 data-bs-dismiss="modal">
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
                            <form id="primaryAddressForm">
                                @csrf
                                <!--begin::Scroll-->
                                <div class="scroll-y me-n7 pe-7" id="primaryAddressModal_scroll"
                                     data-kt-scroll="true"
                                     data-kt-scroll-activate="{default: false, lg: true}"
                                     data-kt-scroll-max-height="auto"
                                     data-kt-scroll-dependencies="#primaryAddressModal_header"
                                     data-kt-scroll-wrappers="#primaryAddressModal_scroll"
                                     data-kt-scroll-offset="300px">
                                    <div class="row g-3">
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_invoice_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_invoice_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Checkbox-->
                                            <label class="form-check form-check-custom ">
                                                <input class="form-check-input"
                                                       name="default_delivery_address" type="checkbox"
                                                       checked value="1"/>
                                                <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_delivery_address")}}
                                        </span>
                                            </label>
                                            <!--end::Checkbox-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("address_title")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text"
                                                   class="form-control form-control-lg "
                                                   name="title" required>
                                            <!--end::Input-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("city")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.city-select name="city_id"
                                                                               dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("district")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <x-admin.form-elements.district-select name="district_id"
                                                                                   dropdownParent="#primaryAddressModal"/>
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("address")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Textarea-->
                                            <textarea name="address" cols="30" rows="3"
                                                      class="form-control "
                                                      required></textarea>
                                            <!--end::Textarea-->
                                        </div>
                                        <div class="col-xl-12">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("invoice_type")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Radio group-->
                                            <div class="btn-group w-100" data-kt-buttons="true"
                                                 data-kt-buttons-target="[data-kt-button]">
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea active"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type" checked
                                                           value="INDIVIDUAL"/>
                                                    <!--end::Input-->
                                                    {{__("individual")}}
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio"
                                                           name="invoice_type"
                                                           value="CORPORATE"/>
                                                    <!--end::Input-->
                                                    {{__("corporate")}}
                                                </label>
                                                <!--end::Radio-->
                                            </div>
                                            <!--end::Radio group-->
                                        </div>
                                        <div class="col-xl-6 individual-area" style="">
                                            <!--begin::Label-->
                                            <label class="form-label required">TC Kimlik Numarası</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="identity_number" required
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_number")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_number"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="form-label required">{{__("tax_office")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="tax_office"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                        <div class="col-xl-6 corporate-area" style="display: none;">
                                            <!--begin::Label-->
                                            <label
                                                class="form-label required">{{__("company_name")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Select-->
                                            <input type="text" name="company_name"
                                                   class="form-control form-control ">
                                            <!--end::Select-->
                                        </div>
                                    </div>
                                </div>
                                <!--end::Scroll-->
                                <!--begin::Actions-->
                                <div class="d-flex flex-center flex-row-fluid pt-12">
                                    <button type="reset" class="btn btn-light me-3"
                                            data-bs-dismiss="modal">{{__("cancel")}}</button>
                                    <button type="submit" class="btn btn-primary">
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
        </div>
        <!--end:::Tab pane-->
        <!--begin:::Tab pane-->
        <div class="tab-pane fade" id="proxy_logs_tab" role="tabpanel">
            <div class="card pt-4 mb-6 mb-xl-9">
                <div class="card-header border-0">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Proxy Logları</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-50px">#</th>
                            <th class="min-w-100px">Sipariş</th>
                            <th class="min-w-100px">İşlem</th>
                            <th class="min-w-150px">Tunnel ID'ler</th>
                            <th>Proxy Detayları</th>
                            <th class="min-w-80px">Not</th>
                            <th class="min-w-125px">Tarih</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @php
                            $proxyLogs = \App\Models\ProxyLog::where('user_id', $user->id)
                                ->orderByDesc('id')
                                ->limit(200)
                                ->get();
                        @endphp
                        @forelse($proxyLogs as $pLog)
                            <tr>
                                <td>{{ $pLog->id }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', ['order' => $pLog->order_id]) }}" class="text-primary fw-bold">
                                        #{{ $pLog->order_id }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $actionLabels = [
                                            'DELIVER' => ['Teslimat', 'badge-light-success'],
                                            'REVOKE' => ['Geri Alma', 'badge-light-danger'],
                                            'REPLACE_DELETE' => ['Proxy Değiştirme (Silme)', 'badge-light-warning'],
                                        ];
                                        $label = $actionLabels[$pLog->action] ?? [$pLog->action, 'badge-light-info'];
                                    @endphp
                                    <span class="badge {{ $label[1] }}">{{ $label[0] }}</span>
                                </td>
                                <td>
                                    @if(is_array($pLog->tunnel_ids))
                                        <span class="text-muted fs-7">{{ implode(', ', $pLog->tunnel_ids) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(is_array($pLog->proxy_details) && count($pLog->proxy_details) > 0)
                                        <a href="javascript:void(0)" class="btn btn-sm btn-light-primary"
                                           onclick="$(this).next('.proxy-log-details').toggle()">
                                            {{ count($pLog->proxy_details) }} proxy göster
                                        </a>
                                        <div class="proxy-log-details mt-2" style="display:none; max-height:200px; overflow-y:auto;">
                                            <table class="table table-sm table-bordered fs-7">
                                                <thead>
                                                <tr>
                                                    <th>Tunnel</th>
                                                    <th>IP</th>
                                                    <th>Port</th>
                                                    <th>User</th>
                                                    <th>Pass</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($pLog->proxy_details as $pd)
                                                    <tr>
                                                        <td>{{ $pd['tunnel_id'] ?? '-' }}</td>
                                                        <td>{{ $pd['ip'] ?? '-' }}</td>
                                                        <td>{{ $pd['port'] ?? '-' }}</td>
                                                        <td>{{ $pd['username'] ?? '-' }}</td>
                                                        <td>{{ $pd['password'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $pLog->note ?? '-' }}</td>
                                <td>{{ $pLog->created_at?->format('d.m.Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Henüz proxy log kaydı yok.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end:::Tab pane-->
    </div>
    <!--end:::Tab content-->
    <!--begin::Modals-->
    <x-admin.modals.primary-user-modal modalId="editUserModal"
                                       :data="$user"
                                       :url="route('admin.users.update', ['user' => $user->id])"
                                       :modalTitle="__('custom_blank_create', ['name' => __('customer')])"
                                       formId="userForm"/>
    <div class="modal fade" id="primaryWalletBalanceModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered modal-md">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>Kullanıcı Bakiye Yönetimi</h2>
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
                <form id="primaryWalletBalanceForm"
                      data-input-url="{{route("admin.users.balance.input", ["user" => $user->id])}}"
                      data-output-url="{{route("admin.users.balance.output", ["user" => $user->id])}}"
                      class="modal-body py-lg-10 px-lg-15">
                    @csrf
                    <div class="row g-5">
                        <div class="col-12">
                            <label class="form-label required">İşlem Tipi</label>
                            <x-admin.form-elements.select name='type'
                                                          :options="[['label' => __('input'), 'value' => 'INPUT'],['label' => __('output'), 'value' => 'OUTPUT']]"
                                                          selectedOption="INPUT"
                                                          :hideSearch="true"/>
                        </div>
                        <div class="col-12">
                            <label class="form-label required">Tutar</label>
                            <!--begin::Input group-->
                            <div class="input-group mb-5">
                                <span class="input-group-text">₺</span>
                                <input type="text" class="form-control priceInput" name="amount" placeholder="0,00"
                                       required>
                            </div>
                            <!--end::Input group-->
                        </div>
                        <div class="col-12 text-center mt-5">
                            <!--begin::Actions-->
                            <div class="d-flex flex-center flex-row-fluid pt-6">
                                <button type="reset" class="btn btn-light me-3"
                                        data-bs-dismiss="modal">{{__("cancel")}}</button>
                                <button type="submit" class="btn btn-primary">
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
                        </div>
                    </div>
                </form>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <x-admin.modals.checkout-detail-modal id="checkoutDetailModal"/>
    <!--end::Modals-->
@endsection
@section("js")
    <script src="{{asset("js/plugins/intl-tel-input/intlTelInput.js")}}"></script>
    <script>
        $(document).ready(function () {

            console.log($("#editUserModal"))
            const userId = "{{$user->id}}";

            let input = document.querySelector(".phoneInput");
            const iti = window.intlTelInput(input, itiOptions("phone"));
            iti.setNumber("{{$user?->phone ?? ''}}");
            $(document).on("submit", "#userForm", function (e) {
                e.preventDefault()
                let form = $("#userForm"),
                    formData = new FormData(this);

                $.ajax({
                    type: 'POST',
                    url: form.data('url'),
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            $("#editUserModal").modal("hide");
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => window.location.reload())
                            $("#editUserModal").modal("hide");
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                            propSubmitButton(form.find("button[type='submit']"), 0);
                        }
                    }
                })
            })
            $(document).on("click", ".deleteBtn", function () {
                let url = $(this).data("url");

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: "Müşteriyi silmek istediğinize emin misiniz?",
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
                                _token: "{{csrf_token()}}"
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    }).then((r) => window.location.href = res.redirectUrl)
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
                    }
                });
            })
            $(document).on("click", ".primaryWalletBalanceBtn", function () {
                $("#primaryWalletBalanceModal").modal("show");
            });
            $(document).on("submit", "#primaryWalletBalanceForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    formData = new FormData(this),
                    url = form.find("[name='type']").val() === "INPUT" ? form.data("input-url") : form.data("output-url");

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            alerts.success.fire({
                                text: res?.message ?? "",
                            }).then((r) => window.location.reload())
                        } else {
                            alerts.error.fire({
                                text: res?.message ?? "{{__('form_has_errors')}}",
                            });
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })
            $(document).on("click", ".userBanBtn, .userUnbanBtn", function () {
                let url = $(this).data("url"),
                    text = $(this).data("text");
                alerts.confirm.fire({
                    text: text,
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload());
                                } else {
                                    alerts.error.fire({
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })
            $(document).on("click", ".accountLoginBtn", function () {
                let btn = $(this);
                $.ajax({
                    type: "POST",
                    url: "{{route("admin.users.accountLogin", ["user"=> $user->id])}}",
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    beforeSend: function () {
                        propSubmitButton(btn, 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            alerts.success.fire({
                                text: res?.message ?? "",
                            });

                            setTimeout((r) => window.open(res?.redirectUrl), 800)
                        } else {
                            alerts.error.fire({
                                text: res?.message ?? ""
                            })
                        }
                        setTimeout((r) => propSubmitButton(btn, 0), 3500)
                    }
                })
            })
            $(document).on('click', '.resetPassBtn', function () {
                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: "Yeni parola, {{$user->phone}} telefon numarasına SMS olarak iletilecektir. Emin misiniz?",
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: 'POST',
                            url: '{{route('admin.users.resetPassword')}}',
                            dataType: 'json',
                            data: {
                                id: {{$user->id}},
                                _token: '{{csrf_token()}}'
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
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
                    }
                })


            })

            // <--START::Security -->
            $(document).on('change', '[name="security[is_limit_payment_methods]"]', function () {
                let element = $(this),
                    area = $("[data-np-security='payment-methods-area']");

                if (element.is(":checked")) {
                    area.show(300);
                } else {
                    area.hide(300);
                }
            })
            $(document).on("submit", "#securityForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    btn = form.find("button[type='submit']");

                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(btn, 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            alerts.success.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                            }).then((r) => window.location.reload())
                        } else {
                            alerts.error.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                        propSubmitButton(btn, 0);
                    }
                })
            })
            // <--END::Security -->


            // <--START::Orders -->
            var ordersTable = $("#ordersTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: false, targets: [0, 6] },
                    { orderable: true, targets: [1, 2, 3, 4, 5] }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.orders.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                        d.status = $(".orderStatusTab.active").data("key")
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('#orderCheckAll').prop('checked', false);
                orderUpdateBulk();
            });

            function orderGetIds() {
                var ids = [];
                $('#ordersTable .bulk-check-order:checked').each(function() { ids.push($(this).val()); });
                return ids;
            }
            function orderUpdateBulk() {
                var ids = orderGetIds();
                $('#orderSelectedCount').text(ids.length);
                ids.length > 0 ? $('#orderBulkBar').removeClass('d-none') : $('#orderBulkBar').addClass('d-none');
            }
            $(document).on('change', '#orderCheckAll', function() {
                $('#ordersTable .bulk-check-order').prop('checked', $(this).is(':checked'));
                orderUpdateBulk();
            });
            $(document).on('change', '#ordersTable .bulk-check-order', function() {
                if (!$(this).is(':checked')) $('#orderCheckAll').prop('checked', false);
                orderUpdateBulk();
            });
            $(document).on('click', '.order-bulk-btn', function() {
                var action = $(this).data('action');
                var ids = orderGetIds();
                if (ids.length === 0) return;
                var msgs = {
                    'mark_active': ids.length + ' siparişi aktif olarak işaretlemek istediğinize emin misiniz?',
                    'mark_cancelled': ids.length + ' siparişi iptal etmek istediğinize emin misiniz?',
                    'delete': ids.length + ' siparişi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.'
                };
                Swal.fire({
                    title: 'Toplu İşlem',
                    text: msgs[action] || 'Emin misiniz?',
                    icon: action === 'delete' ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, uygula',
                    cancelButtonText: 'Vazgeç',
                    confirmButtonColor: action === 'delete' ? '#dc3545' : '#3085d6',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.orders.bulkAction') }}",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}", ids: ids, action: action },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({ title: 'Başarılı', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
                                    ordersTable.draw();
                                } else {
                                    Swal.fire({ title: 'Hata', text: res.message, icon: 'error' });
                                }
                            }
                        });
                    }
                });
            });

            $(document).on("click", ".orderStatusTab", function () {
                ordersTable.draw();
            })
            // <--END::Orders -->
            // <--START::Checkouts -->
            var checkoutsTable = $("#checkoutsTable").DataTable({
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
                        orderable: !0, targets: 3
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.checkouts.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('#checkoutsTable > tbody tr').each(function (index, item) {
                    let bg = $(item).closest("tr").find('td:first span').data('bg');
                    $(item).addClass('bg-' + bg)
                })
            });
            $(document).on("click", "#checkoutsTable tbody tr", function () {
                let id = $(this).find('td:first span').data('id'),
                    modal = $("#checkoutDetailModal"),
                    url = `{{ route('admin.checkouts.find', ['checkout' => '__checkout_placeholder__']) }}`;
                url = url.replace('__checkout_placeholder__', id);

                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            modal.attr("data-id", res.data.id);
                            modal.find(".user").attr("href", res.data.user_detail_url);
                            modal.find(".user").text(`${res.data.user.first_name} ${res.data.user.last_name}`);
                            if (res.data.type == "TRANSFER" && res.data.status == "WAITING_APPROVAL") {
                                modal.find(".paymentNotify").removeClass("d-none");
                            } else {
                                modal.find(".paymentNotify").addClass("d-none");
                            }


                            modal.find(".invoice").text("#" + res.data?.invoice?.invoice_number);
                            modal.find(".invoice").attr("href", res.data?.invoice_detail_url);
                            modal.find(".amount").text(res.data.amount);
                            modal.find(".paymentDate").text(res.data.paid_at ?? "-");
                            modal.find(".paymentType").text(res.data.type);
                            modal.modal("show");
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
            $(document).on("click", ".paymentStatusUpdateBtn", function () {
                let type = $(this).data("type"),
                    id = $(this).closest("#checkoutDetailModal").attr("data-id"),
                    url = `{{ route('admin.checkouts.paymentStatusUpdate', ['checkout' => '__checkout_placeholder__']) }}`;
                url = url.replace('__checkout_placeholder__', id);

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: type === "COMPLETED" ? "Ödemeyi onaylamak istediğinize emin misiniz?" : "Ödemeyi reddetmek istediğinize emin misiniz?",
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
                                type: type
                            },
                            beforeSend: function () {
                                Swal.fire({
                                    icon: "warning",
                                    title: 'Lütfen bekleyiniz',
                                    html: 'Ödeme bildirimi onaylanıyor..',
                                    didOpen: () => {
                                        Swal.showLoading()
                                    },
                                    allowOutsideClick: 0
                                })
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    }).then((r) => $("#checkoutDetailModal").modal("hide"))
                                    t.draw();
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
                    }
                });
            })
            // <--END::Checkouts -->

            // <--START::Invoices -->
            var invTable = $("#invoiceTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: false, targets: [0, 5] },
                    { orderable: true, targets: [1, 2, 3, 4] }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.invoices.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('#invCheckAll').prop('checked', false);
                invUpdateBulk();
            });

            function invGetIds() {
                var ids = [];
                $('#invoiceTable .bulk-check:checked').each(function() { ids.push($(this).val()); });
                return ids;
            }
            function invUpdateBulk() {
                var ids = invGetIds();
                $('#invSelectedCount').text(ids.length);
                ids.length > 0 ? $('#invBulkBar').removeClass('d-none') : $('#invBulkBar').addClass('d-none');
            }
            $(document).on('change', '#invCheckAll', function() {
                $('#invoiceTable .bulk-check').prop('checked', $(this).is(':checked'));
                invUpdateBulk();
            });
            $(document).on('change', '#invoiceTable .bulk-check', function() {
                if (!$(this).is(':checked')) $('#invCheckAll').prop('checked', false);
                invUpdateBulk();
            });
            $(document).on('click', '.inv-bulk-btn', function() {
                var action = $(this).data('action');
                var ids = invGetIds();
                if (ids.length === 0) return;
                var msgs = {
                    'mark_paid': ids.length + ' faturayı ödendi olarak işaretlemek istediğinize emin misiniz?',
                    'mark_pending': ids.length + ' faturayı bekliyor olarak işaretlemek istediğinize emin misiniz?',
                    'mark_cancelled': ids.length + ' faturayı iptal etmek istediğinize emin misiniz?',
                    'delete': ids.length + ' faturayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.'
                };
                Swal.fire({
                    title: 'Toplu İşlem',
                    text: msgs[action] || 'Emin misiniz?',
                    icon: action === 'delete' ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, uygula',
                    cancelButtonText: 'Vazgeç',
                    confirmButtonColor: action === 'delete' ? '#dc3545' : '#3085d6',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.invoices.bulkAction') }}",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}", ids: ids, action: action },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({ title: 'Başarılı', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
                                    invTable.draw();
                                } else {
                                    Swal.fire({ title: 'Hata', text: res.message, icon: 'error' });
                                }
                            }
                        });
                    }
                });
            });
            // <--END::Invoices -->

            /*<!-- START::Addresses-->*/
            $(document).on("click", ".addAddressBtn", function () {
                let modal = $("#primaryAddressModal"),
                    url = $(this).data("url"),
                    form = $("#primaryAddressForm"),
                    header = $("#primaryAddressModal_header");

                form.find("[name='default_invoice_address']").prop("checked", true)
                form.find("[name='default_delivery_address']").prop("checked", true)
                form.find("[name='title']").val("").trigger("change");
                form.find("[name='city_id']").val("").trigger("change");
                form.find("[name='district_id']").val("").trigger("change");
                form.find("[name='address']").val("").trigger("change");
                form.find(".invoiceTypeArea:first").trigger("click");
                form.find("[name='tax_number']").val("").trigger("change");
                form.find("[name='tax_office']").val("").trigger("change");
                form.find("[name='company_name']").val("").trigger("change");

                form.attr("action", url);
                header.find("h2").text(header.data("add-text"));
                modal.modal("show");
            })
            $(document).on("click", "#primaryAddressForm .invoiceTypeArea", function () {
                let form = $("#primaryAddressForm");
                if ($(this).find("[name='invoice_type']").val() == "INDIVIDUAL") {
                    form.find(".individual-area").find('input').prop('disabled', false);
                    form.find(".individual-area").find('input').prop('required', true);
                    form.find(".corporate-area").find('input').prop('required', false);
                    form.find(".corporate-area").find('input').prop('disabled', true);
                    form.find(".individual-area").fadeIn();
                    form.find(".corporate-area").hide();
                } else {
                    form.find(".corporate-area").find('input').prop('disabled', false);
                    form.find(".corporate-area").find('input').prop('required', true);
                    form.find(".individual-area").find('input').prop('required', false);
                    form.find(".individual-area").find('input').prop('disabled', true);
                    form.find(".corporate-area").fadeIn();
                    form.find(".individual-area").hide();
                }
            })
            $(document).on("click", ".editAddressBtn", function () {
                let modal = $("#primaryAddressModal"),
                    findUrl = $(this).data("find-url"),
                    updateUrl = $(this).data("update-url"),
                    form = $("#primaryAddressForm"),
                    header = $("#primaryAddressModal_header");

                $.ajax({
                    type: 'POST',
                    url: findUrl,
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            form.find("[name='default_invoice_address']").prop("checked", res.data.is_default_invoice_address)
                            form.find("[name='default_delivery_address']").prop("checked", res.data.is_default_delivery_address)

                            form.find("[name='title']").val(res.data.title)
                            form.find("[name='city_id']").val(res.data.city_id).trigger("change");
                            form.find("[name='district_id']").append(`<option value="${res.data?.district?.id}" selected="selected">${res.data?.district?.title}</option>`).trigger("change");
                            form.find("[name='address']").val(res.data.address)
                            if (res.data.invoice_type == "CORPORATE") {
                                form.find("[name='identity_number']").val("")
                                form.find("[name='tax_number']").val(res.data.tax_number)
                            } else {
                                form.find("[name='tax_number']").val("")
                                form.find("[name='identity_number']").val(res.data.tax_number)
                            }
                            form.find("[name='tax_office']").val(res.data.tax_office)
                            form.find("[name='company_name']").val(res.data.company_name)
                            form.find(`[name='invoice_type'][value='${res.data.invoice_type}']`).closest("label").trigger("click")
                            modal.modal("show");
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
                form.attr("action", updateUrl);
                header.find("h2").text(header.data("edit-text"));
            })
            $(document).on("click", ".deleteAddressBtn", function () {
                let url = $(this).data("url");
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
                                _token: "{{csrf_token()}}"
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then((r) => window.location.reload());
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "",
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
            $(document).on("submit", "#primaryAddressForm", function (e) {
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
                        propSubmitButton(form.find("button[type='submit']"), 1);
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
                            }).then((r) => window.location.reload());
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })
            /*<!-- END::Addresses-->*/

            /*<!-- START::SmsLogs-->*/
            var smsLogTable = $("#smsLogTable").DataTable({
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
                        orderable: !0, targets: 3
                    },
                    {
                        orderable: !0, targets: 4
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.smsLogs.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });
            /*<!-- END::SmsLogs-->*/

            /*<!-- START::ActivityLogs-->*/
            $("#activityLogsTable").DataTable({
                order: [],
                columnDefs: [
                    {
                        orderable: !0, targets: 0
                    },
                    {
                        orderable: !1, targets: 1
                    },
                    {
                        orderable: !1, targets: 2
                    },
                    {
                        orderable: !0, targets: 3
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.activityLogs.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });
            /*<!-- END::ActivityLogs-->*/

            /*<!-- START::EmailLogs-->*/
            $("#emailLogTable").DataTable({
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
                        orderable: !0, targets: 3
                    },
                    {
                        orderable: !0, targets: 4
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.emailLogs.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
                "rowCallback": function (row, data, index) {
                    $(row).addClass('bg-hover-light-primary');
                }
            }).on("draw", function () {
                KTMenu.createInstances();
            });
            /*<!-- END::EmailLogs-->*/
            $(document).on('click', '#emailLogTable tr', function () {
                let id = $(this).find('span[data-id]').attr('data-id');
                $.ajax({
                    type: 'POST',
                    url: "{{ route("admin.emailLogs.find") }}/" + id,
                    data: {
                        _token: '{{csrf_token()}}'
                    },
                    success: function (res) {
                        if (res.success === true) {
                            $('.email-receipt').html(res.data.receipt);
                            $('.email-date').html(res.data.created_at);
                            $('.email-subject').html(res.data.subject);
                            $('.email-body').html(res.data.body);
                        }
                        $('#emailDetailsModal').modal('show');

                    }
                })
            })


            /*<!-- START::KYC-->*/
            $(document).on("click", ".forceKycBtn, .checkKycBtn", function () {
                let url = $(this).data("url"),
                    text = $(this).data("text");
                alerts.confirm.fire({
                    text: text,
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        text: res?.message ?? "",
                                    }).then((r) => window.location.reload());
                                } else {
                                    alerts.error.fire({
                                        text: res?.message ?? ""
                                    })
                                }
                            }
                        })
                    }
                })
            })
            /*<!-- END::KYC-->*/

            /*<!-- START::Balance Activity-->*/
            var balanceActivityTable = $("#balanceActivityTable").DataTable({
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
                    "url": "{{route("admin.users.balance.ajax")}}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('[data-bs-toggle="tooltip"]').tooltip();

                $('#balanceActivityTable > tbody tr').each(function (index, item) {
                    let bg = $(item).closest("tr").find('td:first span').data('bg');
                    $(item).addClass('bg-' + bg)
                })
            });
            /*<!-- END::Balance Activity-->*/

            /*<!-- START::Sessions-->*/
            var sessionsTable = $("#sessionsTable").DataTable({
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
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{route("admin.userSessions.ajax")}}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.userId = userId
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-sessions-table-action="search"]').addEventListener("keyup", (function (e) {
                sessionsTable.search(e.target.value).draw();
            }));
            /*<!-- END::Sessions-->*/
        })
    </script>
@endsection
