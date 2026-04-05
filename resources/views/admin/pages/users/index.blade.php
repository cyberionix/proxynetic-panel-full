@extends("admin.template")
@section("title", __("customers"))
@section("css")
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
    <style>
        .phoneArea .iti {
            width: 100%;
        }
        @media (max-width: 768px) {
            #kt_app_content_container.container-xxl { max-width: 100% !important; padding-left: 5px !important; padding-right: 5px !important; }
            .card-body { padding-left: 6px !important; padding-right: 6px !important; }
            .card-header { padding-left: 8px !important; padding-right: 8px !important; flex-direction: column; align-items: flex-start !important; gap: 6px; }
            #usersTable th.col-hide-mobile,
            #usersTable td.col-hide-mobile { display: none !important; }
            #usersTable { font-size: 11px; }
            #usersTable th { min-width: auto !important; font-size: 10px; padding: 4px 3px !important; }
            #usersTable td { padding: 5px 3px !important; word-break: break-all; }
            #usersTable td a { font-size: 11px; }
            #usersTable .badge { font-size: 10px; }
            #usersTable .btn { font-size: 10px; padding: 3px 6px; }
            .table-responsive { overflow-x: hidden; }
            .table-filter-area { padding-left: 8px !important; padding-right: 8px !important; }
            [data-table-action="search"] { font-size: 12px; width: 100% !important; }
        }
    </style>
@endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('customers')"/>
@endsection
@section("master")
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
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end">
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-primary addBtn" data-bs-toggle="modal"
                            data-bs-target="#addUserModal"><i
                            class="fa fa-plus fs-5"></i> {{__("add_:name", ["name" => __("customer")])}}</button>
                    <!--end::Add customer-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Filter-->
        <div class="d-flex flex-wrap gap-3 px-10 my-3 table-filter-area">
            <div class="w-200px">
                <!--begin::Label-->
                <label class="form-label fw-semibold">{{__("customer_group")}}</label>
                <!--end::Label-->
                <!--begin::Input-->
                <x-admin.form-elements.user-group-select :isSolid="false"
                                                         name="user_groups"
                                                         :placeholder="__('all_:name', ['name'=> __('customer_groups')])"
                                                         customClass="form-select-sm table-filter-item"
                                                         customAttr="multiple"/>
                <!--end::Input-->
            </div>
        </div>
        <!--end::Filter-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table id="usersTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                    <th class="m-w-50">#</th>
                    <th class="min-w-125px">{{__("name")}} {{__("surname")}}</th>
                    <th class="min-w-125px">{{__("email")}}</th>
                    <th class="min-w-125px col-hide-mobile">{{__("last_login_ip")}}</th>
                    <th class="min-w-125px col-hide-mobile">{{__("customer_group")}}</th>
                    <th class="min-w-125px col-hide-mobile">{{__("last_seen_at")}}</th>
                    <th class="min-w-125px col-hide-mobile"></th>
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
    <x-admin.modals.primary-user-modal modalId="addUserModal"
                                       :modalTitle="__('custom_blank_create', ['name' => __('customer')])"
                                       formId="userForm"/>
    <!--end::Modals-->
@endsection
@section("js")
    <script src="{{asset("js/plugins/intl-tel-input/intlTelInput.js")}}"></script>
    <script>
        $(document).ready(function () {
            let input = document.querySelector(".phoneInput");
            const iti = window.intlTelInput(input, itiOptions("phone"));

            var t = $("#usersTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: true, targets: [0, 1, 2, 3, 4, 5] },
                    { orderable: false, targets: [6] },
                    { className: 'col-hide-mobile', targets: [3, 4, 5, 6] }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.users.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        $(".table-filter-area .table-filter-item").each(function (index, item) {
                            let name = $(item).attr('name'),
                                value = $(item).val();

                            if ($(item).is(':checkbox')) {
                                value = $(item).prop('checked') ? '1' : '0';
                            }

                            if (value) {
                                d[name] = value;
                            }
                        })
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                if (window.innerWidth <= 768) {
                    $('#usersTable tbody tr').css('cursor', 'pointer');
                }
            });

            if (window.innerWidth <= 768) {
                $(document).on('click', '#usersTable tbody td', function(e) {
                    if ($(e.target).is('a, button, input') || $(e.target).closest('a, button, .form-check').length) return;
                    var link = $(this).closest('tr').find('td a').first().attr('href');
                    if (link) window.location.href = link;
                });
            }

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on('change', '.table-filter-area .table-filter-item', function () {
                t.draw();
            })

            $(document).on("submit", "#userForm", function (e) {
                e.preventDefault()
                let form = $("#userForm");
                if ($("#userForm .phoneInput").val() && !iti.isValidNumber()) {
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
                                text: res?.message ?? "",
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
                                text: res?.message ?? "{{__('form_has_errors')}}",
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

            $(document).on("click", ".deleteBtn", function () {
                let id = $(this).closest("tr").find("td:first span").attr("data-id"),
                    url = `{{ route('admin.users.delete', ['user' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

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
                                    });
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
