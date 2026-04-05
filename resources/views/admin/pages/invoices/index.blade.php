@extends("admin.template")
@section("title", __("invoices"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('invoices')"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Navbar-->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body py-0">
                        <ul id="header-nav"
                            class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mt-3 gap-2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab active"
                                   data-bs-toggle="tab" data-key="" href="javascript:void(0);">
                                    <i class="fa fa-list-ul me-2 fs-6"></i>Tümü
                                    <span class="badge badge-light-dark badge-sm ms-2 tab-count" data-status="all"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-danger pb-4 statusTab"
                                   data-bs-toggle="tab" data-key="PENDING" href="javascript:void(0);">
                                    <i class="fa fa-clock me-2 fs-6"></i>Bekliyor
                                    <span class="badge badge-light-danger badge-sm ms-2 tab-count" data-status="PENDING"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-success pb-4 statusTab"
                                   data-bs-toggle="tab" data-key="PAID" href="javascript:void(0);">
                                    <i class="fa fa-check-circle me-2 fs-6"></i>Ödendi
                                    <span class="badge badge-light-success badge-sm ms-2 tab-count" data-status="PAID"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-secondary pb-4 statusTab"
                                   data-bs-toggle="tab" data-key="CANCELLED" href="javascript:void(0);">
                                    <i class="fa fa-ban me-2 fs-6"></i>İptal
                                    <span class="badge badge-light-secondary badge-sm ms-2 tab-count" data-status="CANCELLED"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-info pb-4 statusTab"
                                   data-bs-toggle="tab" data-key="FORMALIZED" href="javascript:void(0);">
                                    <i class="fa fa-file-invoice me-2 fs-6"></i>Resmileştirilmiş
                                    <span class="badge badge-light-info badge-sm ms-2 tab-count" data-status="FORMALIZED"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--end::Navbar-->
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
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
                        </div>
                        <!--begin::Card title-->
                        <div class="card-toolbar">
                            <a href="{{route("admin.invoices.create")}}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus me-1"></i>Fatura Oluştur
                            </a>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="invoiceTable" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="m-w-50">#</th>
                                <th class="min-w-50px">{{__("customer")}}</th>
                                <th class="min-w-125px">{{__("invoice_date")}}</th>
                                <th class="min-w-125px">{{__("amount")}}</th>
                                <th class="min-w-125px">Paraşüt</th>
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
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var t = $("#invoiceTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: true, targets: [0,1,2,3,4] },
                    { orderable: false, targets: 5 }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.invoices.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.showAllList = true
                        d.status = $(".statusTab.active").data("key")
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            function loadTabCounts() {
                $.get("{{ route('admin.invoices.statusCounts') }}", function(data) {
                    $('[data-status="all"]').text(data.total || 0);
                    $('[data-status="PENDING"]').text(data.pending || 0);
                    $('[data-status="PAID"]').text(data.paid || 0);
                    $('[data-status="CANCELLED"]').text(data.cancelled || 0);
                    $('[data-status="FORMALIZED"]').text(data.formalized || 0);
                });
            }
            loadTabCounts();

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".statusTab", function () {
                t.draw();
                loadTabCounts();
            })

            $(document).on("submit", "#userForm", function (e) {
                e.preventDefault()
                let form = $("#userForm");
                if ($("#phone").val() && !iti.isValidNumber()) {
                    Swal.fire({
                        title: "{{__('error')}}",
                        text: "{{__('invalid_phone_number_please_enter_a_valid_phone_number')}}",
                        icon: "error",
                        showConfirmButton: 0,
                        showCancelButton: 1,
                        cancelButtonText: "{{__('close')}}",
                    })
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    url: "{{route("admin.users.store")}}",
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
                            })
                            t.draw();
                            $("#addUserModal").modal("hide");
                            let form = $("#userForm");
                            resetForm(form);
                            form.find("[name='user_group_id']").val("").trigger("change")
                            form.find("[name='country_id']").val("").trigger("change")
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res.message ? res.message : "{{__('form_has_errors')}}",
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
        })
    </script>
@endsection
