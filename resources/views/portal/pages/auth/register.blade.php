<!DOCTYPE html>
<html lang="tr" data-bs-theme="light"><!--begin::Head-->
<head>
    <base href="">
    <title>Kayıt Ol | {{config('brand.name')}}</title>
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
            <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
                <!--begin::Wrapper-->
                <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">
                    <!--begin::Form-->
                    <form id="registerForm">
                    @csrf
                    <!--begin::Heading-->
                        <div class="text-center mb-11">
                            <!--begin::Title-->
                            <h1 class="text-gray-900 fw-bolder mb-3">{{__('register')}}</h1>
                            <!--end::Title-->
                            <!--begin::Subtitle-->
                            <div class="text-gray-500 fw-semibold fs-6">Bilgilerinizi doldurarak dakikalar içinde hesabınızı oluşturun.</div>
                            <!--end::Subtitle=-->
                        </div>
                        <!--begin::Heading-->
                        <!--begin::Login options-->
                        <div class="row g-3 mb-9 d-none">
                            <!--begin::Col-->
                            <div class="col-md-6 mx-auto">
                                <!--begin::Google link=-->
                                <a href="#"
                                   class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                                    <img alt="Logo" src="{{assetPortal('')}}/media/svg/brand-logos/google-icon.svg"
                                         class="h-15px me-3">{{__('register_with_google')}}</a>
                                <!--end::Google link=-->
                            </div>
                            <!--end::Col-->
                            <!--end::Col-->
                        </div>
                        <!--end::Login options-->
                        <!--begin::Separator-->
                        <div class="separator separator-content my-14 d-none">
                            <span
                                class="w-275px text-gray-500 fw-semibold fs-7">{{__('or').' '.__('register_with_email')}}</span>
                        </div>
                        <!--end::Separator-->
                        <div class="row mb-6">
                            <div class="col fv-row fv-plugins-icon-container">
                                <!--begin::Email-->
                                <input type="text" placeholder="{{__('firstname')}}" name="firstName" autocomplete="off"
                                       class="form-control bg-transparent">
                                <!--end::Email-->
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                            <div class="col fv-row fv-plugins-icon-container">
                                <!--begin::Email-->
                                <input type="text" placeholder="{{__('lastname')}}" name="lastName" autocomplete="off"
                                       class="form-control bg-transparent">
                                <!--end::Email-->
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="fv-row mb-6 fv-plugins-icon-container">
                            <!--begin::Email-->
                            <input type="text" placeholder="{{__('email')}}" name="email" autocomplete="off"
                                   class="form-control bg-transparent">
                            <!--end::Email-->
                            <div class="text-muted">{{__('requires_verification')}}</div>
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                        </div>
                        <div class="row">
                            <!--begin::Input group-->
                            <div class="col-lg fv-row mb-6 fv-plugins-icon-container" data-kt-password-meter="true">
                                <!--begin::Wrapper-->

                                <!--begin::Input wrapper-->

                                <input class="form-control bg-transparent" type="password"
                                       placeholder="{{__('password')}}" name="password" autocomplete="off">

                                <!--end::Input wrapper-->

                                <!--end::Wrapper-->
                                <!--begin::Hint-->
                                <div class="text-muted">{{__('password_rules_description')}}</div>
                                <!--end::Hint-->
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                            <!--end::Input group=-->
                            <!--end::Input group=-->
                            <div class="col-lg fv-row mb-8 fv-plugins-icon-container">
                                <!--begin::Repeat Password-->
                                <input placeholder="{{__('repeat_password')}}" name="confirm-password" type="password"
                                       autocomplete="off" class="form-control bg-transparent">
                                <!--end::Repeat Password-->
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                            <!--end::Input group=-->
                        </div>
                        <div class="fv-row mb-8 fv-plugins-icon-container">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="toc" value="1">
                                <span
                                    class="form-check-label fw-semibold text-gray-700 fs-base ms-1">{!! __('accept_gdpr',['url' => route('web.gdpr')]) !!}</span>
                            </label>
                            <div
                                class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                        </div>
                        <div class="d-grid mb-10">
                            <button type="submit" id="kt_sign_up_submit" class="btn btn-primary">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{__('register')}}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__('please_wait')}}
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <div class="text-gray-500 text-center fw-semibold fs-6">{{__('already_have_account')}}
                            <a href="{{route('portal.auth.login')}}"
                               class="link-primary fw-semibold">{{__('login')}}</a></div>
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Wrapper-->
                <!--begin::Footer-->
                <div class="text-end px-lg-10">
                    <!--begin::Links-->
                        <a href="{{route('web.gdpr')}}" target="_blank">{{__('gdpr')}}</a>
                    <!--end::Links-->
                </div>
                <!--end::Footer-->
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
<script>var hostUrl = "{{assetPortal('')}}/";</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{assetPortal('')}}/plugins/global/plugins.bundle.js"></script>
<script src="{{assetPortal('')}}/js/scripts.bundle.js"></script>
<script src="{{assetPortal('')}}/js/custom.js"></script>
<!--end::Global Javascript Bundle-->
<!--begin::Custom Javascript(used for this page only)-->
{{--<script src="{{assetPortal('')}}/js/custom/authentication/sign-up/general.js"></script>--}}
<!--end::Custom Javascript-->
<!--end::Javascript-->

</body>
<!--end::Body-->
<script>
    $(document).ready(function (){
        $(document).on("submit", "#registerForm", function (e){
            e.preventDefault()
            let form = $(this);
            $.ajax({
                type: 'POST',
                url: '{{route('portal.auth.registerPost')}}',
                data: new FormData(document.querySelector('#registerForm')),
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function (){
                    propSubmitButton($("#kt_sign_up_submit"), 1);
                },
                complete: function (data, status) {
                    propSubmitButton($("#kt_sign_up_submit"), 0);
                    res = data.responseJSON;
                    if (res && res.success === true) {
                        $('#registerForm').trigger('reset');
                        Swal.fire({
                            title: "{{__('success')}}",
                            text: "{{__('your_account_has_been_successfully_created').' '.__('redirecting')}}",
                            icon: "success",
                            showConfirmButton: 1,
                            confirmButtonText: "{{__('dashboard')}}",
                        }).then(r => window.location.href = res.redirectUrl)

                        setTimeout(function (){
                            window.location.href = res.redirectUrl
                        },3500)
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
        })
    })
</script>
</html>
