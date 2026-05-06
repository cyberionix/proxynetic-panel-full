@extends("admin.template")
@section("title", __("dashboard"))
@section("css")
    <style>
        .loading {
            position: relative;
            filter: blur(3px);
            pointer-events: none;
        }
        a.card {
            transition: box-shadow 0.2s ease, transform 0.15s ease;
            cursor: pointer;
        }
        a.card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .table th.min-w-125px,
            .table th.min-w-70px { min-width: auto !important; }
            .card-header { flex-direction: column; gap: 8px; }
        }
    </style>
@endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('dashboard')"/>
@endsection
@section("master")
    <div class="row gap-5">
        <!-- begin::First Row-->
        <div class="col-12">
            <div class="row g-5">
                <div class="col-xl-4 position-relative">
                    <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3"
                         data-np-sale-report="loader">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                    </div>
                    <a href="{{route('admin.checkouts.index')}}" class="card card-flush loading text-reset text-hover-primary" data-np-sale-report="area" style="text-decoration:none;">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Currency-->
                                    <span
                                        class="fs-4 fw-semibold text-gray-500 me-1 align-self-start">{{defaultCurrencySymbol()}}</span>
                                    <!--end::Currency-->
                                    <!--begin::Amount-->
                                    <span
                                        class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2"
                                        data-np-sale-report="today-price">{{showBalance("0")}}</span>
                                    <!--end::Amount-->
                                    <!--begin::Badge-->

                                    <!--end::Badge-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">Bugün Satış (<span
                                        data-np-sale-report="today-count">0</span>)</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-2 pb-4">
                            <!--begin::Labels-->
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3 d-none">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Günlük Ortalama</div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5"
                                     data-np-sale-report="daily-avg">{{showBalance("0", true)}}</div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Bu Ay</div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5">
                                    <span class="fs-7 text-gray-500" data-np-sale-report="this-month-count">(0)</span>
                                    <span data-np-sale-report="this-month">{{showBalance("0", true)}}</span>
                                </div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Geçen Ay</div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5">
                                    <span class="fs-7 text-gray-500" data-np-sale-report="last-month-count">(0)</span>
                                    <span data-np-sale-report="last-month">{{showBalance("0", true)}}</span></div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    &nbsp;
                                </div>
                            </div>
                            <!--end::Labels-->
                        </div>
                        <!--end::Card body-->
                    </a>
                </div>
                <div class="col-xl-4 position-relative">
                    <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3"
                         data-np-customer-report="loader">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                    </div>
                    <div class="card card-flush loading" data-np-customer-report="area">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Amount-->
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2"
                                          data-np-customer-report="today">0</span>
                                    <!--end::Amount-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">Müşteri Kaydı (Bugün)</span>
                                <!--end::Subtitle-->
                            </div>
                            <div class="card-toolbar flex-column align-items-end">
                                <i class="ki-duotone ki-profile-user fs-4qx text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2 pb-4">
                            <!--begin::Labels-->
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Toplam Müşteri</div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5" data-np-customer-report="total">0
                                </div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Bu Ay</div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5" data-np-customer-report="this-month">
                                    0
                                </div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Geçen Ay</div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5" data-np-customer-report="last-month">
                                    0
                                </div>
                            </div>
                            <!--end::Labels-->
                        </div>
                        <!--end::Card body-->
                    </div>
                </div>
                <div class="col-xl-4 position-relative">
                    <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3"
                         data-np-support-report="loader">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                    </div>
                    <a href="{{route('admin.supports.index')}}" class="card card-flush loading text-reset text-hover-primary" data-np-support-report="area" style="text-decoration:none;">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Amount-->
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2"
                                          data-np-support-report="pending-supports">0</span>
                                    <!--end::Amount-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span
                                    class="text-gray-500 pt-1 fw-semibold fs-6">{{__("pending_support_request")}}</span>
                                <!--end::Subtitle-->
                            </div>
                            <div class="card-toolbar flex-column align-items-end">
                                <i class="ki-duotone ki-abstract-33 fs-4qx text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2 pb-4">
                            <!--begin::Labels-->
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Bugün <span
                                            class="text-gray-400 fs-8">(Çözümlenen)</span></div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5" data-np-support-report="today">0
                                </div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Dün <span class="text-gray-400 fs-8">(Çözümlenen)</span>
                                    </div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5" data-np-support-report="yesterday">
                                    0
                                </div>
                            </div>
                            <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500">Bu Ay <span
                                            class="text-gray-400 fs-8">(Çözümlenen)</span></div>
                                </div>
                                <div class="fw-bolder text-gray-700 text-end fs-5" data-np-support-report="this-month">
                                    0
                                </div>
                            </div>
                            <!--end::Labels-->
                        </div>
                        <!--end::Card body-->
                    </a>
                </div>
            </div>
        </div>
        <!-- end::First Row-->

        <!-- begin::Last Orders-->
        <div class="col-12 position-relative">
            <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3"
                 data-np-last-orders="loader">
                <span class="spinner-border spinner-border-sm align-middle"></span>
            </div>
            <div class="card card-flush loading" data-np-last-orders="area">
                <div class="card-header">
                    <h3 class="card-title">
                        <div>
                            <i class="fa fa-boxes-stacked fs-2x text-primary me-3"></i>
                        </div>
                        <div class="d-flex align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Son Siparişler</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Son 5 Kayıt</span>
                        </div>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{route("admin.orders.index")}}" class="btn btn-sm btn-light-primary"><i
                                class="fa fa-eye"></i> Tümünü Görüntüle</a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table align-middle table-row-bordered fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="min-w-70px">#</th>
                                <th class="min-w-125px">{{__("customer")}}</th>
                                <th class="min-w-125px">{{__("product")}} / {{__("service")}}</th>
                                <th class="min-w-125px">Teslimat Durumu</th>
                                <th class="min-w-125px">{{__("amount")}}</th>
                                <th class="min-w-70px"></th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600" data-np-last-orders="items">

                            </tbody>
                            <!--end::Table body-->
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
            </div>
        </div>
        <!-- end::Last Orders-->

        <!-- begin::Pending Support, Payments-->
        <div class="col-12">
            <div class="row g-5">
                <div class="col-xl-6 position-relative">
                    <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3"
                         data-np-pending-supports="loader">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                    </div>
                    <div class="card card-flush card-stretch loading" data-np-pending-supports="area">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title">
                                <div>
                                    <i class="ki-duotone ki-abstract-33 fs-2qx text-primary me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                                <div class="d-flex align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Bekleyen Destek Talepleri</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Son 5 Kayıt</span>
                                </div>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{route("admin.supports.index")}}" class="btn btn-sm btn-light-primary"><i class="fa fa-eye"></i> Tümünü
                                    Görüntüle</a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table class="table align-middle table-row-bordered fs-6 gy-5">
                                    <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                        <th class="min-w-50px">#</th>
                                        <th class="min-w-125px">{{__("customer")}}</th>
                                        <th class="min-w-125px">{{__("subject")}}</th>
                                        <th class="min-w-125px">{{__("status")}}</th>
                                        <th class="min-w-50px"></th>
                                    </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600" data-np-pending-supports="items">

                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 position-relative">
                    <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3"
                         data-np-upcoming-invoices="loader">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                    </div>
                    <div class="card card-flush card-stretch loading" data-np-upcoming-invoices="area">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title">
                                <div>
                                    <i class="fa fa-file-invoice fs-2x text-primary me-3"></i>
                                </div>
                                <div class="d-flex align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Ödeme Tarihi Yaklaşan Faturalar</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Son 5 Kayıt</span>
                                </div>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{route("admin.invoices.index")}}" class="btn btn-sm btn-light-primary"><i class="fa fa-eye"></i> Tümünü
                                    Görüntüle</a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table class="table align-middle table-row-bordered fs-6 gy-5">
                                    <thead>
                                    <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                        <th class="min-w-70px">#</th>
                                        <th class="min-w-125px">{{__("customer")}}</th>
                                        <th class="min-w-125px">Son Ödeme Tarihi</th>
                                        <th class="min-w-125px">{{__("amount")}}</th>
                                        <th class="min-w-70px"></th>
                                    </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600"  data-np-upcoming-invoices="items">

                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end::Pending Support, Payments-->
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $.ajax({
                type: 'GET',
                url: '{{route('admin.dashboard.getSaleReports')}}',
                data: {
                    _token: '{{csrf_token()}}',
                },
                dataType: 'json',
                beforeSend: function () {
                    $('[data-np-sale-report="loader"]').removeClass("d-none")
                    $('[data-np-sale-report="area"]').addClass("loading")
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        $('[data-np-sale-report="today-price"]').text(res.data?.today_price);
                        $('[data-np-sale-report="end-of-month-forecast"]').text(res.data?.end_of_month_forecast);
                        $('[data-np-sale-report="today-count"]').text(res.data?.today_count);
                        $('[data-np-sale-report="daily-avg"]').text(res.data?.draw_daily_avg);
                        $('[data-np-sale-report="this-month"]').text(res.data?.draw_this_month_amount);
                        $('[data-np-sale-report="this-month-count"]').text(res.data?.draw_this_month_count);
                        $('[data-np-sale-report="last-month"]').text(res.data?.draw_last_month_amount);
                        $('[data-np-sale-report="last-month-count"]').text(res.data?.draw_last_month_count);
                    } else {
                        if (res?.message) {
                            toastr.error(res.message);
                        }
                    }
                    $('[data-np-sale-report="loader"]').addClass("d-none")
                    $('[data-np-sale-report="area"]').removeClass("loading")

                }
            })

            $.ajax({
                type: 'GET',
                url: '{{route('admin.dashboard.getCustomerReports')}}',
                data: {
                    _token: '{{csrf_token()}}',
                },
                dataType: 'json',
                beforeSend: function () {
                    $('[data-np-customer-report="loader"]').removeClass("d-none")
                    $('[data-np-customer-report="area"]').addClass("loading")
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        $('[data-np-customer-report="today"]').text(res.data?.today);
                        $('[data-np-customer-report="total"]').text(res.data?.total);
                        $('[data-np-customer-report="this-month"]').text(res.data?.this_month);
                        $('[data-np-customer-report="last-month"]').text(res.data?.last_month);
                    } else {
                        if (res?.message) {
                            toastr.error(res.message);
                        }
                    }
                    $('[data-np-customer-report="loader"]').addClass("d-none")
                    $('[data-np-customer-report="area"]').removeClass("loading")

                }
            })

            $.ajax({
                type: 'GET',
                url: '{{route('admin.dashboard.getSupportReports')}}',
                data: {
                    _token: '{{csrf_token()}}',
                },
                dataType: 'json',
                beforeSend: function () {
                    $('[data-np-support-report="loader"]').removeClass("d-none")
                    $('[data-np-support-report="area"]').addClass("loading")
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        $('[data-np-support-report="pending-supports"]').text(res.data?.total_pending);
                        $('[data-np-support-report="today"]').text(res.data?.today);
                        $('[data-np-support-report="yesterday"]').text(res.data?.yesterday);
                        $('[data-np-support-report="this-month"]').text(res.data?.this_month);
                    } else {
                        if (res?.message) {
                            toastr.error(res.message);
                        }
                    }
                    $('[data-np-support-report="loader"]').addClass("d-none")
                    $('[data-np-support-report="area"]').removeClass("loading")

                }
            })

            $.ajax({
                type: 'GET',
                url: '{{route('admin.dashboard.getLastOrders')}}',
                data: {
                    _token: '{{csrf_token()}}',
                    limit: 5
                },
                dataType: 'json',
                beforeSend: function () {
                    $('[data-np-last-orders="loader"]').removeClass("d-none")
                    $('[data-np-last-orders="area"]').addClass("loading")
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        let itemArea = $('[data-np-last-orders="items"]')
                        res.data.map((item) => {
                            itemArea.append(`<tr><td>${item.id}</td><td>${item.user}</td><td>${item.product_name}</td><td>${item.delivery_status}</td><td>${item.amount}</td><td>${item.redirect_url}</td></tr>`)
                        })
                    } else {
                        if (res?.message) {
                            toastr.error(res.message);
                        }
                    }
                    $('[data-np-last-orders="loader"]').addClass("d-none")
                    $('[data-np-last-orders="area"]').removeClass("loading")
                }
            })

            $.ajax({
                type: 'GET',
                url: '{{route('admin.dashboard.getPendingSupports')}}',
                data: {
                    _token: '{{csrf_token()}}',
                    limit: 5
                },
                dataType: 'json',
                beforeSend: function () {
                    $('[data-np-pending-supports="loader"]').removeClass("d-none")
                    $('[data-np-pending-supports="area"]').addClass("loading")
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        let itemArea = $('[data-np-pending-supports="items"]')
                        res.data.map((item) => {
                            itemArea.append(`<tr><td>${item.id}</td><td>${item.user}</td><td>${item.subject}</td><td>${item.status}</td><td>${item.redirect_url}</td></tr>`)
                        })
                    } else {
                        if (res?.message) {
                            toastr.error(res.message);
                        }
                    }
                    $('[data-np-pending-supports="loader"]').addClass("d-none")
                    $('[data-np-pending-supports="area"]').removeClass("loading")
                }
            })

            $.ajax({
                type: 'GET',
                url: '{{route('admin.dashboard.getUpcomingInvoices')}}',
                data: {
                    _token: '{{csrf_token()}}',
                    limit: 5
                },
                dataType: 'json',
                beforeSend: function () {
                    $('[data-np-upcoming-invoices="loader"]').removeClass("d-none")
                    $('[data-np-upcoming-invoices="area"]').addClass("loading")
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        let itemArea = $('[data-np-upcoming-invoices="items"]')
                        res.data.map((item) => {
                            itemArea.append(`<tr><td>${item.id}</td><td>${item.user}</td><td>${item.due_date}</td><td>${item.amount}</td><td>${item.redirect_url}</td></tr>`)
                        })
                    } else {
                        if (res?.message) {
                            toastr.error(res.message);
                        }
                    }
                    $('[data-np-upcoming-invoices="loader"]').addClass("d-none")
                    $('[data-np-upcoming-invoices="area"]').removeClass("loading")
                }
            })
        })
    </script>
@endsection
