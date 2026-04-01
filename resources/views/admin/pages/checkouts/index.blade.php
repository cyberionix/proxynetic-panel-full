@extends("admin.template")
@section("title", __("checkouts"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('checkouts')"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Navbar-->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body py-0">
                        <!--begin:::Tabs-->
                        <ul id="header-nav"
                            class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mt-3 gap-8">
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab active"
                                   data-bs-toggle="tab"
                                   data-key=""
                                   href="javascript:void(0);">{{__("all")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="NEW"
                                   href="javascript:void(0);">{{__("new")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="WAITING_APPROVAL"
                                   href="javascript:void(0);">{{__("waiting")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="3DS_REDIRECTED"
                                   href="javascript:void(0);">3D</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="COMPLETED"
                                   href="javascript:void(0);">{{__("completed")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="FAILED"
                                   href="javascript:void(0);">{{__("failed")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="CANCELLED"
                                   href="javascript:void(0);">{{__("cancelled")}}</a>
                            </li>
                            <!--end:::Tab item-->
                        </ul>
                        <!--end:::Tabs-->
                    </div>
                </div>
                <!--end::Navbar-->
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title d-flex flex-wrap gap-5">
                            <!--begin::Search-->
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="text" data-table-action="search"
                                       class="form-control  w-250px ps-13"
                                       placeholder="{{__("search_in_table")}}"/>
                            </div>
                            <!--end::Search-->
                            <!--start::Info-->
                            <div class="ms-2 d-flex flex-wrap gap-5">
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
                        <div class="card-toolbar">

                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="dataTable"
                               class="table align-middle table-row-dashed table-hover cursor-pointer fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="m-w-50">#</th>
                                <th class="min-w-50px">{{__("customer")}}</th>
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
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    <!--begin::Modals-->
    <x-admin.modals.checkout-detail-modal id="checkoutDetailModal" />
    <!--end::Modals-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var t = $("#dataTable").DataTable({
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
                    "url": "{{ route("admin.checkouts.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.showAllList = true
                        d.status = $(".statusTab.active").data("key")
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('#dataTable > tbody tr').each(function (index, item) {
                    let bg = $(item).closest("tr").find('td:first span').data('bg');
                    $(item).addClass('bg-' + bg)
                })
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".statusTab", function () {
                t.draw();
            })

            $(document).on("click", "#dataTable tbody tr", function () {
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
                            if(res.data.type == "TRANSFER" && res.data.status == "WAITING_APPROVAL"){
                                modal.find(".paymentNotify").removeClass("d-none");
                            }else{
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
                            beforeSend:function (){
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
        })
    </script>
@endsection
