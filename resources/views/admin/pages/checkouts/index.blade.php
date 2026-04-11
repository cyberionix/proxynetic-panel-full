@extends("admin.template")
@section("title", __("checkouts"))
@section("css")
<style>
    .statusTab[data-key="NEW"].active { color: #3b82f6 !important; border-bottom-color: #3b82f6 !important; }
    .statusTab[data-key="WAITING_APPROVAL"].active { color: #f59e0b !important; border-bottom-color: #f59e0b !important; }
    .statusTab[data-key="3DS_REDIRECTED"].active { color: #8b5cf6 !important; border-bottom-color: #8b5cf6 !important; }
    .statusTab[data-key="COMPLETED"].active { color: #10b981 !important; border-bottom-color: #10b981 !important; }
    .statusTab[data-key="FAILED"].active { color: #ef4444 !important; border-bottom-color: #ef4444 !important; }
    .statusTab[data-key="CANCELLED"].active { color: #6b7280 !important; border-bottom-color: #6b7280 !important; }
    #dataTable tbody tr.bg-light-primary { background-color: rgba(59,130,246,0.08) !important; }
    #dataTable tbody tr.bg-light-warning { background-color: rgba(245,158,11,0.08) !important; }
    #dataTable tbody tr.bg-light-info { background-color: rgba(139,92,246,0.08) !important; }
    #dataTable tbody tr.bg-light-success { background-color: rgba(16,185,129,0.08) !important; }
    #dataTable tbody tr.bg-light-danger { background-color: rgba(239,68,68,0.08) !important; }
    #dataTable tbody tr.bg-secondary { background-color: rgba(107,114,128,0.06) !important; }
</style>
@endsection
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
                                    <div class="rounded-circle w-12px h-12px me-2" style="background:#3b82f6;"></div>
                                    <span class="fw-semibold text-gray-700">{{__("new")}}</span>
                                </div>
                                <div class="d-flex align-items-center fs-6 me-3">
                                    <div class="rounded-circle w-12px h-12px me-2" style="background:#f59e0b;"></div>
                                    <span class="fw-semibold text-gray-700">{{__("waiting")}}</span>
                                </div>
                                <div class="d-flex align-items-center fs-6 me-3">
                                    <div class="rounded-circle w-12px h-12px me-2" style="background:#8b5cf6;"></div>
                                    <span class="fw-semibold text-gray-700">3D</span>
                                </div>
                                <div class="d-flex align-items-center fs-6 me-3">
                                    <div class="rounded-circle w-12px h-12px me-2" style="background:#10b981;"></div>
                                    <span class="fw-semibold text-gray-700">{{__("completed")}}</span>
                                </div>
                                <div class="d-flex align-items-center fs-6 me-3">
                                    <div class="rounded-circle w-12px h-12px me-2" style="background:#ef4444;"></div>
                                    <span class="fw-semibold text-gray-700">{{__("failed")}}</span>
                                </div>
                                <div class="d-flex align-items-center fs-6 me-3">
                                    <div class="rounded-circle w-12px h-12px me-2" style="background:#6b7280;"></div>
                                    <span class="fw-semibold text-gray-700">{{__("cancelled")}}</span>
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
