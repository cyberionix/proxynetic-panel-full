@props([
"modalId" => "forceKycModal",
"formId" => "forceKycForm",
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
                <h2>KYC</h2>
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
                @if(auth()->user()->kyc?->status == "WAITING_FOR_CONFIRM")
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
                                <b class="fs-6 text-gray-700">
                                    Bilgilerinizi aldık. En kısa sürede incelenip değerlendirilecektir.
                                </b>
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                @else
                    @if(auth()->user()->kyc?->status == "NOT_CONFIRMED")
                        <div class="alert alert-danger">Bilgileriniz incelendi ve onaylanamadı! Tekrar deneyiniz.</div>
                    @endif
                    <form id="{{$formId}}" action="{{route('portal.users.checkKyc')}}" enctype="multipart/form-data">
                        @csrf
                        <!--begin::Scroll-->
                        <div class="scroll-y me-n7 pe-7" id="{{$modalId}}_scroll" data-kt-scroll="true"
                             data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#{{$modalId}}_header"
                             data-kt-scroll-wrappers="#{{$modalId}}_scroll" data-kt-scroll-offset="300px">
                            <div class="row g-5">
                                <div class="col-xl-12">
                                    @if($hardly)
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
                                                    <div class="fs-6 text-gray-700 ">
                                                        <b>Devam edebilmek için bilgilerinizi girmelisiniz.</b><br>
                                                        <ul class="mb-0">
                                                            <li>Yüklediğiniz görsellerin okunabilir olduğundan emin olun.</li>
                                                        </ul>

                                                    </div>
                                                </div>
                                                <!--end::Content-->
                                            </div>
                                            <!--end::Wrapper-->
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <!--begin::Label-->
                                    <label class="form-label required">Kimlik Ön Yüz</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="file" required class="form-control form-control-lg" name="card_front_side">
                                    <!--end::Input-->
                                </div>
                                <div class="col-12">
                                    <!--begin::Label-->
                                    <label class="form-label required">Kimlik Arka Yüz</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="file" required class="form-control form-control-lg" name="card_back_side">

                                    <!--end::Input-->
                                </div>
                                <div class="col-12">
                                    <!--begin::Label-->
                                    <label class="form-label required">Özçekim</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="file" required class="form-control form-control-lg" name="selfie">
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
                @endif
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
