@extends("portal.template")
@section("title", __("support_tickets") . ' ' . $support->draw_id)
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('support_tickets') . ' ' . $support->draw_id "/>
@endsection
@section("master")
    @if($support->is_locked == 1)
        <div class="alert alert-primary d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <!--begin::Icon-->
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span
                        class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <!--end::Icon-->
            </div>
            <!--begin::Wrapper-->
            <div class="d-flex align-items-center">
                <!--begin::Title-->
                <h6 class="mb-0 text-primary">{{__("locked_support_info_message")}}</h6>
                <!--end::Title-->
            </div>
            <!--end::Wrapper-->
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <!--begin::Support Information-->
            <div class="d-flex align-items-center ms-4 mb-9">
                <!--begin::Icon-->
                @if($support->status == "RESOLVED")
                    <i class="ki-duotone ki-file-added fs-3qx text-success ms-n2 me-3"><span
                            class="path1"></span><span class="path2"></span></i>
                @else
                    <i class="ki-duotone ki-add-files fs-3qx text-warning ms-n2 me-3"><span
                            class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                @endif
                <!--end::Icon-->

                <!--begin::Content-->
                <div class="d-flex flex-column">
                    <!--begin::Title-->
                    <h1 class="text-gray-800 fw-semibold">{{$support->subject}}</h1>
                    <!--end::Title-->

                    <!--begin::Info-->
                    <div class="">
                        <!--begin::Label-->
                        <span class="fw-semibold text-muted">{{__("Oluşturulma Tarihi")}}: <span
                                class="fw-bold text-gray-600 me-1">{{$support->created_at->format(defaultDateTimeFormat())}}</span></span>
                        <!--end::Label-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Content-->
            </div>
            <div class="row mb-9">
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("department")}}</label>
                            <div class="text-gray-500 fw-semibold fs-6">{{$support->drawDepartment}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("updated_date")}}</label>
                            <div
                                class="text-gray-500 fw-semibold fs-6">{{$support->updated_at->format(defaultDateTimeFormat())}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("status")}}</label>
                            <div class="text-gray-500 fw-semibold fs-6">{{$support->drawStatus}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("priority")}}</label>
                            <div class="text-gray-500 fw-semibold fs-6">{{$support->drawPriority}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!--begin::Support Information-->
            <!--begin::Send Message-->
            <div class="mb-9">
                <form id="sendMessageForm"
                      action="{{route("portal.supports.saveMessage", ["support" => $support->id])}}" class="mb-0">
                                <textarea
                                    {{$support->is_locked == 1 ? "disabled" : ""}}
                                    maxlength="1000"
                                    class="form-control form-control-solid placeholder-gray-600 fw-bold fs-4 ps-9 pt-7"
                                    rows="6" name="message" placeholder="Detaylı olarak mesajınızı yazınız"></textarea>
                    <!--begin::Submit-->
                    <button type="submit"
                            {{$support->is_locked == 1 ? "disabled" : ""}}
                            class="btn btn-primary mt-n20 mb-20 position-relative float-end me-7">{{__("send")}}
                    </button>
                    <!--end::Submit-->
                </form>
                <!--end::Textarea-->
            </div>
            <!--end::Send Message-->
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
                                    <div class="d-flex flex-column fw-semibold fs-5 text-gray-600 text-gray-900">
                                        <!--begin::Text-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Username-->
                                            <div class="text-gray-800 fw-bold fs-5 me-3" data-np-message="name"></div>
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
@endsection
@section("js")
    <script>
        $(document).ready(function () {

        })

        /* start::Messages*/
        $(document).ready(function () {
            let userFullName = "{{auth()->user()->full_name}}",
                itemTemplate = $("[data-np-message='item-template']");

            const getSupport = () => {
                $.ajax({
                    type: 'GET',
                    url: "{{ route("portal.supports.find", ["support" => $support->id]) }}",
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
                                    createdAt = moment(item.created_at).format("DD/MM/YYYY HH:mm:ss");

                                console.log(defaultDateTimeFormat())
                                itemTemplate.find("[data-np-message='name']").text(isAdmin ? item.admin.full_name : userFullName)
                                if (isAdmin) {
                                    itemTemplate.find("[data-np-message='badge']").removeClass("badge-primary").addClass("badge-success").text("{{__("staff")}}");
                                    itemTemplate.find("[data-np-message='date']").removeClass("badge-primary").addClass("badge-success").text(createdAt);
                                } else {
                                    itemTemplate.find("[data-np-message='badge']").removeClass("badge-success").addClass("badge-primary").text("Müşteri");
                                    itemTemplate.find("[data-np-message='date']").removeClass("badge-success").addClass("badge-primary").text(createdAt);
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
                let form = $(this), btn = form.find("[type='submit']");

                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        btn.prop("disabled", true)
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            getSupport()
                            form.find("textarea").val("")

                            toastr.success(res?.message ?? "", "{{__('success')}}");

                            setTimeout(() => {
                                btn.prop("disabled", false)
                            }, 10000)
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                            btn.prop("disabled", false)
                        }
                    }
                })
            })
        });
        /* end::Messages*/
    </script>
@endsection
