<!--begin::Header-->
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: false, lg: true}" data-kt-sticky-name="app-header-sticky" data-kt-sticky-offset="{default: false, lg: '300px'}">
    <!--begin::Header container-->
    <div class="app-container container-xxl d-flex flex-stack" id="kt_app_header_container">
        <!--begin::Sidebar toggle-->
        <div class="d-flex align-items-center d-block d-lg-none ms-n3" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px me-2" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
            <!--begin::Logo image-->
            <a href="" class="d-none">
                <img alt="Logo" src="{{url(brand('logo_dark'))}}" class="h-25px theme-light-show" />
                <img alt="Logo" src="{{url(brand('logo_dark'))}}" class="h-25px theme-dark-show" />
            </a>
            <!--end::Logo image-->
        </div>
        <!--end::Sidebar toggle-->
        <!--begin::Header wrapper-->
        <div class="d-flex flex-stack flex-lg-row-fluid" id="kt_app_header_wrapper">
            <!--begin::Page title-->
            <div class="page-title gap-4 me-3 mb-5 mb-lg-0" data-kt-swapper="true" data-kt-swapper-mode="{default: 'prepend', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_content_container', lg: '#kt_app_header_wrapper'}">
                <div class="d-flex align-items-center">
                    <!--begin::Breadcrumb-->
                    @yield("breadcrumb")
                    <!--end::Breadcrumb-->
                </div>
            </div>
            <!--end::Page title-->
            <!--begin::Action-->
            <div class="row gap-3">
                <!--begin::Notifications-->
                <div id="header-notifications-area" class="col d-flex align-items-center"
                     data-url="{{route("portal.users.notifications.list")}}" data-token="{{csrf_token()}}">
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
                             style="background-color: #0038a1">
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
                            <div class="tab-pane fade show active" id="kt_header_notifications_1" role="tabpanel">
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
                        <div
                           class="hoverable py-4 px-5 d-flex flex-stack border-top border-bottom border-top-dashed border-bottom-dashed notification-item">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px symbol-circle p-2">
                                    <i class="fa fa-bullhorn fs-6 text-warning"></i>
                                </div>
                                <div class="mb-0 mx-2">
                                    <span class="fs-8 text-gray-800 fw-bold title"></span>
                                </div>
                            </div>
                            <span class="badge badge-light fs-8 timeAgo"></span>
                        </div>
                    </div>
                    <div class="d-none" data-notification-element="empty-template">
                        <div class="py-4 px-5">
                            <div class="alert alert-primary">Hiç bildirim yok.</div>
                        </div>
                    </div>
                </div>
                <!--end::Notifications-->
                <!--begin::Balance-->
                <a href="{{route("portal.balance.index")}}"
                   class="col position-relative d-flex flex-center gap-2 bg-hover-light-primary w-100px h-30px h-md-40px rounded-1">
                    <i class="fa fa-wallet fs-3"></i>
                    <span class="text-muted fw-semibold" data-np-balance="amount">{{showBalance(auth()->user()->balance, true)}}</span>
                </a>
                <!--end::Balance-->
                <!--begin::Basket-->
                <a href="{{route("portal.basket.index")}}"
                   class="col position-relative d-flex flex-center gap-2 bg-hover-light-primary w-100px h-30px h-md-40px rounded-1">
                    <i class="fa fa-shopping-cart fs-3"></i>
                    <span class="text-muted fw-semibold">{{__("my_basket")}}</span>

                    <span class="position-absolute top-0 translate-middle  badge badge-sm badge-circle badge-success"
                          data-np-basket-summary="count"
                          style="right: -10px; height: 19px;">{{@Auth::user()->basket?->itemsCount() ?? 0}}</span>
                </a>
                <!--end::Basket-->
                <!--begin::Theme mode-->
                <div class="col d-flex align-items-center">
                    <!--begin::Menu toggle-->
                    <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-icon-muted btn-active-icon-primary"
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
            <!--end::Action-->
        </div>
        <!--end::Header wrapper-->
    </div>
    <!--end::Header container-->
</div>
<!--end::Header-->
