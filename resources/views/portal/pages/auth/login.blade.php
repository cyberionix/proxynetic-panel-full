<!DOCTYPE html>
<html lang="tr" data-bs-theme="light"><!--begin::Head-->
<head>
    <base href="">
    <title>Giriş Yap | {{config('brand.name')}}</title>
    <meta charset="utf-8">
    <meta name="description" content="{{config('brand.meta.description')}}">
    <meta name="keywords" content="{{config('brand.meta.keywords')}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Kayıt Ol | {{config('brand.name')}}">
    <meta property="og:url" content="{{url()->current()}}">
    <meta property="og:site_name" content="{{config('brand.name')}}">
        <link rel="shortcut icon" href="{{url(config('brand.favicon'))}}"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">
    <link href="{{assetPortal('')}}/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css">
    <link href="{{assetPortal('')}}/css/style.bundle.css" rel="stylesheet" type="text/css">
    <script>// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_body" class="auth-bg bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat" style="
    background:  linear-gradient(163deg, rgba(104,96,255,1) 0%, rgba(64,131,255,1) 43%, rgba(255,152,150,1) 100%);
">
<!--begin::Theme mode setup on page load-->
<script>var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }</script>
<!--end::Theme mode setup on page load-->
<!--begin::Main-->
<!--begin::Root-->
<div class="d-flex flex-column flex-root">
    <!--begin::Authentication - Sign-up -->
    <div class="d-flex flex-column flex-column-fluid flex-lg-row">
        <!--begin::Aside-->
        <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
            <!--begin::Aside-->
            <div class="d-flex flex-center flex-column p-8" style="border-radius: 15px; background-color: #21283a">
                <!--begin::Logo-->
                <a href="{{route("portal.auth.login")}}">
                    <img alt="Logo" src="{{url(brand('logo'))}}" style="max-width: 350px;">
                </a>
                <!--end::Logo-->
                <!--begin::Title-->
                <h2 class="text-white fw-normal m-0">{{brand('clientarea_title')}}</h2>
                <!--end::Title-->
            </div>
            <!--begin::Aside-->
        </div>
        <!--begin::Aside-->
        <!--begin::Body-->
        <div
            class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
            <!--begin::Card-->
            <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-md-20 p-5">
                @if(session('error'))
                    <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mb-3 p-6">
                        <!--begin::Icon-->
                        <i class="ki-duotone ki-information fs-2tx text-danger me-4"><span class="path1"></span><span
                                class="path2"></span><span class="path3"></span></i>
                        <!--end::Icon-->
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-stack flex-grow-1 ">
                            <!--begin::Content-->
                            <div class=" fw-semibold">
                                <b class="fs-6 text-gray-700">{{session('error')}}</b>
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                @endisset
                <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">
                    <!--begin::Form-->
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form">
                        @csrf
                        <!--begin::Heading-->
                        <div class="text-center mb-11">
                            <!--begin::Title-->
                            <h1 class="text-gray-900 fw-bolder mb-3">{{__('login')}}</h1>
                            <!--end::Title-->
                            <!--begin::Subtitle-->
                            <div class="text-gray-500 d-none fw-semibold fs-6"></div>
                            <!--end::Subtitle=-->
                        </div>
                        <!--begin::Heading-->
                        <!--begin::Login options-->
                        <div class="row g-3 mb-9 d-none flex-center">
                            <!--begin::Col-->
                            <div class="col-md-6">
                                <!--begin::Google link=-->
                                <a href="{{route('portal.auth.login.google.redirect')}}"
                                   class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                    <img alt="Google" src="{{assetPortal('')}}/media/svg/brand-logos/google-icon.svg"
                                         class="h-15px me-3">{{__('login_with_google')}}</a>
                                <!--end::Google link=-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Login options-->
                        <!--begin::Separator-->
                        <div class="separator separator-content my-14 d-none">
                            <span
                                class="w-275px text-gray-500 fw-semibold fs-7">{{__('or')}} E-Posta-Telefon Numarası ile giriş yap</span>
                        </div>
                        <!--end::Separator-->
                        <!--begin::Input group=-->
                        <div class="fv-row mb-8">
                            <!--begin::Email-->
                            <input type="text" placeholder="E-Posta veya Telefon Numarası" name="email"
                                   autocomplete="off"
                                   value=""
                                   class="form-control bg-transparent">
                            <!--end::Email-->
                        </div>
                        <!--end::Input group=-->
                        <div class="fv-row mb-3">
                            <!--begin::Password-->
                            <input class="form-control bg-transparent" type="password"
                                   placeholder="{{__('password')}}" name="password" autocomplete="off">
                            <!--end::Password-->
                        </div>
                        <!--end::Input group=-->
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <div></div>
                            <!--begin::Link-->
                            <a href="javascript:void(0);"
                               class="link-primary forgotPasswordBtn">{{__("forgot_password")}}</a>
                            <!--end::Link-->
                        </div>
                        <!--begin::Submit button-->
                        <div class="d-grid mb-10">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{__("login")}}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">Please wait...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <!--end::Submit button-->
                        <!--begin::Sign up-->
                        <div class="text-gray-500 text-center fw-semibold fs-6">{{__("not_a_member_yet")}}
                            <a href="{{route("portal.auth.register")}}" class="link-primary">{{__("register")}}</a>
                        </div>
                        <!--end::Sign up-->
                    </form>
                    <!--end::Form-->
                    <!--begin::Form-->
                    <div class="generalResetPasswordArea form w-100" style="display: none">
                        <!--begin::Heading-->
                        <div class="text-center mb-11">
                            <!--begin::Title-->
                            <h1 class="text-gray-900 fw-bolder mb-3">Parolanızı mı unuttunuz ?</h1>
                            <!--end::Title-->
                            <!--begin::Subtitle-->
                            <div class="text-gray-500 fw-semibold fs-6"></div>
                            <!--end::Subtitle=-->
                        </div>
                        <!--begin::Heading-->
                        <form id="otpSendForm" class="otpSendArea">
                            @csrf
                            <!--begin::Separator-->
                            <div class="separator separator-content my-14">
                            <span
                                class="w-275px text-gray-500 fw-semibold fs-7">Parolanızı sıfırlamak için e-posta adresinizi girin.</span>
                            </div>
                            <!--end::Separator-->
                            <!--begin::Input group=-->
                            <div class="fv-row mb-8">
                                <!--begin::Email-->
                                <input type="text" placeholder="{{__('email')}}" name="email" autocomplete="off"
                                       value=""
                                       class="form-control bg-transparent">
                                <!--end::Email-->
                            </div>
                            <!--end::Input group=-->
                            <!--begin::Submit button-->
                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">Sıfırlama E-Postası Gönder</span>
                                    <!--end::Indicator label-->
                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    <!--end::Indicator progress-->
                                </button>
                            </div>
                            <!--end::Submit button-->
                        </form>
                        <div class="otpEntryArea" style="display: none;">
                            <div class="alert alert-primary"></div>
                            <!--begin::Input group=-->
                            <div class="fv-row mb-8">
                                <div class="d-flex justify-content-center mt-5">
                                    <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                           placeholder="#" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           oninput="handleInput(this, 1)">
                                    <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                           placeholder="#" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           oninput="handleInput(this, 2)">
                                    <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                           placeholder="#" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           oninput="handleInput(this, 3)">
                                    <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                           placeholder="#" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           oninput="handleInput(this, 4)">
                                    <input type="text" class="otp-code-input form-control text-center fs-2 mx-1"
                                           placeholder="#" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           oninput="handleInput(this, 5)">
                                    <input type="text" class="otp-code-input last form-control text-center fs-2 mx-1"
                                           placeholder="#" maxlength="1" pattern="[0-9]" inputmode="numeric"
                                           oninput="handleInput(this, 6)">
                                </div>
                                <button type="button" class="btn btn-lg btn-success w-100 mt-5" id="verifyOtpButton"
                                        onclick="verifyForgotPasswordOTPCode()">Doğrula
                                </button>
                                <div class="text-end mt-3">
                                    <button class="btn btn-sm btn-primary fw-bold" disabled id="resendButton">Tekrar
                                        Gönder <span class="resendTime">(02:00)</span></button>
                                </div>
                            </div>
                            <!--end::Input group=-->
                        </div>
                        <form id="resetPasswordForm" class="resetPasswordArea" style="display: none;">
                            @csrf
                            <div class="alert alert-success"></div>
                            <!--begin::Input group=-->
                            <div class="fv-row mb-8">
                                <!--begin::Email-->
                                <input type="password" placeholder="Yeni parola" name="new_password" autocomplete="off"
                                       value=""
                                       class="form-control bg-transparent">
                                <!--end::Email-->
                            </div>
                            <!--end::Input group=-->
                            <!--begin::Input group=-->
                            <div class="fv-row mb-8">
                                <!--begin::Email-->
                                <input type="password" placeholder="Yeni parola tekrar" name="confirm_new_password"
                                       autocomplete="off"
                                       value=""
                                       class="form-control bg-transparent">
                                <!--end::Email-->
                            </div>
                            <!--end::Input group=-->
                            <!--begin::Submit button-->
                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">Parolayı Güncelle</span>
                                    <!--end::Indicator label-->
                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    <!--end::Indicator progress-->
                                </button>
                            </div>
                            <!--end::Submit button-->
                        </form>
                        <!--begin::Sign up-->
                        <div class="text-gray-500 text-center fw-semibold fs-6">Giriş ekranına geri mi dönmek
                            istiyorsunuz?
                            <a href="javascript:void(0);" class="link-primary showLogin">{{__("login")}}</a>
                        </div>
                        <!--end::Sign up-->
                    </div>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Body-->
    </div>
    <!--end::Authentication - Sign-up-->
