<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Header-->
    <div class="app-sidebar-header d-flex flex-column px-10 pt-0" id="kt_app_sidebar_header">
        <!--begin::Logo-->
        <div class="d-flex flex-column gap-5 mb-5">
            <div class="d-flex flex-center">
                <a href="{{route("portal.dashboard")}}" class="w-150px py-10">
                    <img src="{{url(brand('logo'))}}" alt="{{brand("name")}}" class="img-fluid">
                </a>
            </div>
            <div class="d-flex flex-column justify-content-center">
                <!--begin::User info-->
                <div class="d-flex">
                    <div class="symbol symbol-50px me-3">
                        <span class="fs-2 symbol-label">{{ mb_strtoupper(substr(Auth::user()->first_name, 0, 1)) }}</span>

                    </div>
                    <!--begin::Username-->
                    <div class="d-flex flex-column justify-content-center">
                        <div class="text-white fs-4 fw-bold ms-3">{{Auth::user()->full_name}}</div>
                        <div class="text-muted ms-3">{{Auth::user()->email}}</div>
                    </div>
                    <!--end::Username-->
                </div>
                <!--end::User info-->
            </div>
        </div>
        <!--end::Logo-->
    </div>
    <!--end::Header-->
    <!--begin::Navs-->
    <div class="app-sidebar-navs flex-column-fluid mt-5" id="kt_app_sidebar_navs">
        <div id="kt_app_sidebar_navs_wrappers" class="hover-scroll-y my-2" data-kt-scroll="true"
             data-kt-scroll-activate="true" data-kt-scroll-height="auto"
             data-kt-scroll-dependencies="#kt_app_sidebar_header, #kt_app_sidebar_projects"
             data-kt-scroll-wrappers="#kt_app_sidebar_navs" data-kt-scroll-offset="5px">
            <!--begin::Projects-->
            <div class="menu menu-rounded menu-column">
                <!--begin::Menu Item-->
                <div class="menu-item {{Route::is("portal.dashboard") ? "hover" : ""}}">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="{{route("portal.dashboard")}}">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="fa fa-home fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title">Kontrol Paneli</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
                <!-- start::CATEGORIES -->
                <div class="separator"></div>

                <!--begin::Menu Item-->
                @if(!auth()->user()->block_test_products && \App\Models\Product::testProducts()->count() > 0)
                    <div class="menu-item {{Route::is(["portal.products.testProduct"]) ? "hover" : ""}}">
                        <!--begin::Menu link-->
                        <a class="menu-link" href="{{route("portal.products.testProduct")}}">
                            <!--begin::Bullet-->
                            <span class="menu-icon">
                            <i class="text-warning fa fa-star fs-2"></i>
                        </span>
                            <!--end::Bullet-->
                            <!--begin::Title-->
                            <span class="menu-title">Ücretsiz Test Proxy</span>
                            <!--end::Title-->
                        </a>
                        <!--end::Menu link-->
                    </div>
                    @endif
                <!--end::Menu Item-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                    <span
                        class="menu-link main-menu-item">
                        <span class="menu-icon">
                            <i class="ki-outline ki-home-2 fs-2"></i>
                        </span>
                        <span class="menu-title">{{__("buy_new_product")}}</span><span class="menu-arrow"></span></span>
                    <!--end:Menu link--><!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion"><!--begin:Menu item-->
                    @foreach($_allProductCategories as $_productCategory)
                        <!--begin::Menu Item-->
                            <div class="menu-item">
                                <!--begin::Menu link-->
                                <a class="menu-link"
                                   href="{{route("portal.products.index", ["productCategory" => $_productCategory->id])}}">
                                    <!--begin::Bullet-->
                                    <span class="menu-icon">
                                <span class="bullet bullet-dot h-10px w-10px bg-secondary"></span>
                            </span>
                                    <!--end::Bullet-->
                                    <!--begin::Title-->
                                    <span class="menu-title">{{$_productCategory->name}}</span>
                                    <!--end::Title-->
                                </a>
                                <!--end::Menu link-->
                            </div>
                            <!--end::Menu Item-->
                        @endforeach
                    </div><!--end:Menu sub--></div>
                <!-- end::CATEGORIES -->
                <!--begin::Menu Item-->
                <div class="menu-item {{Route::is(["portal.orders.index"]) ? "hover" : ""}}">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="{{route("portal.orders.index")}}">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="bi bi-stack fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title">{{__("my_products_and_services")}}</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
                <!--begin::Menu Item-->
                <div class="menu-item {{Route::is(["portal.invoices.index","portal.invoices.show"]) ? "hover" : ""}}">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="{{route("portal.invoices.index")}}">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="fa fa-file-invoice fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title">Faturalarım</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
                <!--begin::Menu Item-->
                <div class="menu-item {{Route::is(["portal.supports.index"]) ? "hover" : ""}}">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="{{route("portal.supports.index")}}">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="fa fa-envelope-open-text fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title d-inline-flex align-items-center">{{__("my_support_tickets")}}@if(($portalActiveSupportCount ?? 0) > 0)<span class="badge badge-sm badge-danger ms-2 min-w-25px">{{ $portalActiveSupportCount }}</span>@endif</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
                <!--begin::Menu Item-->
                <div class="menu-item">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="javascript:void(0)" data-np-btn="create-support">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="fa fa-file-pen fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title">{{__("create_:name", ["name" => __("support_ticket")])}}</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
                <div class="separator"></div>
                <!--begin::Heading-->
                <div class="menu-item">
                    <div class="menu-content menu-heading text-uppercase fs-7">Hesap Bilgileri</div>
                </div>
                <!--end::Heading-->
                <!--begin::Menu Item-->
                <div class="menu-item {{Route::is(["portal.users.profile"]) ? "hover" : ""}}">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="{{route("portal.users.profile")}}">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="fa fa-users-cog fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title">{{__("Profil Bilgileri")}}</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
                <!--begin::Menu Item-->
                <div class="menu-item">
                    <!--begin::Menu link-->
                    <a class="menu-link" href="{{route("portal.auth.logout")}}">
                        <!--begin::Bullet-->
                        <span class="menu-icon">
                            <i class="fa fa-sign-out fs-2"></i>
                        </span>
                        <!--end::Bullet-->
                        <!--begin::Title-->
                        <span class="menu-title">{{__("log_out")}}</span>
                        <!--end::Title-->
                    </a>
                    <!--end::Menu link-->
                </div>
                <!--end::Menu Item-->
            </div>
            <!--end::Projects-->
        </div>
    </div>
    <!--end::Navs-->
</div>
<!--end::Sidebar-->
