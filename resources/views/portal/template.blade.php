<!DOCTYPE html>
<!--
Author: Keenthemes
Product Name: Metronic
Product Version: 8.1.8
Purchase: https://1.envato.market/EA4JP
Website: http://www.keenthemes.com
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html lang="en">
<!--begin::Head-->
<head>
    <base href=""/>
    <title>@yield("title") | Proxynetic Müşteri Panel</title>
    <meta charset="utf-8"/>
    <meta name="description"
          content="Nevada kahvesi, dünyanın en değerli kahvelerinden biridir. Bu eşsiz kahveyi Kolombiya'da sadece 450 aile üretmektedir. Bu ailelere erişmek ve satış izni almak son derece zordur. Kolombiya Sierra Nevada kahvesi, uluslararası organik tarım standartları altında çeşitli akreditasyonlar almıştır."/>
    <meta name="keywords"
          content="Nevada, coffee, kahve, kahve çekirdeği"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta property="og:locale" content="en_US"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title"
          content="Nevada Coffe"/>
    <meta property="og:url" content="{{route("portal.dashboard")}}"/>
    <meta property="og:site_name" content="Nevada Coffee"/>
    <link rel="shortcut icon" href="{{url(config('brand.favicon'))}}"/>
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>
    <!--end::Fonts-->
    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{assetPortal("")}}/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet"
          type="text/css"/>
    <link href="{{assetPortal("")}}/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
    <!--end::Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{assetPortal("")}}/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css"/>
    <link href="{{assetPortal("")}}/css/style.bundle.css" rel="stylesheet" type="text/css"/>
    <style>
        .bg-primary.hoverable:hover {
            background-color: #187ff8 !important
        }
    </style>
    @yield("css")
    @stack('css')

    <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_app_body" data-kt-app-header-fixed-mobile="true" data-kt-app-toolbar-enabled="true"
      data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-header="true"
      data-kt-app-sidebar-push-toolbar="true" class="app-default">
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
<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        @include("portal.static.header")
        <!--begin::Wrapper-->
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            @include("portal.static.sidebar")
            <!--begin::Main-->
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <!--begin::Content wrapper-->
                <div class="d-flex flex-column flex-column-fluid">
                    <!--begin::Content-->
                    <div id="kt_app_content" class="app-content flex-column-fluid ">
                        <!--begin::Content container-->
                        <div id="kt_app_content_container" class="app-container container-xxl">
                            @if(session('payment_result_message'))
                                <div class="alert alert-primary d-flex flex-column flex-sm-row p-5">
                                    <div class="d-flex align-items-center">
                                        <!--begin::Icon-->
                                        <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span
                                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <!--end::Icon-->
                                    </div>
                                    <!--begin::Wrapper-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Title-->
                                        <h4 class="mb-0 text-primary">{{session('payment_result_message')}}</h4>
                                        <!--end::Title-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                            @endif
                            @yield("master")
                        </div>
                        <!--end::Content container-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Content wrapper-->
                @include("portal.static.footer")
            </div>
            <!--end:::Main-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>
<!--end::App-->
<!--begin::Scrolltop-->
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <i class="ki-duotone ki-arrow-up">
        <span class="path1"></span>
        <span class="path2"></span>
    </i>
</div>
<!--end::Scrolltop-->
<!--begin::Modals-->
<div class="modal fade" id="headerNotificationModal" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="title">Mert</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
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
                <div class="message"></div>
                <!--begin::Actions-->
                <div class="d-flex flex-center flex-row-fluid pt-12">
                    <button type="reset" class="btn btn-light me-3"
                            data-bs-dismiss="modal">{{__("cancel")}}</button>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<x-portal.modals.create-support-modal/>
<!--end::Modals-->
<!--begin::Javascript-->
<script>
    var hostUrl = "{{assetPortal("")}}/";
