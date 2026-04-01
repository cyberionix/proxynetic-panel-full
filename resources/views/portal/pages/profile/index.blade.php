@extends("portal.template")
@section("title", __("profile_information"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('profile_information')"/>
@endsection
@section("master")
    <!--begin::Layout-->
    <div class="d-flex flex-column flex-xl-row gap-8 gap-xl-13">
        <!--begin::Sidebar-->
        <div class="flex-column flex-lg-row-auto w-100 w-xl-350px">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body pt-15">
                    <!--begin::Summary-->
                    <div class="d-flex flex-center flex-column mb-5">
                        <!--begin::Avatar-->
                        <div class="symbol symbol-90px symbol-circle mb-3">
                                        <span class="symbol-label bg-light-secondary text-gray-900 fs-2qx fw-bolder ">
                                            {{ mb_substr(auth()->user()->first_name, 0, 1) }}
                                        </span>
                        </div>
                        <!--end::Avatar-->
                        <!--begin::Name-->
                        <div class="fs-3 text-gray-800 fw-bold mb-6">{{auth()->user()->fullName}}</div>
                        <!--end::Name-->
                    </div>
                    <!--end::Summary-->

                    <!--begin::Details toggle-->
                    <div class="d-flex flex-stack fs-4 py-3">
                        <div class="fw-bold">{{__("details")}}</div>
                    </div>
                    <!--end::Details toggle-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--begin::Details content-->
                    <div class="fs-6">
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{__("email_address")}}</div>
                        <div class="text-gray-600">{{auth()->user()->email}}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{__("phone_number")}}</div>
                        <div class="text-gray-600">{{auth()->user()->phone ?? "-"}}</div>
                        <!--begin::Details item-->
                    </div>
                    <!--end::Details content-->
                    <div class="separator separator-dashed my-3"></div>
                    <!--begin::Notification Perms-->
                    <div class="pb-5 fs-6 mt-5">
                        <div class="fs-4 fw-bold">{{__("notification_permissions")}}</div>
                        <div class="d-flex flex-stack mt-5">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa fa-sms fs-2hx"></i>
                                <div
                                    class="fs-6 text-gray-800 fw-bold">{{__("receive_sms_notifications")}}</div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom ">
                                    <!--begin::Input-->
                                    <input class="form-check-input" type="checkbox" value="sms"
                                           id="sms_notification_perm" {{auth()->user()->accept_sms ? "checked" : ""}}>
                                    <!--end::Input-->

                                    <!--begin::Label-->
                                    <span class="form-check-label fw-semibold text-muted"
                                          for="sms_notification_perm"></span>
                                    <!--end::Label-->
                                </label>
                            </div>
                        </div>
                        <div class="d-flex flex-stack mt-5">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa fa-envelope fs-2hx"></i>
                                <div
                                    class="fs-6 text-gray-800 fw-bold">{{__("receive_email_notifications")}}</div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <label
                                    class="form-check form-switch form-switch-sm form-check-custom ">
                                    <!--begin::Input-->
                                    <input class="form-check-input" type="checkbox" value="email"
                                           id="email_notification_perm" {{auth()->user()->accept_email ? "checked" : ""}}>
                                    <!--end::Input-->

                                    <!--begin::Label-->
                                    <span class="form-check-label fw-semibold text-muted"
                                          for="email_notification_perm"></span>
                                    <!--end::Label-->
                                </label>
                            </div>
                        </div>
                    </div>
                    <!--end::Notification Perms-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Sidebar-->
        <!--begin::Content-->
        <div class="flex-lg-row-fluid">
            <!--begin:::Tabs-->
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8 ms-4 gap-5">
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link mx-0 text-active-primary pb-4 active" data-bs-toggle="tab"
                       href="#information_form_tab">{{__("information_form")}}</a>
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
                       href="#change_password_tab">{{__("change_password")}}</a>
                </li>
                <!--end:::Tab item-->
            </ul>
            <!--end:::Tabs-->
            <!--begin:::Tab content-->
            <div class="tab-content" id="myTabContent">
                <!--begin:::Tab pane-->
                <div class="tab-pane fade show active" id="information_form_tab" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card pt-4 mb-6 mb-xl-9">
                        <!--begin::Card header-->
                        <div class="card-header border-0">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>{{__("information_form")}}</h2>
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
                                    <input type="text" required class="form-control form-control-lg" name="first_name" value="{{auth()->user()->first_name}}" {{auth()->user()->identity_number_verified_at ? "disabled" : ""}}>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label required">{{__("last_name")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" required class="form-control form-control-lg" name="last_name" value="{{auth()->user()->last_name}}" {{auth()->user()->identity_number_verified_at ? "disabled" : ""}}>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label">{{__("birth_date")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-portal.form-elements.date-input name="birth_date"
                                                                       :attr="auth()->user()->identity_number_verified_at ? 'disabled' : ''"
                                                                       :value="auth()->user()->birth_date"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label">{{__("tc_identity_number")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-lg" name="identity_number"
                                           {{auth()->user()->identity_number_verified_at ? "disabled" : ""}}
                                           value="{{auth()->user()->identity_number}}">
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
                        <div class="card-body pt-0 pb-5">
                            <div class="row g-5">
                                <div class="col-xxl-6">
                                    <a href="javascript:void(0);"
                                       data-url="{{route("portal.users.addresses.store")}}"
                                       class="card card-dashed h-xl-100 fs-5 fw-bold  p-6 bg-light-primary d-flex flex-center addAddressBtn">
                                        <div>
                                            <i class="fa fa-plus text-gray-900"></i> {{__("add_:name", ["name" => __("address")])}}
                                        </div>
                                    </a>
                                </div>
                                @foreach(auth()->user()->addresses as $address)
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
                                                        <span class="ms-1" data-bs-toggle="tooltip" title="{{$text}}">⭐</span>
                                                    @endif
                                                </div>
                                                <!--begin::Actions-->
                                                <div class="d-flex align-items-center py-2">
                                                    <!--begin::Edit-->
                                                    <div
                                                        class="btn btn-icon btn-sm btn-color-gray-500 btn-active-icon-danger me-2 deleteAddressBtn"
                                                        data-url="{{route("portal.users.addresses.delete", ["address" => $address->id])}}"
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
                                                        data-update-url="{{route("portal.users.addresses.update", ["address" => $address->id])}}"
                                                        data-find-url="{{route("portal.users.addresses.find", ["address" => $address->id])}}"
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
                </div>
                <!--end:::Tab pane-->
                <!--begin:::Tab pane-->
                <div class="tab-pane fade" id="change_password_tab" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card pt-4 mb-6 mb-xl-9">
                        <!--begin::Card header-->
                        <div class="card-header border-0">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>{{__("change_password")}}</h2>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <form action="{{route("portal.auth.changePassword")}}" id="changePasswordForm"
                              class="card-body pt-0 pb-5">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold required">{{__("old_password")}}</label>
                                <input type="password" name="old_password" autocomplete
                                       class="form-control form-control " required>
                            </div>
                            <div class="row g-4">
                                <div class="col-xl-6">
                                    <label
                                        class="form-label fw-bold required">{{__("new_password")}}</label>
                                    <input type="password" name="new_password" autocomplete
                                           class="form-control form-control " required>
                                </div>
                                <div class="col-xl-6">
                                    <label class="form-label fw-bold required">{{__("new_password")}}
                                        ({{__("again")}})</label>
                                    <input type="password" name="confirm_new_password" autocomplete
                                           class="form-control form-control " required>
                                </div>
                            </div>
                            <div class="mt-10">
                                <button type="submit" class="btn btn-primary w-100">
                                    <span class="indicator-label">{{__("save_changes")}}</span>
                                    <span class="indicator-progress">{{__("please_wait")}}...
                                                    <span
                                                        class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                                </span>
                                </button>
                            </div>
                        </form>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end:::Tab pane-->
            </div>
            <!--end:::Tab content-->
        </div>
        <!--end::Content-->
    </div>
    <x-portal.modals.primary-address-modal/>
    <!--end::Layout-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            /*<!-- start::user permissions-->*/
            $(document).on("change", "#sms_notification_perm, #email_notification_perm", function () {
                let element = $(this),
                    isChecked = $(this).is(":checked") ? 1 : 0,
                    url = "{{route("portal.users.updatePermission")}}",
                    type = $(this).val(),
                    swalText = "";

                switch (type) {
                    case "sms":
                        swalText = '{{__("update_notification_status", ["name" => "SMS"])}}';
                        break;
                    case "email":
                        swalText = '{{__("update_notification_status", ["name" => __("email")])}}';
                        break;
                }

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
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                isChecked: isChecked,
                                type: type
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
                    } else {
                        element.prop("checked", !isChecked);
                    }
                });
            })
            /*<!-- end::user permissions-->*/

            /*<!-- start::addresses-->*/
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
                            if(res.data.invoice_type == "CORPORATE"){
                                form.find("[name='identity_number']").val("")
                                form.find("[name='tax_number']").val(res.data.tax_number)
                            }else{
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
            /*<!-- end::addresses-->*/

            /*<!-- start::change password-->*/
            $(document).on("submit", "#changePasswordForm", function (e) {
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
            /*<!-- end::change password-->*/
        })
    </script>
@endsection
