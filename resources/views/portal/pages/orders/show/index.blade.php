@extends("portal.template")
@section("title", $order->product_data['name'])
@section("breadcrumb")
    <x-portal.bread-crumb
        :data="[$order->product_data['name'], __('my_products_and_services') => route('portal.orders.index')]"/>
@endsection
@section("css")
    <style>
        .swal2-restart-popup {
            border-radius: 1rem !important;
            padding: 1.75rem 1.5rem 1.5rem !important;
            box-shadow: 0 1rem 3rem rgba(24, 28, 50, 0.12) !important;
        }
        .swal2-restart-popup .swal2-title {
            font-size: 1.35rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.02em !important;
            color: #181c32 !important;
            padding-top: 0.25rem !important;
            margin-bottom: 1rem !important;
        }
        .swal2-restart-popup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .swal2-restart-popup .swal2-icon.swal2-success {
            border-color: rgba(25, 135, 84, 0.35) !important;
            color: #198754 !important;
        }
        .swal-restart-body {
            text-align: left;
        }
        .swal-restart-lead {
            font-size: 1.02rem;
            font-weight: 600;
            color: #3f4254;
            line-height: 1.6;
            margin: 0 0 1rem 0;
        }
        .swal-restart-panel {
            background: linear-gradient(145deg, #f8fffb 0%, #eef8f2 55%, #ecfdf3 100%);
            border: 1px solid rgba(25, 135, 84, 0.22);
            border-radius: 0.75rem;
            padding: 1rem 1.15rem 1.15rem;
        }
        .swal-restart-panel .label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #198754;
            margin: 0 0 0.4rem 0;
        }
        .swal-restart-panel .main {
            font-size: 0.95rem;
            color: #181c32;
            line-height: 1.55;
            margin: 0 0 0.35rem 0;
        }
        .swal-restart-panel .main strong {
            color: #157347;
            font-weight: 700;
        }
        .swal-restart-panel .hint {
            font-size: 0.82rem;
            color: #7e8299;
            line-height: 1.45;
            margin: 0;
        }
    </style>
