<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<head>
    <title>@yield("title") | {{brand("name")}}</title>
    <meta charset="utf-8" />
    <meta name="description" content="@yield("description")" />
    <meta name="keywords" content="@yield("keywords")" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
    <link rel="shortcut icon" href="{{url(config('brand.favicon'))}}" />
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{assetAdmin("")}}/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{assetAdmin("")}}/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="{{assetAdmin("")}}/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    <link href="{{assetAdmin("")}}/css/custom.css" rel="stylesheet" type="text/css"/>

    @yield("css")
    @stack("css")
    <script>// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
<!--begin::Theme mode setup on page load-->
<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
<!--end::Theme mode setup on page load-->
<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
       @include("admin.static.header")
        <!--begin::Wrapper-->
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            @include("admin.static.sidebar")
            <!--begin::Main-->
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <!--begin::Content wrapper-->
                <div class="d-flex flex-column flex-column-fluid">
                    @yield("breadcrumb")
                    <!--begin::Content-->
                    <div id="kt_app_content" class="app-content flex-column-fluid">
                        <!--begin::Content container-->
                        <div id="kt_app_content_container" class="app-container container-xxl">
                            @yield("master")
                        </div>
                        <!--end::Content container-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Content wrapper-->
                @include("admin.static.footer")
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
<!--begin::Javascript-->
<script>var hostUrl = "{{url('')}}/netAdmin/";</script>
<!--begin::Global Javascript Bundle(mandatory for all pages)-->
<script src="{{assetAdmin("")}}/plugins/global/plugins.bundle.js"></script>
<script src="{{assetAdmin("")}}/js/scripts.bundle.js"></script>
<!--end::Global Javascript Bundle-->
<!--begin::Vendors Javascript(used for this page only)-->
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
<script src="{{assetAdmin("")}}/plugins/custom/datatables/datatables.bundle.js"></script>
<!--end::Vendors Javascript-->
<!--begin::Custom Javascript(used for this page only)-->
<script src="{{assetAdmin("")}}/js/widgets.bundle.js"></script>
<script src="{{assetAdmin("")}}/js/custom/widgets.js"></script>
<script src="{{assetAdmin("")}}/js/custom/apps/chat/chat.js"></script>
<script src="{{assetAdmin("")}}/js/custom/utilities/modals/upgrade-plan.js"></script>
<script src="{{assetAdmin("")}}/js/custom/utilities/modals/create-app.js"></script>
<script src="{{assetAdmin("")}}/js/custom/utilities/modals/new-target.js"></script>
<script src="{{assetAdmin("")}}/js/custom/utilities/modals/users-search.js"></script>

<script>
    const defaultDateFormat = () => {
        return "DD/MM/YYYY";
    };
    const defaultDateTimeFormat = () => {
        return "DD/MM/YYYY HH:mm:ss";
    };

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

    $(document).on("change", ".headerUserSelect", function (){
        let id = $(this).val();
        let url = `{{ route('admin.auth.userAccountLogin', ['user' => '__user_placeholder__']) }}`;
        url = url.replace('__user_placeholder__', id);

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: "{{csrf_token()}}"
            },
            beforeSend: function () {
                //
            },
            complete: function (data, status) {
                let res = data.responseJSON;
                if (res?.success === true) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: res?.message ?? "",
                        confirmButtonText: 'Tamam'
                    });
                    setTimeout(() => {
                        window.open(res.redirectUrl, '_blank');
                    }, 800)
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: res?.message ?? "",
                        confirmButtonText: 'Tamam'
                    })
                }
            }
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

</script>

<script src="{{assetAdmin('')}}/js/custom.js"></script>

<script>
(function(){
    var storageKey = 'admin_last_ticket_id';
    var suppressNext = true;
    var polling = false;

    function readLastId() {
        return parseInt(localStorage.getItem(storageKey)) || 0;
    }

    function playTicketSound() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var t = ctx.currentTime;
            [[830, 0, 0.12], [1050, 0.13, 0.12], [1320, 0.26, 0.18]].forEach(function(n) {
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = n[0];
                gain.gain.setValueAtTime(0.18, t + n[1]);
                gain.gain.exponentialRampToValueAtTime(0.001, t + n[1] + n[2]);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(t + n[1]);
                osc.stop(t + n[1] + n[2] + 0.05);
            });
        } catch(e) {}
    }

    function pollNewTickets() {
        if (polling || document.hidden) return;
        polling = true;

        var lastTicketId = readLastId();

        $.ajax({
            url: "{{ route('admin.supports.newTicketsPoll') }}",
            type: 'GET',
            data: { last_id: lastTicketId },
            dataType: 'json',
            success: function(res) {
                var freshId = readLastId();
                if (res.max_id && res.max_id > freshId) {
                    if (!suppressNext && res.tickets && res.tickets.length > 0) {
                        res.tickets.forEach(function(ticket) {
                            toastr.options = {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toastr-top-right',
                                timeOut: 5000,
                                extendedTimeOut: 3000,
                                onclick: function() {
                                    window.location.href = ticket.url;
                                }
                            };
                            toastr.info(
                                '<div style="cursor:pointer"><strong>' + ticket.user + '</strong><br>' + ticket.subject + '</div>',
                                '<i class="fa fa-headset me-2"></i>Yeni Destek Talebi #' + ticket.id
                            );
                        });
                        playTicketSound();
                    }
                    localStorage.setItem(storageKey, res.max_id);
                }
                suppressNext = false;
            },
            complete: function() {
                polling = false;
            }
        });
    }

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            suppressNext = true;
        }
    });

    $(document).ready(function() {
        pollNewTickets();
        setInterval(pollNewTickets, 15000);
    });
})();
</script>

<!--end::Custom Javascript-->
@yield("js")
@stack("js")
<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
