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
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="text" data-table-action="search"
                                       class="form-control w-250px ps-13"
                                       placeholder="{{__("search_in_table")}}"/>
                            </div>
                        </div>
                        <div class="card-toolbar gap-3">
                            <div id="bulkActionBar" class="d-none d-flex align-items-center gap-2">
                                <span class="fw-semibold text-gray-700 me-1"><span id="selectedCount">0</span> seçili</span>
                                <button class="btn btn-sm btn-light-success bulk-btn" data-action="mark_paid">
                                    <i class="fa fa-check me-1"></i>Ödendi Yap
                                </button>
                                <button class="btn btn-sm btn-light-warning bulk-btn" data-action="mark_pending">
                                    <i class="fa fa-clock me-1"></i>Bekliyor Yap
                                </button>
                                <button class="btn btn-sm btn-light-secondary bulk-btn" data-action="mark_cancelled">
                                    <i class="fa fa-ban me-1"></i>İptal Et
                                </button>
                                <button class="btn btn-sm btn-light-danger bulk-btn" data-action="delete">
                                    <i class="fa fa-trash me-1"></i>Sil
                                </button>
                            </div>
                            <a href="{{route("admin.invoices.create")}}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus me-1"></i>Fatura Oluştur
                            </a>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <table id="invoiceTable" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                    </div>
                                </th>
                                <th class="m-w-50">#</th>
                                <th class="min-w-50px">{{__("customer")}}</th>
                                <th class="min-w-125px">{{__("invoice_date")}}</th>
                                <th class="min-w-125px">{{__("amount")}}</th>
                                <th class="min-w-125px">{{__("status")}}</th>
                                <th class="min-w-125px">{{__("action")}}</th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">

                            </tbody>
                        </table>
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
                    { orderable: false, targets: [0, 6] },
                    { orderable: true, targets: [1,2,3,4,5] }
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
                $('#checkAll').prop('checked', false);
                updateBulkBar();
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

            function getSelectedIds() {
                var ids = [];
                $('.bulk-check:checked').each(function() { ids.push($(this).val()); });
                return ids;
            }

            function updateBulkBar() {
                var ids = getSelectedIds();
                $('#selectedCount').text(ids.length);
                if (ids.length > 0) {
                    $('#bulkActionBar').removeClass('d-none');
                } else {
                    $('#bulkActionBar').addClass('d-none');
                }
            }

            $(document).on('change', '#checkAll', function() {
                $('.bulk-check').prop('checked', $(this).is(':checked'));
                updateBulkBar();
            });

            $(document).on('change', '.bulk-check', function() {
                if (!$(this).is(':checked')) $('#checkAll').prop('checked', false);
                updateBulkBar();
            });

            $(document).on('click', '.bulk-btn', function() {
                var action = $(this).data('action');
                var ids = getSelectedIds();
                if (ids.length === 0) return;

                var messages = {
                    'mark_paid': ids.length + ' faturayı ödendi olarak işaretlemek istediğinize emin misiniz?',
                    'mark_pending': ids.length + ' faturayı bekliyor olarak işaretlemek istediğinize emin misiniz?',
                    'mark_cancelled': ids.length + ' faturayı iptal etmek istediğinize emin misiniz?',
                    'delete': ids.length + ' faturayı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.'
                };

                Swal.fire({
                    title: 'Toplu İşlem',
                    text: messages[action] || 'Emin misiniz?',
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
                                    t.draw();
                                    loadTabCounts();
                                } else {
                                    Swal.fire({ title: 'Hata', text: res.message, icon: 'error' });
                                }
                            }
                        });
                    }
                });
            });

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