@endsection
@section("master")
    <div class="row g-5">
        @if($order->upgradePrices())
            <div class="col-12">
                <div class="card mb-5">
                    <div class="card-body text-center bg-light-primary py-6">
                        <div>
                            <i class="fa fa-rocket fs-5x mb-2 text-primary"></i>
                        </div>
                        <button class="btn btn-lg btn-primary" data-bs-toggle="modal"
                                data-bs-target="#upgradeModal">{{__("upgrade_now")}}</button>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-12">
            <div class="row g-5">
                <div class="col-12 col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light-secondary min-h-60px h-60px">
                            <h3 class="card-title fw-bold">{{__("general_information")}}</h3>
                            <div class="card-toolbar">
                                @if($order->lastInvoiceItem)
                                    <a target="_blank"
                                       href="{{route("portal.invoices.show", ["invoice" => $order->lastInvoiceItem->invoice_id])}}"
                                       class="btn btn-primary btn-sm"><i class="fa fa-file-invoice fs-4"></i> Fatura
                                        Görüntüle</a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-row-bordered gy-3">
                                <tbody>
                                <tr>
                                    <td class="text-gray-800 fw-bold fs-6">{{__("service_group")}}</td>
                                    <td class="text-gray-600 fw-semibold fs-6">{{@$order->product_data["category"]["name"]}}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-800 fw-bold fs-6">{{__("service_name")}}</td>
                                    <td class="text-gray-600 fw-semibold fs-6">{{@$order->product_data["name"]}}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-800 fw-bold fs-6">{{__("service_status")}}</td>
                                    <td class="text-gray-600 fw-semibold fs-6">{!! $order->drawStatus() !!}</td>
                                </tr>
                                <tr>
                                    <td class="text-gray-800 fw-bold fs-6">{{__("payment_period")}}</td>
                                    <td class="text-gray-600 fw-semibold fs-6">{{$order->getPaymentPeriod()}}</td>
                                </tr>
                                @if($order->activeDetail?->price?->is_test_product)
                                    <tr>
                                        <td class="text-gray-800 fw-bold fs-6">{{__("start_date")}}</td>
                                        <td class="text-gray-600 fw-semibold fs-6">{{$order->start_date->format(defaultDateFormat())}}</td>
                                    </tr>
                                        <tr>
                                            <td class="text-gray-800 fw-bold fs-6">{{__("end_date")}}</td>
                                            <td class="text-gray-600 fw-semibold fs-6">{{$order->start_date?->format(defaultDateFormat())}} {{$order->created_at?->addHours(2)->format('H:i')}} </td>
                                        </tr>
                                    @else
                                    <tr>
                                        <td class="text-gray-800 fw-bold fs-6">{{__("start_date")}}</td>
                                        <td class="text-gray-600 fw-semibold fs-6">{{$order->start_date->format(defaultDateFormat())}}</td>
                                    </tr>
                                    @if($order->end_date)
                                        <tr>
                                            <td class="text-gray-800 fw-bold fs-6">{{__("end_date")}}</td>
                                            <td class="text-gray-600 fw-semibold fs-6">{{$order->end_date?->format(defaultDateFormat())}}</td>
                                        </tr>
                                    @endif
                                    @endif

                                <tr>
                                    <td colspan="2" class="text-center fs-3">
                                        <span class="fw-bold">{{__("total_amount")}}:</span> <span
                                            class="fw-semibold">{{showBalance($order->getTotalAmount() ?? 0, true)}}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light-secondary min-h-60px h-60px">
                            <h3 class="card-title fw-bold">
                                {{__("additional_services")}}
                            </h3>
                        </div>
                        <div class="card-body">
                            <x-proxy-additional-services :order="$order"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-5 mt-lg-8">
            <div class="card">
                <div class="card-header bg-light-secondary min-h-60px h-60px">
                    <h3 class="card-title fw-bold">
                        {{__("proxy_information")}}
                    </h3>
                    <div class="card-toolbar">
                        <div class="text-end">
                            <div class="text-gray-700 fw-semibold">{{__("order_number")}}</div>
                            <div class="fw-bold fs-4">
                                #{{$order->id}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-proxy-information :order="$order"/>
                </div>
            </div>
        </div>
    </div>
    <!--begin::Modals-->
    @if($order->upgradePrices())
        <div class="modal fade" id="upgradeModal" data-bs-backdrop="static"
             data-bs-keyboard="false" tabindex="-1"
             aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="upgradeModal_header">
                        <!--begin::Modal title-->
                        <h2>{{__("upgrade_options")}}</h2>
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
                        <form id="upgradeForm">
                            @csrf
                            <!--begin::Scroll-->
                            <div class="scroll-y me-n7 pe-7" id="upgradeModal_scroll"
                                 data-kt-scroll="true"
                                 data-kt-scroll-activate="{default: false, lg: true}"
                                 data-kt-scroll-max-height="auto"
                                 data-kt-scroll-dependencies="#upgradeModal_header"
                                 data-kt-scroll-wrappers="#upgradeModal_scroll"
                                 data-kt-scroll-offset="300px">
                                <div class="row g-3">
                                    @foreach($order->upgradePrices() as $upgradePrice)
                                        @php
                                            $col = "4";
                                            switch (count($order->upgradePrices())){
                                                case 1:
                                                    $col = "12";
                                                    break;
                                                case 2:
                                                    $col = "6";
                                                    break;
                                            }
                                        @endphp
                                        <div class="col-xl-{{$col}}">
                                            <div class="card">
                                                <div class="card-body text-center bg-light-primary">
                                                    <!--begin::Desc-->
                                                    <div class="text-gray-800 fs-3 fw-semibold mb-2">
                                                        {{$upgradePrice->duration}} {{__(mb_strtolower($upgradePrice->duration_unit))}}
                                                    </div>
                                                    <!--end::Desc-->
                                                    <!--begin::Price-->
                                                    <div class="text-center">
                                                        <span
                                                            class="mb-2 fs-1 text-primary">{{\App\Models\Currency::DEFAULT_SYMBOL}}</span>
                                                        <span
                                                            class="fs-2hx fw-bold text-primary">{{showBalance($upgradePrice->price - $upgradePrice->discount)}}</span>
                                                    </div>
                                                    <!--end::Price-->

                                                    <button type="button" class="btn btn-primary mt-4 upgradeBtn"
                                                            data-upgrade-price-id="{{$upgradePrice->id}}">
                                                        <!--begin::Indicator label-->
                                                        <span class="indicator-label"><i class="fa fa-rocket me-1"></i> {{__("upgrade_now")}}</span>
                                                        <!--end::Indicator label-->
                                                        <!--begin::Indicator progress-->
                                                        <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                        <!--end::Indicator progress-->
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Scroll-->
                            <!--begin::Actions-->
                            <div class="d-flex flex-center flex-row-fluid pt-12">
                                <button type="reset" class="btn btn-light me-3"
                                        data-bs-dismiss="modal">{{__("cancel")}}</button>
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
    @endif
    <!--end::Modals-->

