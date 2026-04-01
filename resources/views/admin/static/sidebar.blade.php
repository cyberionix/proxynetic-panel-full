<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{route("admin.dashboard")}}" class="mx-auto">
            <img alt="Logo" src="{{url(brand("logo"))}}" class="w-175px app-sidebar-logo-default p-5"/>
            <img alt="Logo" src="{{url(brand("logo_dark"))}}" class="w-35px app-sidebar-logo-minimize"/>
        </a>
        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                 data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                     data-kt-menu="true" data-kt-menu-expand="false">
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">{{__("overview")}}</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.dashboard") ? "active" : ""}}"
                           href="{{route("admin.dashboard")}}">
												<span class="menu-icon">
													<i class="ki-duotone ki-element-11 fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
												</span>
                            <span class="menu-title">{{__("dashboard")}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">{{__("pages")}}</span>
                        </div>
                        <!--end:Menu content-->
                    </div>

                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{Route::is(["admin.users.index", "admin.userGroups.index"]) ? "here show" : ""}}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
												<span class="menu-icon">
                                                    <i class="fa fa-people-group fs-3"></i>
												</span>
												<span class="menu-title">{{__("customers")}}</span>
												<span class="menu-arrow"></span>
											</span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.users.index") ? "active" : ""}}"
                                   href="{{route("admin.users.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">{{__("customers")}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.userGroups.index") ? "active" : ""}}"
                                   href="{{route("admin.userGroups.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">{{__("customer_groups")}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->

                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{Route::is(["admin.invoices.*", "admin.checkouts.index"]) ? "here show" : ""}}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
												<span class="menu-icon">
                                                    <i class="fa fa-calculator fs-3"></i>
												</span>
												<span class="menu-title">{{__("accounting")}}</span>
												<span class="menu-arrow"></span>
											</span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.invoices.create") ? "active" : ""}}" href="{{route("admin.invoices.create")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">{{__("Yeni Oluştur")}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->

                                <a class="menu-link {{Route::is("admin.invoices.index") ? "active" : ""}}" href="{{route("admin.invoices.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">{{__("sale_invoices")}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.checkouts.index") ? "active" : ""}}" href="{{route("admin.checkouts.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Ödemeler</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->

                            <!--begin:Menu item-->

                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.orders.index") ? "active" : ""}}" href="{{route("admin.orders.index")}}">
												<span class="menu-icon">
													<i class="fa fa-file-invoice fs-3"></i>
												</span>
                            <span class="menu-title">Siparişler</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.supports.index") ? "active" : ""}}" href="{{route("admin.supports.index")}}">
												<span class="menu-icon">
													<i class="fa fa-life-ring fs-3"></i>
												</span>
                            <span class="menu-title d-inline-flex align-items-center">{{__("support_tickets")}}@if(($adminPendingSupportCount ?? 0) > 0)<span class="badge badge-sm badge-danger ms-2">{{ $adminPendingSupportCount }}</span>@endif</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{Route::is(["admin.products.index", "admin.products.categories.index"]) ? "here show" : ""}}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
												<span class="menu-icon">
                                                    <i class="bi bi-stack fs-3"></i>
												</span>
												<span class="menu-title">{{__("products")}}</span>
												<span class="menu-arrow"></span>
											</span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.products.index") ? "active" : ""}}" href="{{route("admin.products.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">{{__("products")}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.products.categories.index") ? "active" : ""}}" href="{{route("admin.products.categories.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">{{__("product_categories")}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.tokenPools.index") ? "active" : ""}}" href="{{route("admin.tokenPools.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Havuz Yönetimi</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{Route::is("admin.localtonetV4.settings") ? "active" : ""}}" href="{{route("admin.localtonetV4.settings")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Localtonetv4</span>
                                </a>
                            </div>
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{Route::is(["admin.products.3proxy.*", "admin.products.3proxyPools.*"]) ? "here show" : ""}}">
                                <!--begin:Menu link-->
                                <span class="menu-link">
												<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
												<span class="menu-title">3Proxy</span>
												<span class="menu-arrow"></span>
											</span>
                                <!--end:Menu link-->
                                <!--begin:Menu sub-->
                                <div class="menu-sub menu-sub-accordion">
                                    <!--begin:Menu item-->
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{Route::is("admin.products.3proxy.servers.index") ? "active" : ""}}" href="{{route("admin.products.3proxy.servers.index")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                            <span class="menu-title">Sunucular</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                    <!--end:Menu item-->
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{Route::is("admin.products.3proxy.createProxy") ? "active" : ""}}" href="{{route("admin.products.3proxy.createProxy")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                            <span class="menu-title">Proxy Oluştur</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                    <!--end:Menu item-->
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{Route::is(["admin.reports.*"]) ? "here show" : ""}}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
												<span class="menu-icon">
                                                    <i class="bi bi-calculator-fill fs-3"></i>
												</span>
												<span class="menu-title">Raporlar</span>
												<span class="menu-arrow"></span>
											</span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{Route::is("admin.reports.financial") ? "active" : ""}}" href="{{route("admin.reports.financial")}}">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Finansal Raporlar</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Yönetim</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.settings") ? "active" : ""}}"
                           href="{{route("admin.settings")}}">
												<span class="menu-icon">
                                                    <i class="fa fa-cogs fs-3"></i>
												</span>
                            <span class="menu-title">{{__("settings")}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.couponCodes.index") ? "active" : ""}}"
                           href="{{route("admin.couponCodes.index")}}">
												<span class="menu-icon">
                                                    <i class="fa fa-gift fs-3"></i>
												</span>
                            <span class="menu-title">Kupon Kodları</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.userSessions.index") ? "active" : ""}}"
                           href="{{route("admin.userSessions.index")}}">
												<span class="menu-icon">
                                                    <i class="fa fa-file-contract fs-3"></i>
												</span>
                            <span class="menu-title">{{__("session_recordings")}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.alerts.index") ? "active" : ""}}"
                           href="{{route("admin.alerts.index")}}">
												<span class="menu-icon">
                                                    <i class="fa fa-exclamation-triangle fs-3"></i>
												</span>
                            <span class="menu-title">{{__("alerts")}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.activityLogs.index") ? "active" : ""}}"
                           href="{{route("admin.activityLogs.index")}}">
												<span class="menu-icon">
                                                    <i class="fa fa-chart-line fs-3"></i>
												</span>
                            <span class="menu-title">Kullanıcı Hareketleri</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item d-none">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.bulkSms.index") ? "active" : ""}}"
                           href="{{route("admin.bulkSms.index")}}">
												<span class="menu-icon">
                                                    <i class="ki-duotone ki-send fs-3">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
												</span>
                            <span class="menu-title">{{__("bulk_sms")}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item d-none">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.bulkEmail.index") ? "active" : ""}}"
                           href="{{route("admin.bulkEmail.index")}}">
												<span class="menu-icon">
                                                    <i class="fas fa-mail-bulk fs-3"></i>
												</span>
                            <span class="menu-title">{{__("bulk_email")}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.smsLogs.index") ? "active" : ""}}" href="{{route("admin.smsLogs.index")}}">
												<span class="menu-icon">
													<i class="fa fa-sms fs-3"></i>
												</span>
                            <span class="menu-title">SMS Kayıtları</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.emailLogs.index") ? "active" : ""}}" href="{{route("admin.emailLogs.index")}}">
												<span class="menu-icon">
													<i class="fa fa-mail-reply-all fs-3"></i>
												</span>
                            <span class="menu-title">E-posta Kayıtları</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--begin:Menu item-->
                    <div class="menu-item  d-none">
                        <!--begin:Menu link-->
                        <a class="menu-link {{Route::is("admin.activityLogs.index") ? "active" : ""}}"
                           href="{{route("admin.activityLogs.index")}}">
												<span class="menu-icon">
                                                    <i class="fa fa-cogs fs-3"></i>
												</span>
                            <span class="menu-title">Sistem Ayarları</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Logs</span>
                        </div>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{Route::is("admin.threeProxyLogs.index") ? "active" : ""}}"
                           href="{{route("admin.threeProxyLogs.index")}}">
                            <span class="menu-icon">
                                <i class="fa fa-server fs-3"></i>
                            </span>
                            <span class="menu-title">3Proxy Logları</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
<!--end::Sidebar-->
