@props([
"modalId" => "phoneVerifyModal",
"formId" => "phoneVerifyForm",
"hardly" => true
])
@push("css")
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">
    <style>
        .phoneArea .iti {
            width: 100%;
        }
    </style>
@endpush
@push("js")
    <script src="{{asset("js/plugins/intl-tel-input/intlTelInput.js")}}"></script>
    <script>
        @if($hardly)
        $(document).ready(function () {
            $("#{{$modalId}}").modal("show");
        })
        @endif
    </script>
    <script>
        const isPhoneNull = "{{auth()->user()->phone == null}}";

        function getVerificationCodeValue() {
            let code = '';
            $('.otp-code-input').each(function (index, element) {
                code += element.value;
            });
            return code;
        }

        function handleInput(element, currentIndex) {
            if (/^[0-9]$/.test(element.value)) {
                if (currentIndex < 6) {
                    document.querySelector(`input[maxlength="1"]:nth-child(${currentIndex + 1})`).focus();
                }
            } else if (element.value === "" && currentIndex > 1) {
                document.querySelector(`input[maxlength="1"]:nth-child(${currentIndex - 1})`).focus();
            }
            element.value = element.value.replace(/[^0-9]/g, '');
        }

        function verifyPhoneOTPCode() {
            if (!isPhoneNull) {
                $.ajax({
                    type: 'POST',
                    url: '{{route('portal.auth.verify_phone_otp')}}',
                    data: {
                        _token: '{{csrf_token()}}',
                        code: getVerificationCodeValue(),
                    },
                    dataType: 'json',
                    success: function (res) {

                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: 1,
                                confirmButtonText: "{{__('close')}}",
                            }).then(r => window.location.reload())

                            setTimeout(function () {
                                window.location.reload()
                            }, 3500)
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
                        // startCountdown(5);
                    }
                })
            }
        }

        $('input.otp-code-input.last').on('input', function (event) {
            var lastInputValue = event.target.value;
            if (event.inputType !== 'deleteContentBackward' && lastInputValue.trim() !== '') {
                verifyPhoneOTPCode();
            }
        })
    </script>
    <script>
        $(document).ready(function () {
            var countdown, time = "{{auth()->user()->getSmsOtpRemainingTime()}}";

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
                    $("[data-np-phone-verify='resend-time']").text("(" + formatTime(minutes) + ":" + formatTime(seconds) + ")");

                    // Zamanı azalt
                    remainingTime--;


                    if (remainingTime < 0) {
                        // Geri sayım tamamlandığında
                        clearInterval(countdown);
                        $("[data-np-phone-verify='send-otp-btn']").prop("disabled", false);
                        $("[data-np-phone-verify='resend-time']").text("");
                    }
                }, 1000);

                $("[data-np-phone-verify='send-otp-btn']").prop("disabled", true);
            }

            startCountdown(time)
        })
    </script>
    <script>
        $(document).ready(function () {
            let input = document.querySelector(".phoneInput");
            const iti = window.intlTelInput(input, itiOptions("phone"));
            iti.setNumber("{{auth()->user()?->phone ?? ''}}");

            $(document).on("submit", "#{{$formId}}", function (e) {
                e.preventDefault()

                if ($(".phoneInput").val() && !iti.isValidNumber()) {
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
<div class="modal fade" id="{{$modalId}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="{{$modalId}}_header">
                <!--begin::Modal title-->
                <h2>{{__("phone_verify")}}</h2>
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
                <form id="{{$formId}}" action="{{route('portal.users.savePhoneAndSendVerificationOTP')}}">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="scroll-y me-n7 pe-7" id="{{$modalId}}_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#{{$modalId}}_header"
                         data-kt-scroll-wrappers="#{{$modalId}}_scroll" data-kt-scroll-offset="300px">
                        <div class="row g-5">
                            <div class="col-xl-12">
                                @if($hardly)<br>
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
                                                    <ul class="mb-0">
                                                        <li>Devam edebilmek için telefon numaranızı doğrulamalısınız.
                                                        </li>
                                                        <li>Telefon numaranıza 6 haneli doğrulama kodu
                                                            gönderilecektir.
                                                        </li>
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
                                @if(auth()->user()->phone && !str_starts_with(auth()->user()->phone, "+90"))
                                    <div class="alert alert-primary">
                                        Telefon numaranız incelemede. En kısa sürede ekibimiz tarafından incelenip onaylanacaktır. Beklediğiniz için teşekkür ederiz.
                                    </div>
                                @else
                                    <div
                                        class="text-center {{!(auth()->user()->getSmsOtpRemainingTime() && auth()->user()->getSmsOtpRemainingTime() > 0) ? "d-none" : ""}}">
                                        <span class="fs-5">💬 <b>{{auth()->user()->phone}}</b> numaralı telefonu doğrulamak için bir SMS gönderdik. Gelen mesajda yer alan 6 haneli kodu aşağıdaki alana girerek telefon numaranızı doğrulayabilirsiniz.</span>
                                        <div class="d-flex justify-content-center mt-5">
                                            <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                                   placeholder="#"
                                                   maxlength="1" pattern="[0-9]" inputmode="numeric"
                                                   oninput="handleInput(this, 1)">
                                            <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                                   placeholder="#"
                                                   maxlength="1" pattern="[0-9]" inputmode="numeric"
                                                   oninput="handleInput(this, 2)">
                                            <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                                   placeholder="#"
                                                   maxlength="1" pattern="[0-9]" inputmode="numeric"
                                                   oninput="handleInput(this, 3)">
                                            <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                                   placeholder="#"
                                                   maxlength="1" pattern="[0-9]" inputmode="numeric"
                                                   oninput="handleInput(this, 4)">
                                            <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                                   placeholder="#"
                                                   maxlength="1" pattern="[0-9]" inputmode="numeric"
                                                   oninput="handleInput(this, 5)">
                                            <input type="text"
                                                   class="otp-code-input last form-control text-center fs-2 mx-1"
                                                   placeholder="#"
                                                   maxlength="1" pattern="[0-9]" inputmode="numeric"
                                                   oninput="handleInput(this, 6)">
                                        </div>
                                        <button type="button" class="btn btn-lg btn-success mt-5" id="verifyOtpButton"
                                                onclick="verifyPhoneOTPCode()">Doğrula
                                        </button>
                                    </div>
                                @endif
                                <div data-np-phone-verify="phone-input">
                                    <!--begin::Label-->
                                    <label class="form-label required">{{__("phone_number")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="phoneArea">
                                        <input type="tel"
                                               class="form-control form-control-lg phoneInput">
                                    </div>
                                    <!--end::Input-->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary mt-5" data-np-phone-verify='send-otp-btn'>
                                            <!--begin::Indicator label-->
                                            <span class="indicator-label"><i
                                                    class="fa fa-paper-plane me-2"></i>Doğrulama Kodu Gönder <span
                                                    data-np-phone-verify='resend-time'></span></span>
                                            <!--end::Indicator label-->
                                            <!--begin::Indicator progress-->
                                            <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            <!--end::Indicator progress-->
                                        </button>
                                    </div>
                                </div>
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