@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $(document).on("click", ".upgradeBtn", function () {
                let upgradePriceId = $(this).data("upgrade-price-id");
                alerts.confirm.fire({
                    title: "Yükseltme işlemi yapmak üzeresiniz!",
                    text: "Devam etmeniz halinde yükseltme faturanız oluşturulacaktır.",
                }).then((r) => {
                    if (r.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: "{{route("portal.orders.upgrade", ["order" => $order->id])}}",
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                upgrade_price_id: upgradePriceId,
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        text: res?.message ?? "",
                                        showCancelButton: false,
                                        allowOutsideClick: false
                                    })
                                    setTimeout(() => window.location.href = res.redirectUrl, 800)
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
        })
    </script>

    @if($order->isLocaltonetLikeDelivery() && !$order->isCanDeliveryType('LOCALTONETV4'))
        <!--begin::Modals-->
        <div class="modal fade" id="changeAirplaneModeModal" data-bs-backdrop="static" data-bs-keyboard="false"
             tabindex="-1"
             aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-md">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="primarySupportModal_header">
                        <!--begin::Modal title-->
                        <h2>{{__("automatic_ip_renewal")}}</h2>
                        <!--end::Modal title-->
                        <div class="d-flex align-items-center gap-1">
                            <!--begin::Close-->
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Close-->
                        </div>
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body py-lg-10 px-lg-15">
                        <form id="changeAirplaneModeForm"
                              action="{{route("portal.orders.localtonet.setAutoAirplaneModeSetting", ["order" => $order->id])}}">
                            @csrf
                            <!--begin::Scroll-->
                            <div class="scroll-y me-n7 pe-7" id="primaryEventModal_scroll" data-kt-scroll="true"
                                 data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                                 data-kt-scroll-dependencies="#primarySupportModal_header"
                                 data-kt-scroll-wrappers="#primaryEventModal_scroll" data-kt-scroll-offset="300px">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="mb-5 alert alert-primary">En az 30 saniye kabul edilir.</div>
                                        <!--begin::Label-->
                                        <label class="required form-label">{{__("duration")}} ({{__("seconds")}}
                                            )</label>
                                        <!--end::Label-->
                                        <!--begin::Select-->
                                        <input type="number" name="time" min="30" class="form-control" required>
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
        <script>
            $(document).ready(function () {
                $(document).on("change", "#localtonet_authorization_tab [name='is_active']", function () {
                    let element = $(this),
                        area = $("#localtonet_authorization_tab");

                    if (element.is(":checked")) {
                        area.find(".whitelistArea").hide()
                        area.find(".userNamePassArea").fadeIn()
                    } else {
                        area.find(".userNamePassArea").hide()
                        area.find(".whitelistArea").fadeIn()
                    }
                })
                $("#localtonet_authorization_tab [name='is_active']").trigger("change");
                $(document).on("submit", "#authorizationForm", function (e) {
                    e.preventDefault()
                    let form = $(this);
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
                            alerts.wait.fire()
                        },
                        complete: function (data, status) {
                            Swal.close();
                            res = data.responseJSON;
                            if (res && res.success === true) {
                                Swal.fire({
                                    title: "{{__('success')}}",
                                    text: res?.message ?? "",
                                    icon: "success",
                                    showConfirmButton: 0,
                                    showCancelButton: 1,
                                    cancelButtonText: "{{__('close')}}"
                                }).then((r) => window.location.reload())
                            } else {
                                Swal.fire({
                                    title: "{{__('error')}}",
                                    text: res?.message ? res.message : "{{__('form_has_errors')}}",
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

                $(document).on("click", ".proxyChangeStatusBtn", function () {
                    let btn = $(this),
                        alertText = btn.data("alert-text"),
                        url = btn.data("action");

                    Swal.fire({
                        icon: 'warning',
                        title: "{{__('warning')}}",
                        text: alertText,
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
                                beforeSend: function () {
                                    propSubmitButton(btn, 1);
                                },
                                complete: function (data, status) {
                                    propSubmitButton(btn, 0);
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        Swal.fire({
                                            title: "{{__('success')}}",
                                            text: res?.message ?? "",
                                            icon: "success",
                                            showConfirmButton: 0,
                                            showCancelButton: 1,
                                            cancelButtonText: "{{__('close')}}"
                                        }).then((r) => window.location.reload())
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

                $(document).on("click", ".changePortBtn", function () {
                    let btn = $(this);
                    Swal.fire({
                        icon: 'warning',
                        title: "{{__('warning')}}",
                        text: "Portu düzenlemek istediğinize emin misiniz?",
                        showConfirmButton: 1,
                        showCancelButton: 1,
                        cancelButtonText: "{{__('close')}}",
                        confirmButtonText: "{{__('yes')}}",
                    }).then((result) => {
                        if (result.isConfirmed === true) {
                            $.ajax({
                                type: "POST",
                                url: '{{route("portal.orders.localtonet.setServerPort", ["order" => $order->id])}}',
                                dataType: "json",
                                data: {
                                    _token: "{{csrf_token()}}",
                                    server_port: $("[name='server_port']").val() ?? null
                                },
                                beforeSend: function () {
                                    propSubmitButton(btn, 1);
                                },
                                complete: function (data, status) {
                                    propSubmitButton(btn, 0);
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        Swal.fire({
                                            title: "{{__('success')}}",
                                            text: res?.message ?? "",
                                            icon: "success",
                                            showConfirmButton: 0,
                                            showCancelButton: 1,
                                            cancelButtonText: "{{__('close')}}"
                                        }).then((r) => window.location.reload())
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

                $(document).on("click", ".changeAirplaneModeBtn", function () {
                    if ($(this).hasClass("active")) {
                        alerts.confirm.fire({
                            title: "{{__('warning')}}",
                            text: "Otomatik IP yenilemeyi durdurmak istediğinize emin misiniz?",
                            confirmButtonText: "{{__('yes')}}, durdur",
                            cancelButtonText: "{{__("cancel")}}"
                        }).then((r) => {
                            if (r.isConfirmed === true) {
                                $.ajax({
                                    type: "POST",
                                    url: "{{route("portal.orders.localtonet.setAutoAirplaneModeSetting", ["order" => $order->id])}}",
                                    dataType: "json",
                                    data: {
                                        _token: "{{csrf_token()}}",
                                        stop: true,
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
                                            }).then((r) => window.location.reload())
                                        } else {
                                            alerts.error.fire({
                                                text: res?.message ?? ""
                                            })
                                        }
                                    }
                                })
                            }
                        })
                    } else {
                        $("#changeAirplaneModeModal").modal("show")
                    }
                })

                $(document).on("submit", "#changeAirplaneModeForm", function (e) {
                    e.preventDefault()
                    let form = $(this);
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
                                }).then((r) => window.location.reload())
                            } else {
                                Swal.fire({
                                    title: "{{__('error')}}",
                                    text: res?.message ? res.message : "{{__('form_has_errors')}}",
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

                $(document).on("click", ".ipChangeBtn, .deviceRestartBtn", function () {
                    let btn = $(this),
                        ajaxUrl = btn.data("ajax-url"),
                        swalText = btn.data("swal-text");
                    Swal.fire({
                        icon: 'warning',
                        title: "{{__('warning')}}",
                        text: swalText,
                        showConfirmButton: 1,
                        showCancelButton: 1,
                        cancelButtonText: "{{__('close')}}",
                        confirmButtonText: "{{__('yes')}}",
                    }).then((result) => {
                        if (result.isConfirmed === true) {
                            $.ajax({
                                type: "POST",
                                url: ajaxUrl,
                                dataType: "json",
                                data: {
                                    _token: "{{csrf_token()}}"
                                },
                                beforeSend: function () {
                                    propSubmitButton(btn, 1);
                                    alerts.wait.fire()
                                },
                                complete: function (data, status) {
                                    Swal.close();
                                    propSubmitButton(btn, 0);
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        if (btn.hasClass('deviceRestartBtn')) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: "{{__('success')}}",
                                                html: '<div class="swal-restart-body">' +
                                                    '<p class="swal-restart-lead">Cihaz yeniden başlatma isteği iletildi.</p>' +
                                                    '<div class="swal-restart-panel">' +
                                                    '<p class="label">Beklenen süre</p>' +
                                                    '<p class="main">Proxy bağlantısı genellikle <strong>5–10 dakika</strong> içinde yeniden kullanılabilir olur.</p>' +
                                                    '<p class="hint">Bu sürede kısa kesintiler yaşanması normaldir.</p>' +
                                                    '</div></div>',
                                                width: '28rem',
                                                showConfirmButton: false,
                                                showCancelButton: true,
                                                cancelButtonText: "{{__('close')}}",
                                                buttonsStyling: false,
                                                customClass: {
                                                    popup: 'swal2-restart-popup',
                                                    cancelButton: 'btn btn-success btn-sm px-5 fw-semibold'
                                                },
                                                focusCancel: true
                                            }).then((r) => window.location.reload())
                                        } else {
                                            Swal.fire({
                                                title: "{{__('success')}}",
                                                text: res?.message ?? "",
                                                icon: "success",
                                                showConfirmButton: 0,
                                                showCancelButton: 1,
                                                cancelButtonText: "{{__('close')}}"
                                            }).then((r) => window.location.reload())
                                        }
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

                $(document).on("click", ".addQuotaBtn", function () {
                    let btn = $(this),
                        quota = $("[name='quota']").val();

                    if (!quota) {
                        alerts.confirm.fire({
                            title: "{{__('warning')}}",
                            text: "{{__("custom_field_is_required", ["name" => __(":name_selection", ["name" => __("quota")])])}}",
                            showConfirmButton: false,
                            showCancelButton: true
                        })
                        return false;
                    }

                    alerts.confirm.fire({
                        title: "{{__('warning')}}",
                        text: "{{__("are_you_sure_you_want_to_create_a_quota_increase_invoice")}}",
                        confirmButtonText: "{{__('yes')}}",
                        cancelButtonText: "{{__("cancel")}}"
                    }).then((r) => {
                        if (r.isConfirmed === true) {
                            $.ajax({
                                type: "POST",
                                url: "{{route("portal.orders.addQuotaPost", ["order" => $order->id])}}",
                                dataType: "json",
                                data: {
                                    _token: "{{csrf_token()}}",
                                    quota: quota
                                },
                                beforeSend: function () {
                                    propSubmitButton(btn, 1);
                                },
                                complete: function (data, status) {
                                    propSubmitButton(btn, 0);
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        alerts.success.fire({
                                            title: "{{__('success')}}",
                                            text: res?.message ?? "",
                                            showConfirmButton: false,
                                            showCancelButton: false,
                                        })
                                        setTimeout((r) => {
                                            window.location.href = res?.redirectUrl
                                        }, 1500)
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
                $(document).on("click", ".addQuotaDurationBtn", function () {
                    let btn = $(this),
                        quotaDuration = $("[name='quota_duration']").val();

                    if (!quotaDuration) {
                        alerts.confirm.fire({
                            title: "{{__('warning')}}",
                            text: "{{__("custom_field_is_required", ["name" => __(":name_selection", ["name" => __("quota_and_duration")])])}}",
                            showConfirmButton: false,
                            showCancelButton: true
                        })
                        return false;
                    }

                    alerts.confirm.fire({
                        title: "{{__('warning')}}",
                        text: "{{__("are_you_sure_you_want_to_create_a_quota_duration_increase_invoice")}}",
                        confirmButtonText: "{{__('yes')}}",
                        cancelButtonText: "{{__("cancel")}}"
                    }).then((r) => {
                        if (r.isConfirmed === true) {
                            $.ajax({
                                type: "POST",
                                url: "{{route("portal.orders.addQuotaDurationPost", ["order" => $order->id])}}",
                                dataType: "json",
                                data: {
                                    _token: "{{csrf_token()}}",
                                    quota_duration: quotaDuration
                                },
                                beforeSend: function () {
                                    propSubmitButton(btn, 1);
                                },
                                complete: function (data, status) {
                                    propSubmitButton(btn, 0);
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        alerts.success.fire({
                                            title: "{{__('success')}}",
                                            text: res?.message ?? "",
                                            showConfirmButton: false,
                                            showCancelButton: false,
                                        })
                                        setTimeout((r) => {
                                            window.location.href = res?.redirectUrl
                                        }, 1500)
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

                $(document).on("click", ".generateUserNamePassBtn", function () {
                    let area = $("#localtonet_authorization_tab");

                    function generateRandomString(length) {
                        let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                        let result = '';
                        for (let i = 0; i < length; i++) {
                            result += characters.charAt(Math.floor(Math.random() * characters.length));
                        }
                        return result;
                    }

                    area.find("[name='user_name']").val(generateRandomString(6));
                    area.find("[name='password']").val(generateRandomString(6));
                })
            })
        </script>
    @endif
@endsection
