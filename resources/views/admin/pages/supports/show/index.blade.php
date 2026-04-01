@extends("admin.template")
@section("title", __("support_ticket"))
@section("css") @endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="[__('support_tickets') => route('admin.supports.index'), $support->subject]"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-5">
                <!--begin::Body-->
                <div class="card-body">
                    <div class="row mb-6">
                        <div class="col-xl-6">
                            <!--begin::Label-->
                            <span class="text-gray-800 fw-bold fs-6">{{__("customer")}}:</span>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <a target="_blank" href="{{route("admin.users.show", ["user" => $support->user_id])}}"
                               class="fs-4 text-primary fw-bold ms-1">{{$support->user->full_name}}</a>
                            <!--end::Input-->
                        </div>
                        <div class="col-xl-6 text-end">
                            @if($support->is_locked == 1)
                                <button class="btn btn-danger btn-sm lockBtn" data-url="{{route("admin.supports.unlock", ["support" => $support->id])}}" data-swal-text="Destek Talebinin kilidini kaldırmak istediğinize emin misiniz?"><i class="fa fa-lock-open me-1"></i>Kilidi Kaldır</button>
                            @else
                                <button class="btn btn-danger btn-sm lockBtn" data-url="{{route("admin.supports.lock", ["support" => $support->id])}}" data-swal-text="Destek Talebini kilitlemek istediğinize emin misiniz?"><i class="fa fa-lock me-1"></i>Kilitle</button>
                            @endif
                            <button class="btn btn-danger btn-sm deleteBtn" data-url="{{route("admin.supports.delete", ["support" => $support->id])}}" data-swal-text="Destek Talebini silmek istediğinize emin misiniz?"><i class="fa fa-trash me-1"></i>{{__("delete")}}</button>
                        </div>
                    </div>
                    <div class="separator mb-4"></div>
                    <div class="mb-5">
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">Hazır Mesaj Şablonu</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <x-portal.form-elements.select/>
                        <!--end::Select-->
                    </div>
                    <form id="sendMessageForm"
                          action="{{route("admin.supports.saveMessage", ["support" => $support->id])}}" class="mb-5">
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("message")}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="message"
                                  class="editorInput form-control mb-2"></textarea>
                        <!--end::Textarea-->
                        <div class="text-end">
                            <button type="submit" class="btn btn-light-primary mt-3">{{__("Yanıtla")}}</button>
                        </div>
                    </form>
                </div>
                <!--end::Body-->
            </div>
            <div class="card">
                <div class="card-body">
                    <!--begin::Messages-->
                    <div>
                        <div data-np-message="items"></div>
                        <div class="d-none" data-np-message="item-template">
                            <div class="card card-bordered w-100 mb-5" data-np-message="item">
                                <!--begin::Body-->
                                <div class="card-body">
                                    <!--begin::Wrapper-->
                                    <div class="w-100 d-flex flex-stack">
                                        <!--begin::Container-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Info-->
                                            <div
                                                class="d-flex flex-column fw-semibold fs-5 text-gray-600 text-gray-900">
                                                <!--begin::Text-->
                                                <div class="d-flex align-items-center">
                                                    <!--begin::Username-->
                                                    <div class="text-gray-800 fw-bold fs-5 me-3"
                                                         data-np-message="name"></div>
                                                    <!--end::Username-->
                                                    <span class="badge badge-success" data-np-message="badge"></span>
                                                </div>
                                                <!--end::Text-->
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Container-->

                                        <!--begin::Actions-->
                                        <div>
                                            <span class="badge badge-primary me-2" data-np-message="user-ip"></span>
                                            <span class="badge badge-success" data-np-message="date"></span>
                                        </div>
                                        <!--end::Actions-->
                                    </div>
                                    <!--end::Wrapper-->

                                    <div class="separator separator-dashed my-5"></div>

                                    <!--begin::Desc-->
                                    <p class="fw-normal fs-5 text-gray-700 m-0" data-np-message="message">
                                        I run a team of 20 product managers, developers, QA and UX Previously
                                        we designed everything ourselves.
                                    </p>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Body-->
                            </div>
                        </div>
                    </div>
                    <!--end::Messages-->
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div>
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("related_service")}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        @if($support->order)
                            <div>
                                <a class="text-primary fw-bold fs-4"
                                   target="_blank"
                                   href="{{route("admin.orders.show", ["order" => $support->order->id])}}">#{{$support->order->id}}</a>
                            </div>
                        @else
                            -
                        @endif
                        <!--end::Select-->
                    </div>
                    <div class="separator separator-dashed my-3"></div>
                    <div>
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("status")}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        @php
                            $statusSelectUrl = route("admin.supports.updateStatus", ["support" => $support->id]);
                        @endphp
                        <x-admin.form-elements.support-statuses-select name="support_status"
                                                                       required="required"
                                                                       customClass="statusSelect form-select-sm"
                                                                       customAttr="data-url='{{$statusSelectUrl}}' data-swal-text='Talep durumunu düzenlemek istediğinize emin misiniz?' data-current-val='{{$support->status}}'"
                                                                       :selectedOption="$support->status"
                                                                       :hideSearch="true"/>
                        <!--end::Select-->
                    </div>
                    <div class="separator separator-dashed my-3"></div>
                    <div>
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("department")}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        @php
                            $departmentSelectUrl = route("admin.supports.updateDepartment", ["support" => $support->id]);
                        @endphp
                        <x-portal.form-elements.department-select name="department"
                                                                  required="required"
                                                                  customClass="departmentSelect form-select-sm"
                                                                  customAttr="data-url='{{$departmentSelectUrl}}' data-swal-text='Departmanı düzenlemek istediğinize emin misiniz?' data-current-val={{$support->department}}"
                                                                  :selectedOption="$support->department"
                                                                  :hideSearch="true"/>
                        <!--end::Select-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("js")
    <script src="{{assetAdmin("plugins/custom/tinymce/tinymce.bundle.js")}}"></script>
    <script>
        $(document).ready(function () {
            tinymce.init({
                selector: ".editorInput",
                height: "300",
                plugins: [
                    "advlist autolink lists link charmap print preview anchor",
                    "searchreplace visualblocks code fullscreen",
                    "insertdatetime media table contextmenu paste imagetools wordcount textcolor colorpicker textpattern"
                ],
                toolbar: "styleselect fontselect fontsizeselect | bold italic underline forecolor backcolor colorpicker | link | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat code",
            });
            $(".editorInput[name='message']").html("<p>Saygılarımla<br />Sağlam Proxy Hizmetleri<br />Firma Yetkilisi<br />Whatsapp Destek Hattı : 0530 132 02 95</p>")
        })
    </script>

    <script>
        $(document).ready(function () {
            let userFullName = "{{$support->user->full_name}}",
                itemTemplate = $("[data-np-message='item-template']");

            const getSupport = () => {
                $.ajax({
                    type: 'GET',
                    url: "{{ route("admin.supports.find", ["support" => $support->id]) }}",
                    data: {
                        _token: '{{csrf_token()}}'
                    },
                    beforeSend: function () {
                        //
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res.success === true) {
                            $("[data-np-message='items']").html("");
                            res.data.messages.map((item) => {
                                let isAdmin = !!item.admin_id,
                                    createdAt = moment(item.created_at).format(defaultDateTimeFormat());
                                itemTemplate.find("[data-np-message='name']").text(isAdmin ? item.admin.full_name : userFullName)
                                if (isAdmin) {
                                    itemTemplate.find("[data-np-message='badge']").removeClass("badge-primary").addClass("badge-success").text("{{__("staff")}}");
                                    itemTemplate.find("[data-np-message='date']").removeClass("badge-primary").addClass("badge-success").text(createdAt);
                                    itemTemplate.find("[data-np-message='user-ip']").addClass("d-none")

                                } else {
                                    itemTemplate.find("[data-np-message='badge']").removeClass("badge-success").addClass("badge-primary").text("Müşteri");
                                    itemTemplate.find("[data-np-message='date']").removeClass("badge-success").addClass("badge-primary").text(createdAt);
                                    itemTemplate.find("[data-np-message='user-ip']").text("IP:  "  + item?.user_ip);
                                    itemTemplate.find("[data-np-message='user-ip']").removeClass("d-none")
                                }
                                itemTemplate.find("[data-np-message='message']").html(item.message)

                                $("[data-np-message='items']").append($("[data-np-message='item-template']").html());
                            })
                        } else {
                            console.log("Mesajlar çekilirken bir sorun oluştu.")
                        }
                    }
                })
            }
            getSupport();

            $(document).on("submit", "#sendMessageForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    btn = form.find("btn[type='submit']");

                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(btn, 1)
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            getSupport()
                            form.find("textarea").val("")

                            toastr.success(res?.message ?? "", "{{__('success')}}");
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
                        setTimeout(() => propSubmitButton(btn, 0), 3000)
                    }
                })
            })

            $(document).on("change", ".statusSelect, .departmentSelect", function () {
                let element = $(this),
                    ajaxUrl = element.data("url"),
                    swalText = element.data("swal-text"),
                    currentVal = element.data("current-val");
                if (currentVal != element.val()) {
                    alerts.confirm.fire({
                        title: "{{__('warning')}}",
                        text: swalText,
                        confirmButtonText: "{{__('yes')}}",
                    }).then((result) => {
                        if (result.isConfirmed === true) {
                            $.ajax({
                                type: "POST",
                                url: ajaxUrl,
                                dataType: "json",
                                data: {
                                    _token: "{{csrf_token()}}",
                                    value: element.val()
                                },
                                beforeSend: function () {
                                    element.prop("disabled", true)
                                },
                                complete: function (data, status) {
                                    element.prop("disabled", false)
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        alerts.success.fire({
                                            title: "{{__('success')}}",
                                            text: res?.message ?? "",
                                        }).then((r) => window.location.reload())
                                    } else {
                                        alerts.error.fire({
                                            title: "{{__('error')}}",
                                            text: res?.message ?? "",
                                        }).then((r) => window.location.reload())
                                    }
                                }
                            })
                        } else {
                            element.val(currentVal).trigger("change")
                        }
                    });
                }
            })
            $(document).on("click", ".lockBtn, .deleteBtn", function () {
                let element = $(this),
                    ajaxUrl = element.data("url"),
                    swalText = element.data("swal-text"),
                    currentVal = element.data("current-val");
                alerts.confirm.fire({
                    title: "{{__('warning')}}",
                    html: swalText,
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: ajaxUrl,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                value: element.val()
                            },
                            beforeSend: function () {
                                element.prop("disabled", true)
                            },
                            complete: function (data, status) {
                                element.prop("disabled", false)
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => {
                                        if(res?.redirectUrl){
                                            window.location.href = res.redirectUrl
                                        }else{
                                            window.location.reload()
                                        }
                                    })
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "",
                                    })
                                }
                            }
                        })
                    } else {
                        element.val(currentVal).trigger("change")
                    }
                });
            })
        })
    </script>
@endsection
