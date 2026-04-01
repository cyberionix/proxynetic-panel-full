@props([
"modalId" => "requiredIdentityNumberModal",
"formId" => "requiredIdentityNumberForm",
"hardly" => true
])
<div class="modal fade" id="{{$modalId}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="{{$modalId}}_header">
                <!--begin::Modal title-->
                <h2>{{__("profile_information")}}</h2>
                <!--begin::Close-->
                <a href="{{route("portal.auth.logout")}}" class="btn btn-danger btn-sm"><i class="fa fa-sign-out fs-4"></i>Çıkış Yap</a>
                @if(!$hardly)
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                @endif
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body py-lg-10 px-lg-15">
                <form id="{{$formId}}" action="{{route('portal.users.storeIdentityNumber')}}">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="scroll-y me-n7 pe-7" id="{{$modalId}}_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#{{$modalId}}_header"
                         data-kt-scroll-wrappers="#{{$modalId}}_scroll" data-kt-scroll-offset="300px">
                        <div class="row g-5">
                            <div class="col-xl-12">
                                    <div
                                        class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-3 p-6">
                                        <!--begin::Icon-->
                                        <i class="ki-duotone ki-information fs-2tx text-primary me-4"><span
                                                class="path1"></span><span class="path2"></span><span
                                                class="path3"></span></i>
                                        <!--end::Icon-->
                                        <!--begin::Wrapper-->
                                        <div class="d-flex flex-stack flex-grow-1 ">
                                            <!--begin::Content-->
                                            <div class=" fw-semibold">
                                                @if(auth()->user()->address->country_id == 1)
                                                    <div class="fs-6 text-gray-700 ">
                                                        Devam edebilmek için kimlik doğrulaması yapmalısınız.
                                                    </div>
                                                    @else
                                                    <div class="fs-6 text-gray-700 ">
                                                        Devam edebilmek için bilgilerinizi girmelisiniz.
                                                    </div>
                                                @endif
                                            </div>
                                            <!--end::Content-->
                                        </div>
                                        <!--end::Wrapper-->
                                    </div>
                                @if(!empty(auth()->user()->not_tc_citizen_at) && empty(auth()->user()->identity_number_verified_at))
                                    <div class="alert alert-primary">
                                        Profil bilgileriniz ekibimiz tarafından inceleniyor. En kısa sürede değerlendirilip hesabınız aktif edilecektir.
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <div>
                                    <label class="form-check form-check-custom form-check-solid me-10">
                                        <input class="form-check-input h-30px w-30px" type="checkbox" name="is_not_tc_citizen" value="1" {{auth()->user()->not_tc_citizen_at ? "checked" : ""}}>
                                        <span class="form-check-label fw-semibold">
                                        TC Vatandaşı Değilim
                                    </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("first_name")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" required class="form-control form-control-lg" name="first_name"
                                       value="{{auth()->user()->first_name}}">
                                <!--end::Input-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("last_name")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" required class="form-control form-control-lg" name="last_name"
                                       value="{{auth()->user()->last_name}}">
                                <!--end::Input-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("birth_date")}} <span class="text-muted fs-7">({{__("day")}}/{{__("month")}}/{{__("year")}})</span></label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input class="form-control dateMask" name="birth_date" value="{{auth()->user()->birth_date}}">
                                <!--end::Input-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label">{{__("tc_identity_number")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" class="form-control form-control-lg" name="identity_number">
                                <!--end::Input-->
                            </div>
                        </div>
                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="d-flex flex-center flex-row-fluid pt-12">
                        @if(!$hardly)
                            <button type="reset" class="btn btn-light me-3"
                                    data-bs-dismiss="modal">{{__("cancel")}}</button>
                        @endif
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
        @if($hardly)
        $(document).ready(function () {
            $("#{{$modalId}}").modal("show");
        })
        @endif
        $(document).ready(function () {
            $(document).on("submit", "#{{$formId}}", function (e) {
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
                            alerts.success.fire({
                                text: res?.message ?? "",
                            }).then(r => window.location.reload());
                        } else {
                            alerts.error.fire({
                                text: res?.message ?? "",
                            })
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })
        })
    </script>
@endpush
