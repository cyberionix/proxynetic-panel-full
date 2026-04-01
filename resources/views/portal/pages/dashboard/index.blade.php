@extends("portal.template")
@section("title", __("dashboard"))
@section("css") @endsection
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('dashboard')"/>
@endsection
@section("master")
    @if(session()->has('success'))
        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-3 p-6">
            <!--begin::Icon-->
            <i class="ki-duotone ki-information fs-2tx text-primary me-4"><span class="path1"></span><span
                    class="path2"></span><span class="path3"></span></i>
            <!--end::Icon-->
            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-grow-1 ">
                <!--begin::Content-->
                <div class=" fw-semibold">
                    <b class="fs-6 text-gray-700">{{session('success')}}</b>
                </div>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
    @endif


    <div class="row g-5">
        <div class="col-12 mb-4">
            <h1>Sn. {{auth()->user()->full_name}}. Müşteri Panelinize Hoşgeldiniz. </h1>
            <div class="separator"></div>
        </div>
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <a href="{{route("portal.orders.index")}}" class="card bg-primary hoverable">
                        <div class="card-body text-white d-flex justify-content-between align-items-center gap-8">
                            <div>
                                <i class="bi bi-stack fs-4x text-white"></i>
                            </div>
                            <div class="text-gray-100 fw-bolder">
                                <div class="fs-1">{{__("my_products_and_services")}}</div>
                                <div class="text-end cardDataSpinner d-none" style="margin-top: 2rem">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </div>
                                <div class="fs-2qx text-end" data-np-info-card="order"></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-lg-4">
                    <a href="{{route("portal.supports.index")}}" class="card bg-info hoverable">
                        <div class="card-body text-white d-flex justify-content-between align-items-center gap-8">
                            <div>
                                <i class="ki-duotone ki-abstract-33 fs-5x text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <div class="text-gray-100 fw-bolder">
                                <div class="fs-1">{{__("my_support_tickets")}}</div>
                                <div class="text-end cardDataSpinner d-none" style="margin-top: 2rem">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </div>
                                <div class="fs-2qx text-end" data-np-info-card="support"></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-lg-4">
                    <a href="{{route("portal.invoices.index")}}" class="card bg-dark hoverable">
                        <div class="card-body text-white d-flex justify-content-between align-items-center gap-8">
                            <div>
                                <i class="fa fa-file-invoice fs-4x text-white"></i>

                            </div>
                            <div class="text-gray-100 fw-bolder">
                                <div class="fs-1">{{__("invoices")}}</div>
                                <div class="text-end cardDataSpinner d-none" style="margin-top: 2rem">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </div>
                                <div class="fs-2qx text-end" data-np-info-card="invoice"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-12">
            @foreach(auth()->user()->alerts() as $alert)
                @if(!isset($_COOKIE["alert_read_" . $alert->id]))
                    <!--begin::Alert-->
                    <div data-np-alert="item" data-id="{{$alert->id}}"
                         class="alert alert-dismissible bg-{{$alert->bg_color}} d-flex flex-column flex-sm-row p-5">
                        <div class="d-flex align-items-center">
                            <!--begin::Icon-->
                            <i class="ki-duotone ki-notification-bing fs-3x text-light me-4 mb-5 mb-sm-0"><span
                                    class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <!--end::Icon-->
                        </div>
                        <!--begin::Wrapper-->
                        <div class="d-flex align-items-center text-light">
                            <!--begin::Title-->
                            <h4 class="mb-0 text-light">{{$alert->message}}</h4>
                            <!--end::Title-->
                        </div>
                        <!--end::Wrapper-->

                        <!--begin::Close-->
                        <button type="button"
                                data-np-alert="close-btn"
                                class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto">
                            <i class="ki-duotone ki-cross fs-1 text-light"><span class="path1"></span><span
                                    class="path2"></span></i>
                        </button>
                        <!--end::Close-->
                    </div>
                    <!--end::Alert-->
                @endif
            @endforeach
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <div>
                            <i class="bi bi-stack fs-2x text-primary me-3"></i>
                        </div>
                        <div class="d-flex align-items-start flex-column">
                            <span
                                class="card-label fw-bold fs-3 mb-1">{{__("product")}} / {{__("service")}} Bilgileri</span>
                            <span
                                class="text-muted mt-1 fw-semibold fs-7">{{__("last_:number_records", ["number" => "4"])}}</span>
                        </div>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{route("portal.orders.index")}}" class="btn btn-light-primary btn-sm"><i
                                class="fa fa-eye"></i> {{__("view_all")}}</a>
                    </div>
                </div>
                <div class="card-body">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="proxyListTable" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="min-w-50px">#</th>
                                <th class="min-w-125px">IP:PORT</th>
                                <th class="min-w-125px">ISP</th>
                                <th class="min-w-125px">{{__("Mevcut IP")}}</th>
                                <th class="min-w-125px">{{__("status")}}</th>
                                <th class="min-w-125px">{{__("Trafik")}}</th>
                                <th class="min-w-125px">{{__("action")}}</th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">

                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <div class="w-100 h-100px d-flex flex-center proxyListSpinner"
                             style="margin-top: 2rem">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </div>
                    </div>
                    <!--end::Table-->
                </div>
            </div>
        </div>
        <div class="col-xl-6 d-none">
            <div class="card card-stretch">
                <div class="card-header">
                    <h3 class="card-title">
                        <div>
                            <i class="bi bi-stack fs-2x text-primary me-3"></i>
                        </div>
                        <div class="d-flex align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">{{__("my_active_products_and_services")}}</span>
                            <span
                                class="text-muted mt-1 fw-semibold fs-7">{{__("last_:number_records", ["number" => "4"])}}</span>
                        </div>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{route("portal.orders.index")}}" class="btn btn-light-primary btn-sm"><i
                                class="fa fa-eye"></i> {{__("view_all")}}</a>
                    </div>
                </div>
                <div class="card-body pt-6">
                    <div class="w-100 h-100px d-flex flex-center cardDataSpinner" style="margin-top: 2rem">
                        <span class="spinner-border spinner-border-sm align-middle"></span>
                    </div>
                    <div data-np-order="items">

                    </div>
                    <div class="d-none" data-np-order="item-template">
                        <a class="d-flex flex-stack py-3 rounded-3 bg-hover-light-secondary" data-np-order="item">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-40px ms-3 me-4">
                                <div class="symbol-label fs-2 fw-semibold bg-light-primary text-inverse-danger">
                                    <i class="fa fa-cube fs-3"></i>
                                </div>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Section-->
                            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                <!--begin:Author-->
                                <div class="flex-grow-1 me-2">
                                    <div class="text-gray-800 fs-6 fw-bold" data-np-order="name"></div>
                                    <span
                                        class="text-muted fw-semibold d-block fs-7"
                                        data-np-order="category_name"></span>
                                    <span
                                        class="text-muted fw-semibold d-block fs-7">{{__("next_payment_date")}}: <span
                                            class="fw-bold" data-np-order="end_date"></span></span>
                                </div>
                                <!--end:Author-->
                                <!--begin::Actions-->
                                <div
                                    class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px me-3">
                                    <i class="ki-duotone ki-arrow-right fs-2"><span class="path1"></span><span
                                            class="path2"></span></i>
                                </div>
                                <!--begin::Actions-->
                            </div>
                            <!--end::Section-->
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-stretch">
                <div class="card-header">
                    <h3 class="card-title">
                        <div>
                            <i class="ki-duotone ki-abstract-33 fs-2qx text-primary me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <div class="d-flex align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">{{__("my_support_tickets")}}</span>
                            <span
                                class="text-muted mt-1 fw-semibold fs-7">{{__("last_:number_records", ["number" => "4"])}}</span>
                        </div>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{route("portal.supports.index")}}" class="btn btn-light-primary btn-sm"><i
                                class="fa fa-eye"></i> {{__("view_all")}}</a>
                    </div>
                </div>
                <div class="card-body pt-6">
                    <!--begin::Table-->
                    <table id="supportsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                            <th class="min-w-125px">{{__("subject")}}</th>
                            <th class="min-w-125px">{{__("status")}}</th>
                            <th class="min-w-125px">{{__("action")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3">
                                <div class="w-100 h-100px d-flex flex-center cardDataSpinner"
                                     style="margin-top: 2rem">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                    <!--end::Table-->
                </div>
            </div>
        </div>
    </div>
    @if(!auth()->user()->email_verified_at)
        <x-portal.modals.email-confirmation-waiting-modal/>
    @elseif(!auth()->user()->phone_verified_at)
        <x-portal.modals.phone-verify-modal/>
    @elseif(!auth()->user()->address)
        <x-portal.modals.primary-address-modal hardly="true"/>
    @elseif(!auth()->user()->identity_number_verified_at)
        <x-portal.modals.identity-number-modal/>
        @else
        @if(auth()->user()->is_force_kyc && !auth()->user()->kyc?->verified_at)
            <x-portal.modals.kyc-modal/>
        @endif
    @endif
@endsection
@section("js")

    <script>
        @if(!auth()->user()->address)
        $(document).ready(function(){
            $('#primaryAddressModal').modal('show');

        })
        @endif
    </script>
    @if(!empty(auth()->user()->phone_verified_at) && !empty(auth()->user()->identity_number_verified_at) && !(auth()->user()->is_force_kyc && !auth()->user()->kyc?->verified_at))
        <script>

            $(document).ready(function () {

                let orderItemTemplate = $("[data-np-order='item-template']");
                $.ajax({
                    type: 'GET',
                    url: '{{route('portal.dashboard.getData')}}',
                    data: {
                        _token: '{{csrf_token()}}',
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        $(".cardDataSpinner").removeClass("d-none")
                        $("[data-np-order='items']").html("")
                        $("#supportsTable").find("tbody").html("");
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            $("[data-np-info-card='order']").text(res?.info_card?.order ?? 0)
                            $("[data-np-info-card='support']").text(res?.info_card?.support ?? 0)
                            $("[data-np-info-card='invoice']").text(res?.info_card?.invoice ?? 0)

                            if (res?.orders.length <= 0) {
                                $("[data-np-order='items']").append('<div class="text-gray-500 fw-bold text-center py-5 fs-6">{{__("there_are_no_products_services")}}</div>');
                            } else {
                                res?.orders.map((item, index) => {
                                    orderItemTemplate.find("[data-np-order='item']").attr("href", item?.viewRoute);
                                    orderItemTemplate.find("[data-np-order='name']").text(item?.product_data?.name);
                                    orderItemTemplate.find("[data-np-order='category_name']").text(item?.product_data?.category?.name);
                                    orderItemTemplate.find("[data-np-order='end_date']").text(item?.drawEndDate);

                                    $("[data-np-order='items']").append($("[data-np-order='item-template']").html());
                                    if (index < 3) {
                                        $("[data-np-order='items']").append('<div class="separator separator-dashed my-4"></div>');
                                    }
                                })
                            }

                            if (res?.supports.length <= 0) {
                                $("#supportsTable").find("tbody").append('<tr><td colspan="3"><div class="text-gray-500 fw-bold text-center py-5 fs-6">{{__("you_dont_have_a_support_request_yet")}}</div></td></tr>');
                            } else {
                                res?.supports.map((item) => {
                                    $("#supportsTable").find("tbody").append(`<tr><td>${item?.subject}</td><td>${item?.drawStatusBadge}</td><td>${item?.drawAction}</td></tr>`)
                                })
                            }

                            $(".cardDataSpinner").addClass("d-none")
                        } else {
                            toastr.error(res?.message ?? "{{__('error')}}");
                        }
                    }
                })

                $(document).on("click", ".ipChangeBtn", function () {
                    let btn = $(this),
                        ajaxUrl = btn.data("ajax-url");
                    Swal.fire({
                        icon: 'warning',
                        title: "{{__('warning')}}",
                        text: "{{__("are_you_sure_you_want_to_change_ip")}}",
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
                                    Swal.fire({
                                        icon: "warning",
                                        title: '{{__("please_wait")}}',
                                        html: '{{__("transaction_continues")}}',
                                        didOpen: () => {
                                            Swal.showLoading()
                                        },
                                        allowOutsideClick: 0
                                    })
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

                $(document).on("click", '[data-np-alert="close-btn"]', function () {
                    let alertArea = $(this).closest('[data-np-alert="item"]'),
                        alertId = alertArea.data("id"),
                        cookieName = "alert_read_" + alertId;

                    setCookie(cookieName, "", 7);

                    alertArea.remove();
                })
            })
        </script>
        <script>
            $(document).ready(function () {
                let count = 4, dataOrder = 1;

                function fetchData(dataOrder) {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            type: 'GET',
                            url: '{{route('portal.orders.localtonet.getProxyListTable')}}',
                            data: {
                                _token: '{{csrf_token()}}',
                                dataOrder: dataOrder
                            },
                            dataType: 'json',
                            beforeSend: function () {
                                $(".proxyListSpinner").removeClass("d-none");
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    if (!res.data) {
                                        $(".proxyListSpinner").addClass("d-none");
                                        return resolve(false);
                                    }

                                    let changeIpDraw = "";
                                    if (res.data?.deliveryType == "LOCALTONET" || res.data?.deliveryType == "LOCALTONETV4") {
                                        changeIpDraw = '<div class="badge badge-primary cursor-pointer mt-1 ipChangeBtn" data-ajax-url="' + res.data.ip_change_url + '"><i class="fa fa-rotate-right text-white me-1"></i> IP Değiştir</div>';
                                    }
                                    let ispImage = "-";
                                    if (res.data?.isp_image) {
                                        ispImage = '<img class="w-50px" src="' + res.data?.isp_image + '"/>';
                                    }
                                    let seeMore = "";
                                    if (res.data?.isSeeMore) {
                                        seeMore = '<br><a class="text-hover-primary text-gray-500 fw-bold fs-7 gs-0" href="' + res.data?.viewUrl + '">...{{__("see_more")}}</a>';
                                    }

                                    $("#proxyListTable").find("tbody").append('<tr>' +
                                        '<td>' + res.data?.orderId + '</td>' +
                                        '<td>' + res.data?.ipPort + ' ' + seeMore + '</td>' +
                                        '<td>' + ispImage + '</td>' +
                                        '<td><div>' + res.data?.ip + '</div>' + changeIpDraw + '</td>' +
                                        '<td>' + res.data?.drawStatus + '</td>' +
                                        '<td>' + res.data?.traffic + '</td>' +
                                        '<td><a href="' + res.data?.viewUrl + ' " class="btn btn-light-primary btn-sm">{{__("view")}}</a></td></tr>');
                                    resolve(true);
                                } else {
                                    toastr.error(res?.message ?? "{{__('error')}}");
                                    reject(new Error(res?.message ?? "{{__('error')}}"));
                                }
                                $(".proxyListSpinner").addClass("d-none");
                            }
                        });
                    });
                }

                async function executeFetchData() {
                    for (let i = 0; i < count; i++) {
                        try {
                            await fetchData(dataOrder);
                            dataOrder++;
                        } catch (error) {
                            console.error("Error fetching data for order", dataOrder, error);
                        }
                    }
                }

                executeFetchData();
            })
        </script>
    @endif
    @if(session("error"))
        <script>
            alerts.error.fire({
                text: "{{session("error")}}"
            })
        </script>
    @endif
    @if(session("success"))
        <script>
            alerts.success.fire({
                text: "{{session("success")}}"
            })
        </script>
    @endif
@endsection
