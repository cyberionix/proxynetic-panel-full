@extends("admin.template")
@section("title", __("alerts"))
@section("css") @endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('alerts')"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <div class="alert alert-primary">
        Eklenen uyarılar, bitiş tarihine kadar kullanıcıların Anasayflarında görünür.
    </div>
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
                <!--begin::Add-->
                <button type="button" class="btn btn-primary addAlertBtn" data-url="{{route("admin.alerts.store")}}"><i
                        class="fa fa-plus fs-5"></i> {{__("add_:name", ["name" => __("alert")])}}</button>
                <!--end::Add-->
            </div>
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table id="dataTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                    <th class="m-w-50">#</th>
                    <th class="min-w-50px">{{__("message")}}</th>
                    <th class="min-w-125px">Arka Plan Renk</th>
                    <th class="min-w-125px">{{__("start_date")}}</th>
                    <th class="min-w-125px">{{__("end_date")}}</th>
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
    <div class="modal fade" id="primaryAlertModal" data-bs-backdrop="static"
         data-bs-keyboard="false" tabindex="-1"
         aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header" id="primaryAlertModal_header"
                     data-add-text="{{__("add_:name", ["name" => __("alert")])}}"
                     data-edit-text="{{__("edit_:name", ["name" => __("alert")])}}">
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
                    <form id="primaryAlertForm">
                        @csrf
                        <!--begin::Scroll-->
                        <div class="scroll-y me-n7 pe-7" id="primaryAlertModal_scroll"
                             data-kt-scroll="true"
                             data-kt-scroll-activate="{default: false, lg: true}"
                             data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#primaryAlertModal_header"
                             data-kt-scroll-wrappers="#primaryAlertModal_scroll"
                             data-kt-scroll-offset="300px">
                            <div class="row g-5">
                                <div class="col-12">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("message")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control" name="message" rows="2" required></textarea>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("start_date")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.date-input name="start_date"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("end_date")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.date-input name="end_date"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-12">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("Arka Plan Renk")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row g-5">
                                        <div class="col-4">
                                            <div class="form-check form-check-custom">
                                                <input class="form-check-input" type="radio" name="bg_color" value="success" id="flexRadioDefault0" required="">
                                                <label class="form-check-label" for="flexRadioDefault0">
                                                    <span class="exampleCategoryText badge badge-success w-80px h-30px"> </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="form-check form-check-custom">
                                                <input class="form-check-input" type="radio" name="bg_color" value="primary" id="flexRadioDefault1" required="">
                                                <label class="form-check-label" for="flexRadioDefault1">
                                                    <span class="exampleCategoryText badge badge-primary w-80px h-30px"> </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-xl-4">
                                            <div class="form-check form-check-custom">
                                                <input class="form-check-input" type="radio" name="bg_color" value="danger" id="flexRadioDefault2" required="">
                                                <label class="form-check-label" for="flexRadioDefault2">
                                                    <span class="exampleCategoryText badge badge-danger w-80px h-30px"> </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Input-->
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
                    },
                    {
                        orderable: !1, targets: 5
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.alerts.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".addAlertBtn", function () {
                let modal = $("#primaryAlertModal"),
                    url = $(this).data("url"),
                    form = $("#primaryAlertForm"),
                    header = $("#primaryAlertModal_header");

                form.find("[name='message']").html("").trigger("change")
                form.find("[name='bg_color']:first").prop("checked", true)
                form.find("[name='start_date']").val("").trigger("change")
                form.find("[name='end_date']").val("").trigger("change")

                form.attr("action", url);
                header.find("h2").text(header.data("add-text"));
                modal.modal("show");
            })
            $(document).on("click", ".editAlertBtn", function () {
                let modal = $("#primaryAlertModal"),
                    findUrl = $(this).data("find-url"),
                    updateUrl = $(this).data("update-url"),
                    form = $("#primaryAlertForm"),
                    header = $("#primaryAlertModal_header");

                $.ajax({
                    type: 'GET',
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
                            form.find("[name='message']").html(res.data.message).trigger("change")
                            form.find("[name='start_date']").val(res.data.draw_start_date).trigger("change")
                            form.find("[name='end_date']").val(res.data.draw_end_date).trigger("change")
                            form.find("[name='bg_color'][value='" + res.data.bg_color + "']").prop("checked", true)

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
            $(document).on("click", ".deleteAlertBtn", function () {
                let url = $(this).data("delete-url");

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
                                        text: res.message,
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    })
                                    t.draw()
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
                            }
                        })
                    }
                });
            })


            $(document).on("submit", "#primaryAlertForm", function (e) {
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
                            })
                            $("#primaryAlertModal").modal("hide")
                            t.draw()
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
        })
    </script>
@endsection
