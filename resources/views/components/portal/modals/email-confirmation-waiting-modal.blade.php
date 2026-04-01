@props([
"modalId" => "emailConfirmationWaitingModal",
"formId" => "emailConfirmationWaitingForm",
"hardly" => true
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
                <h2>{{__("email_verify")}}</h2>
                <!--begin::Close-->
                <a href="{{route("portal.auth.logout")}}" class="btn btn-danger btn-sm"><i
                        class="fa fa-sign-out fs-4"></i>Çıkış Yap</a>
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
                                @if(!auth()->user()->getEmailOtpRemainingTime() || auth()->user()->getEmailOtpRemainingTime() == 0)
                                    📩 E-posta adresinizi doğrulamak için doğrulama e-postası alınız.
                                @else
                                    📩 E-posta adresinizi doğrulamak için <b>{{auth()->user()->email}}</b> adresine bir link gönderdik.
                                @endif
                            </b>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-5" data-np-email-verify='send-otp-btn'>
                        <!--begin::Indicator label-->
                        <span class="indicator-label"><i
                                class="fa fa-paper-plane me-2"></i>Doğrulama E-Postası Gönder <span
                                data-np-email-verify='resend-time'></span></span>
                        <!--end::Indicator label-->
                        <!--begin::Indicator progress-->
                        <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        <!--end::Indicator progress-->
                    </button>
                </div>
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

        $(document).ready(function (){
            $(document).on("click", "[data-np-email-verify='send-otp-btn']", function (){
                $.ajax({
                    type: 'POST',
                    url: "{{route("portal.auth.send_email_otp")}}",
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    beforeSend: function () {
                        propSubmitButton($("[data-np-email-verify='send-otp-btn']"), 1)
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            alerts.success.fire({
                                "text": res?.message ?? ""
                            }).then((r) => window.location.reload())
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                            propSubmitButton($("[data-np-email-verify='send-otp-btn']"), 0)
                        }
                    }
                })
            })
        })
    </script>

    <script>
        $(document).ready(function () {
            var countdown, time = "{{auth()->user()->getEmailOtpRemainingTime()}}";

            function formatTime(value) {
                return value < 10 ? "0" + value : value;
            }

            function startCountdown(seconds) {
                var remainingTime = seconds;

                clearInterval(countdown);
                countdown = setInterval(function () {
                    var minutes = Math.floor(remainingTime / 60);
                    var seconds = remainingTime % 60;

                    // Zamanı güncelle
                    $("[data-np-email-verify='resend-time']").text("(" + formatTime(minutes) + ":" + formatTime(seconds) + ")");

                    // Zamanı azalt
                    remainingTime--;


                    if (remainingTime < 0) {
                        // Geri sayım tamamlandığında
                        clearInterval(countdown);
                        $("[data-np-email-verify='send-otp-btn']").prop("disabled", false);
                        $("[data-np-email-verify='resend-time']").text("");
                    }
                }, 1000);

                $("[data-np-email-verify='send-otp-btn']").prop("disabled", true);
            }

            startCountdown(time)
        })
    </script>
@endpush