</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{assetPortal("")}}/plugins/global/plugins.bundle.js"></script>
<script src="{{assetPortal("")}}/js/scripts.bundle.js"></script>
<!--end::Global Javascript Bundle-->
<!--begin::Vendors Javascript(used for this page only)-->
<script src="{{assetPortal("")}}/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<script src="https://cdn.amcharts.com/lib/5/map.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/continentsLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/usaLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>
<script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js"></script>
<script src="{{assetPortal("")}}/plugins/custom/datatables/datatables.bundle.js"></script>
<!--end::Vendors Javascript-->
<!--begin::Custom Javascript(used for this page only)-->
<script src="{{assetPortal("")}}/js/widgets.bundle.js"></script>
<script src="{{assetPortal("")}}/js/custom/widgets.js"></script>
<script src="{{assetPortal("")}}/js/custom/apps/chat/chat.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/create-campaign.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/upgrade-plan.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/offer-a-deal/type.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/offer-a-deal/details.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/offer-a-deal/finance.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/offer-a-deal/complete.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/offer-a-deal/main.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/create-app.js"></script>
<script src="{{assetPortal("")}}/js/custom/utilities/modals/users-search.js"></script>
<script>
    $(document).ready(function (){
        $('.main-menu-item').on('click',function(){
            let elm = $(this).closest('.menu-accordion');
            let sub = $(elm).find('.menu-sub-accordion');
            if($(elm).hasClass('show')){
                $(elm).find('.menu-sub-accordion').hide(400);
                setTimeout(function(){
                    $(elm).removeClass('show');
                    $(elm).removeClass('here');
                },400);
            }else{
                $(elm).find('.menu-sub-accordion').show(400);
                $(elm).addClass('show');
                $(elm).addClass('here');
            }
        })

        $(document).on("click", ".copy-text", function (){
            // data-text özelliğine sahip metni al
            var textToCopy = $(this).data('text');

            // Kopyalama işlemi için geçici bir textarea oluştur
            var $tempTextarea = $('<textarea>');
            $('body').append($tempTextarea);
            $tempTextarea.val(textToCopy).select();
            document.execCommand('copy');
            $tempTextarea.remove();

            // Kopyalama başarılıysa kullanıcıyı bilgilendir
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toastr-top-center",
                "preventDuplicates": true,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            toastr.success("Metin başarıyla kopyalandı.");
        })
    })

    var alerts = {
        success: Swal.mixin({
            icon: 'success',
            title: "{{__('success')}}",
            buttonsStyling: false,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Kapat',
            customClass: {
                cancelButton: 'btn btn-secondary'
            }
        }),
        error: Swal.mixin({
            icon: 'error',
            title: "{{__('error')}}",
            cancelButtonText: 'Kapat',
            buttonsStyling: false,
            showConfirmButton: false,
            showCancelButton: true,
            customClass: {
                cancelButton: 'btn btn-secondary'
            }
        }),
        confirm: Swal.mixin({
            icon: 'warning',
            title: 'Uyarı',
            confirmButtonText: 'Devam et',
            buttonsStyling: false,
            text: 'Devam etmek istediğinize emin misiniz?',
            showCancelButton: true,
            cancelButtonText: 'Kapat',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-secondary'
            }
        }),
        wait: Swal.mixin({
            icon: 'warning',
            title: '{{__("please_wait")}}',
            html: '{{__("transaction_continues")}}',
            showCancelButton: false,
            allowOutsideClick: 0,
            didOpen: () => {
                Swal.showLoading()
            }
        })
    }
    const defaultDateFormat = () => {
        return "{{defaultDateFormat()}}";
    };
    const defaultDateTimeFormat = () => {
        return "{{defaultDateTimeFormat()}}";
    };
</script>
<script src="{{assetPortal("")}}/js/custom.js"></script>
<script>
    const itiOptions = (hiddenInput, extraParams = {}) => {
        let options = {
            utilsScript: "{{asset("js/plugins/intl-tel-input/intlTelInput-utils.js")}}",
            preferredCountries: ["tr"],
            separateDialCode: true,
            hiddenInput: hiddenInput,
            nationalMode: true,
            allowDropdown: true,
            formatOnDisplay: false,
        };

        Object.keys(extraParams).forEach(key => {
            options[key] = extraParams[key];
        });

        return options;
    }
</script>
@yield("js")
@stack('js')
<!--end::Custom Javascript-->
<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