</div>
<!--end::Root-->
<!--end::Main-->
<!--begin::Javascript-->
<script>
    var hostUrl = "{{assetPortal('')}}/";
    const defaultDateFormat = () => {
        return "{{defaultDateFormat()}}";
    };
</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{assetPortal('')}}/plugins/global/plugins.bundle.js"></script>
<script src="{{assetPortal('')}}/js/scripts.bundle.js"></script>
<!--end::Global Javascript Bundle-->

<script src="{{assetPortal("")}}/js/custom.js"></script>
<!--end::Javascript-->
</body>
<!--end::Body-->
<script>
    let otpCode = "", countdown;
    const getVerificationCodeValue = () => {
        let code = '';
        $('.otp-code-input').each(function (index, element) {
            code += element.value;
        });
        return code;
    }
    const handleInput = (element, currentIndex) => {
        if (/^[0-9]$/.test(element.value)) {
            if (currentIndex < 6) {
                document.querySelector(`input[maxlength="1"]:nth-child(${currentIndex + 1})`).focus();
            }
        } else if (element.value === "" && currentIndex > 1) {
            document.querySelector(`input[maxlength="1"]:nth-child(${currentIndex - 1})`).focus();
        }
        element.value = element.value.replace(/[^0-9]/g, '');
    }
    const verifyForgotPasswordOTPCode = () => {
        $.ajax({
            type: 'POST',
            url: '{{route('portal.auth.verifyForgotPasswordOtp')}}',
            data: {
                _token: '{{csrf_token()}}',
                code: getVerificationCodeValue(),
                email: $("#otpSendForm [name='email']").val()
            },
            dataType: 'json',
            success: function (res) {
                if (res && res.success === true) {
                    otpCode = getVerificationCodeValue();
                    $(".otpEntryArea").hide();
                    $("#resetPasswordForm").fadeIn();
                    $("#resetPasswordForm").find(".alert").html(res?.message ?? "");
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
    const formatTime = (value) => {
        // Zamanı iki haneli olarak formatla
        return value < 10 ? "0" + value : value;
    }
    const startCountdown = (seconds) => {
        var remainingTime = seconds;

        clearInterval(countdown);
        countdown = setInterval(function () {
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;

            // Zamanı güncelle
            $("#resendButton .resendTime").text("(" + formatTime(minutes) + ":" + formatTime(seconds) + ")");

            // Zamanı azalt
            remainingTime--;

            if (remainingTime < 0) {
                // Geri sayım tamamlandığında
                clearInterval(countdown);
                $("#resendButton").prop("disabled", false);
                $("#resendButton .resendTime").text("");
            }
        }, 1000);
    }

    $(document).ready(function () {
        let e = document.querySelector("#kt_sign_in_form");
        let t = document.querySelector("#kt_sign_in_submit");

        r = FormValidation.formValidation(e, {
            fields: {
                email: {
                    validators: {
                        notEmpty: {message: "{{__('the_field_is_required')}}"}
                    }
                },
                password: {
                    validators: {
                        notEmpty: {message: "{{__('the_field_is_required')}}"},
                    }
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger({event: {password: !1}}),
                bootstrap: new FormValidation.plugins.Bootstrap5({
                    rowSelector: ".fv-row",
                    eleInvalidClass: "",
                    eleValidClass: ""
                })
            }
        }), t.addEventListener("click", (function (s) {
            s.preventDefault(), r.revalidateField("password"), r.validate().then((function (r) {
                if (r == 'Valid') {
                    propSubmitButton(t, 1);
                    $.ajax({
                        type: 'POST',
                        url: '{{route('portal.auth.loginPost')}}',
                        data: new FormData(document.querySelector('#kt_sign_in_form')),
                        dataType: 'json',
                        contentType: false,
                        processData: false,
                        cache: false,
                        complete: function (data, status) {
                            propSubmitButton(t, 0);
                            res = data.responseJSON;
                            if (res && res.success === true) {
                                Swal.fire({
                                    title: "{{__('success')}}",
                                    text: res.message,
                                    icon: "success",
                                    showConfirmButton: 0,
                                    allowOutsideClick: false,
                                })
                                setTimeout(() => {
                                    window.location.href = res.redirectUrl;
                                }, 1500)
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
                        },
                    })
                } else {
                    Swal.fire({
                        text: "{{__('form_has_errors')}}",
                        icon: "error",
                        showConfirmButton: 0,
                        showCancelButton: 1,
                        cancelButtonText: "{{__('close')}}"
                    })
                }
            }))
        }))
        $(document).on("click", ".forgotPasswordBtn", function () {
            $("#kt_sign_in_form").hide();
            $(".generalResetPasswordArea").fadeIn();
        })

        $(document).on("click", ".showLogin", function () {
            $(".generalResetPasswordArea").hide();
            $("#kt_sign_in_form").fadeIn();
        })

        $(document).on("submit", "#otpSendForm", function (e) {
            e.preventDefault()
            let form = $(this);
            $.ajax({
                type: 'POST',
                url: "{{route('portal.auth.forgotPassword')}}",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    propSubmitButton(form.find("button[type='submit']"), 1);
                    $(".otpEntryArea").find(".alert").html("");
                },
                complete: function (data, status) {
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        $(".otpSendArea").hide();
                        $(".otpEntryArea").fadeIn();
                        $(".otpEntryArea").find(".alert").html(res?.message ?? "");
                        startCountdown(120);
                    } else {
                        Swal.fire({
                            title: "{{__('error')}}",
                            text: res?.message ? res.message : "{{__('form_has_errors')}}",
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

        $(document).on('input', 'input.otp-code-input.last', function (event) {
            var lastInputValue = event.target.value;
            if (event.inputType !== 'deleteContentBackward' && lastInputValue.trim() !== '') {
                verifyForgotPasswordOTPCode();
            }
        })

        $("#resendButton").on("click", function () {
            $("#otpSendForm").trigger("submit");
            $(this).prop("disabled", true);
            startCountdown(90);
        });

        $(document).on("submit", "#resetPasswordForm", function (e) {
            e.preventDefault()
            let form = $(this),
                formData = new FormData(this);
            formData.append("code", otpCode);
            formData.append("email", $("#otpSendForm [name='email']").val());
            $.ajax({
                type: 'POST',
                url: "{{route('portal.auth.resetPassword')}}",
                data: formData,
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
                            title: res.reload ? "{{__('success')}}" : "",
                            html: res?.message ?? "",
                            icon: "success",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "{{__('close')}}"
                        }).then((r) => window.location.href = res.redirectUrl);
                    } else {
                        Swal.fire({
                            title: "{{__('error')}}",
                            text: res?.message ? res.message : "{{__('form_has_errors')}}",
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
</html>
@include('sweetalert::alert')
