@props([
"modalId" => "createSupportModal",
"formId" => "createSupportForm",
"action" => route("portal.supports.store")
])
<div class="modal fade" id="{{$modalId}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="{{$modalId}}_header">
                <!--begin::Modal title-->
                <h2>{{__("create_:name", ["name" => __("support_ticket")])}}</h2>
                <!--end::Modal title-->
                <div class="d-flex align-items-center gap-1">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body py-lg-10 px-lg-15">
                <form id="{{$formId}}" action="{{$action}}"
                      data-update-url="{{route("portal.supports.store")}}">
                    @csrf
                    <input type="hidden" name="id">
                    <!--begin::Scroll-->
                    <div class="scroll-y me-n7 pe-7" id="{{$modalId}}_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#{{$modalId}}"
                         data-kt-scroll-wrappers="#{{$modalId}}_scroll" data-kt-scroll-offset="300px">
                        <div class="row g-3">
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{__("subject")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="subject" class="form-control" maxlength="70" required>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{__("department")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-portal.form-elements.department-select name="department"
                                                                          required="required"
                                                                          dropdownParent="#{{$modalId}}"
                                                                          :hideSearch="true"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label">{{__("product")}} / {{__("service")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-portal.form-elements.my-order-select name="order_id"
                                                                        dropdownParent="#{{$modalId}}"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="required form-label">{{__("priority")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-portal.form-elements.priority-select name="priority"
                                                                        dropdownParent="#{{$modalId}}"
                                                                        required="required"
                                                                        :hideSearch="true"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-12">
                                <!--begin::Label-->
                                <label
                                    class="required form-label">{{__("please_write_your_message_in_detail")}}</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea name="message" rows="5" class="form-control" maxlength="1000"
                                          required></textarea>
                                <!--end::Textarea-->
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

@push("js")
    <script>
        $(document).ready(function () {
            $(document).on("click", "[data-np-btn='create-support']", function (){
                $("#{{$modalId}}").modal("show");
            })
            $(document).on("submit", "#{{$formId}}", function (e) {
                e.preventDefault()
                let form = $(this), btn = form.find("button[type='submit']");

                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(btn, 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            alerts.success.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                cancelButtonText: "{{__('close')}}",
                            }).then((r) => window.location.href = res.redirectUrl);
                        } else {
                            alerts.error.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                        propSubmitButton(btn, 0);
                    }
                })
            })
        })
    </script>
@endpush
