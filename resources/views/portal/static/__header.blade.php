<!--begin::Header-->
<div id="kt_header" style="" class="header align-items-stretch">
    <!--begin::Brand-->
    <div class="header-brand">
        <!--begin::Logo-->
        <a href="#" class="d-flex mx-0 mx-lg-auto p-3">
            <img alt="Logo" src="{{url(brand('logo'))}}" class="w-100"/>
        </a>
        <!--end::Logo-->
        <!--begin::Aside minimize-->
        <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-minimize"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="aside-minimize">
            <i class="ki-duotone ki-entrance-right fs-1 me-n1 minimize-default">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <i class="ki-duotone ki-entrance-left fs-1 minimize-active">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Aside minimize-->
        <!--begin::Aside toggle-->
        <div class="d-flex align-items-center d-lg-none me-n2" title="Show aside menu">
            <div class="btn btn-icon btn-active-color-primary w-30px h-30px" id="kt_aside_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>
        <!--end::Aside toggle-->
    </div>
    <!--end::Brand-->
    <!--begin::Toolbar-->
    <div class="toolbar d-flex align-items-stretch" style="background-color: var(--bg-header)">
        <!--begin::Toolbar container-->
        <div class="container-xxl py-6 py-lg-0 d-flex align-items-center justify-content-between flex-wrap">
            <!--begin::Page title-->
            <div class="page-title d-flex justify-content-center flex-column me-5">
                @yield("breadCrumb")
            </div>
            <!--end::Page title-->
            <!--begin::Action group-->
            <div class="d-flex align-items-stretch overflow-auto pt-3 pt-lg-0">
                <!--begin::Notifications-->
                <div id="header-notifications-area" class="d-flex align-items-center"
                     data-url="" data-token="{{csrf_token()}}">
                    <!--begin::Menu- wrapper-->
                    <div
                        class="position-relative btn btn-icon btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px"
                        data-kt-menu-trigger="click" data-kt-menu-overflow="true" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-notification-on fs-1 icon">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                    </div>
                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px"
                         data-kt-menu="true">
                        <!--begin::Heading-->
                        <div class="d-flex flex-column bgi-no-repeat rounded-top"
                             style="background-color: var(--default-bg-primary)">
                            <!--begin::Title-->
                            <h3 class="text-white fw-bold px-9 mt-10 mb-6">{{__("notifications")}}
                                <span class="fs-8 opacity-75 ps-3">Okunmamış <span class="total">0</span></span>
                            </h3>
                            <!--end::Title-->
                        </div>
                        <!--end::Heading-->
                        <!--begin::Tab content-->
                        <div class="tab-content">
                            <!--begin::Tab panel-->
                            <div class="tab-pane fade show active" id="kt_header_notifications_1"
                                 data-url=""
                                 data-token="{{csrf_token()}}"
                                 role="tabpanel">
                                <!--begin::Items-->
                                <div class="scroll-y mh-325px my-5 list">

                                </div>
                                <!--end::Items-->
                                <!--begin::View more-->
                                <div class="py-3 text-center border-top d-none">
                                    <a href="#"
                                       class="btn btn-color-gray-600 btn-active-color-primary">Tümünü Gör
                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr064.svg-->
                                        <span class="svg-icon svg-icon-5">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<rect opacity="0.5" x="18" y="13" width="13" height="2"
                                                                      rx="1" transform="rotate(-180 18 13)"
                                                                      fill="currentColor"/>
																<path
                                                                    d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z"
                                                                    fill="currentColor"/>
															</svg>
														</span>
                                        <!--end::Svg Icon-->
                                    </a>
                                </div>
                                <!--end::View more-->
                            </div>
                            <!--end::Tab panel-->
                        </div>
                        <!--end::Tab content-->
                    </div>
                    <!--end::Menu wrapper-->
                    <div class="d-none" data-notification-element="item-template">
                        <a href="javascript:void(0);"
                           class="hoverable py-4 px-5 d-flex flex-stack border-top border-bottom border-top-dashed border-bottom-dashed notification-item">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px symbol-circle me-3 p-2">
                                    <i class="fa fa-bullhorn fs-3 text-warning"></i>
                                </div>
                                <div class="mb-0 me-2">
                                    <span class="fs-8 text-gray-800 text-hover-primary fw-bold title"></span>
                                </div>
                            </div>
                            <span class="badge badge-light fs-8 timeAgo"></span>
                        </a>
                    </div>
                    <div class="d-none" data-notification-element="empty-template">
                        <div class="py-4 px-5">
                            <div class="alert alert-primary">Hiç bildirim yok.</div>
                        </div>
                    </div>
                </div>
                <!--end::Notifications-->
                <!--begin::Theme mode-->
                <div class="d-flex align-items-center">
                    <!--begin::Menu toggle-->
                    <a href="#" class="btn btn-sm btn-icon btn-icon-muted btn-active-icon-primary"
                       data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                       data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-night-day theme-light-show fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                            <span class="path6"></span>
                            <span class="path7"></span>
                            <span class="path8"></span>
                            <span class="path9"></span>
                            <span class="path10"></span>
                        </i>
                        <i class="ki-duotone ki-moon theme-dark-show fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>
                    <!--begin::Menu toggle-->
                    <!--begin::Menu-->
                    <div
                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                        data-kt-menu="true" data-kt-element="theme-mode-menu">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-night-day fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
															<span class="path3"></span>
															<span class="path4"></span>
															<span class="path5"></span>
															<span class="path6"></span>
															<span class="path7"></span>
															<span class="path8"></span>
															<span class="path9"></span>
															<span class="path10"></span>
														</i>
													</span>
                                <span class="menu-title">Light</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-moon fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
													</span>
                                <span class="menu-title">Dark</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
													<span class="menu-icon" data-kt-element="icon">
														<i class="ki-duotone ki-screen fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
															<span class="path3"></span>
															<span class="path4"></span>
														</i>
													</span>
                                <span class="menu-title">System</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Theme mode-->
            </div>
            <!--end::Action group-->
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--end::Toolbar-->
</div>
<!--end::Header-->
