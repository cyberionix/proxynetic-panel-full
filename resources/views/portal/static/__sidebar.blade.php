<!--begin::Aside-->
<div id="kt_aside" class="aside" data-kt-drawer="true" data-kt-drawer-name="aside"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
     data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_aside_mobile_toggle">
    <!--begin::Aside Toolbarl-->
    <div class="aside-toolbar flex-column-auto" id="kt_aside_toolbar">
        <!--begin::Aside user-->
        <!--begin::User-->
        <div class="aside-user d-flex align-items-sm-center justify-content-center py-5">
            <!--begin::Symbol-->
            <div class="symbol symbol-50px">
                <div class="symbol-label fs-3  bg-white text-black text-uppercase">
                    {{ mb_substr(auth()->user()->first_name, 0, 1) }}
                </div>
            </div>
            <!--end::Symbol-->
            <!--begin::Wrapper-->
            <div class="aside-user-info flex-row-fluid flex-wrap ms-5">
                <!--begin::Section-->
                <div class="d-flex">
                    <!--begin::Info-->
                    <div class="flex-grow-1 me-2">
                        <!--begin::Username-->
                        <a href="#"
                           class="text-white text-hover-primary fs-6 fw-bold">{{auth()->user()->first_name}} {{ auth()->user()->last_name}}</a>
                        <!--end::Username-->
                        <!--begin::Description-->
                        <span class="text-gray-600 fw-semibold d-block fs-8 mb-1"></span>
                        <!--end::Description-->
                        <!--begin::Label-->
                        <div class="d-flex align-items-center text-success fs-9">
                            <span class="bullet bullet-dot bg-success me-1"></span>{{__("online")}}</div>
                        <!--end::Label-->
                    </div>
                    <!--end::Info-->
                    <!--begin::User menu-->
                    <div class="me-n2">
                        <!--begin::Action-->
                        <a href="#" class="btn btn-icon btn-sm btn-active-color-primary mt-n2"
                           data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start"
                           data-kt-menu-overflow="true">
                            <i class="ki-duotone ki-setting-2 text-white fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </a>
                        <!--begin::User account menu-->
                        <div
                            class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                            data-kt-menu="true">
                            <!--begin::Menu item-->
                            <div class="menu-item px-5">
                                <a href="{{route("portal.users.profile")}}" class="menu-link px-5">{{__("profile_information")}}</a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-5">
                                <a href="{{route('portal.auth.logout')}}" class="menu-link px-5">{{__('log_out')}}</a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::User account menu-->
                        <!--end::Action-->
                    </div>
                    <!--end::User menu-->
                </div>
                <!--end::Section-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::User-->
        <!--end::Aside user-->
    </div>
    <!--end::Aside Toolbarl-->
    <!--begin::Aside menu-->
    <div class="aside-menu flex-column-fluid">
        <!--begin::Aside Menu-->
        <div class="hover-scroll-overlay-y mx-3 my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
             data-kt-scroll-height="auto"
             data-kt-scroll-dependencies="{default: '#kt_aside_toolbar, #kt_aside_footer', lg: '#kt_header, #kt_aside_toolbar, #kt_aside_footer'}"
             data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="5px">
            <!--begin::Menu-->
            <div
                class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
                id="#kt_aside_menu" data-kt-menu="true">
                <!--begin:Menu item-->
                <div class="menu-item pt-5">
                    <!--begin:Menu content-->
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase text-white fs-7">{{__("overview")}}</span>
                    </div>
                    <!--end:Menu content-->
                </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{Route::is("portal.dashboard") ? "active" : ""}}"
                       href="{{route("portal.dashboard")}}">
										<span class="menu-icon">
											<i class="ki-duotone text-white ki-element-11 fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
										</span>
                        <span class="menu-title text-white">{{__("dashboard")}}</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{Route::is(["portal.invoices.index","portal.invoices.show"]) ? "active" : ""}}"
                       href="{{route("portal.invoices.index")}}">
										<span class="menu-icon">
                                            <i class="fa fa-file-invoice text-white fs-2"></i>
										</span>
                        <span class="menu-title text-white">Faturalarım</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{Route::is(["portal.supports.index"]) ? "active" : ""}}"
                       href="{{route("portal.supports.index")}}">
										<span class="menu-icon">
                                            <i class="fa fa-envelope-open-text text-white fs-2"></i>
										</span>
                        <span class="menu-title text-white d-inline-flex align-items-center">{{__("support")}}@if(($portalActiveSupportCount ?? 0) > 0)<span class="badge badge-sm badge-danger ms-2 min-w-25px">{{ $portalActiveSupportCount }}</span>@endif</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu content-->
                    <div class="menu-content">
                        <span class="menu-heading text-white fw-bold text-uppercase fs-7">Hesap Bilgileri</span>
                    </div>
                    <!--end:Menu content-->
                </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{Route::is("portal.users.profile") ? "active" : ""}}"
                       href="{{route("portal.users.profile")}}">
										<span class="menu-icon">
											<i class="fa fa-users-cog text-white fs-3"></i>
										</span>
                        <span class="menu-title text-white">Profil Bilgileri</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
                <!--begin:Menu item-->
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link {{Route::is("portal.auth.logout") ? "active" : ""}}"
                       href="{{route("portal.auth.logout")}}">
										<span class="menu-icon">
											<i class="fa fa-sign-out text-white fs-3"></i>
										</span>
                        <span class="menu-title text-white">Çıkış Yap</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--end:Menu item-->
            </div>
            <!--end::Menu-->
        </div>
        <!--end::Aside Menu-->
    </div>
    <!--end::Aside menu-->
</div>
<!--end::Aside-->
