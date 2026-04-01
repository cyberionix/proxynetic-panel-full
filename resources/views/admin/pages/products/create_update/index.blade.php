@extends("admin.template")
@section("title", $pageTitle)
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="[__('products') => route('admin.products.index'), $pageTitle]"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Form-->
                <form id="primaryForm"
                      enctype="multipart/form-data"
                      action="{{isset($product) ? route("admin.products.update", ["product" => $product->id]) : route("admin.products.store")}}"
                      class="form d-flex flex-column flex-lg-row">
                    <!--begin::Aside column-->
                    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                        <!--begin::Status-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>{{__("status")}}</h2>
                                </div>
                                <!--end::Card title-->
                                <!--begin::Card toolbar-->
                                <div class="card-toolbar">
                                    <div
                                        class="rounded-circle bg-{{isset($product) && !$product->is_active ? "danger" : "success"}} w-15px h-15px"
                                        id="np_add_product_status"></div>
                                </div>
                                <!--begin::Card toolbar-->
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Select2-->
                                <x-admin.form-elements.select name="product[status]"
                                                              hideSearch="true"
                                                              :selectedOption="@$product->is_active ?? 1"
                                                              :options="[
                                                                ['label' => __('active'), 'value' => 1],
                                                                ['label' => __('passive'), 'value' => 0]
                                                                ]"/>
                                <!--end::Select2-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Status-->

                        <!--begin::Category & tags-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>{{__("product_details")}}</h2>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <div class="mb-5">
                                    <!--begin::Label-->
                                    <label
                                        class="form-label">{{__(":name_selection", ["name" => __("category")])}}</label>
                                    <!--end::Label-->
                                    <!--begin::Select2-->
                                    <x-admin.form-elements.product-category-select name="product[category_id]"
                                                                                   :selectedOption="@$product->category->id ?? ''"
                                                                                   :allowClear="true"/>
                                    <!--end::Select2-->
                                </div>
                                <div>
                                    <!--begin::Label-->
                                    <label class="form-label">ISP</label>
                                    <!--end::Label-->
                                    <!--begin::Image input-->
                                    <div class="text-center">
                                        <div
                                            class="image-input image-input-outline mb-3
                                             {{isset($product) && $product->isp_image ? "" : "image-input-empty image-input-placeholder"}}"
                                            style="background-image: url('{{assetAdmin("media/svg/avatars/blank.svg")}}')"
                                            data-kt-image-input="true">
                                            <!--begin::Preview existing avatar-->
                                            <div class="image-input-wrapper w-150px h-150px"
                                                 style="{{isset($product) && $product->isp_image ? "background-image: url('" . asset($product->isp_image) . "')" : ""}}"
                                            ></div>
                                            <!--end::Preview existing avatar-->
                                            <!--begin::Label-->
                                            <label
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                title="{{__("change_image")}}">
                                                <i class="ki-duotone ki-pencil fs-7">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <!--begin::Inputs-->
                                                <input type="file" name="isp_image" accept=".png, .jpg, .jpeg"/>
                                                <input type="hidden" name="isp_image_remove"/>
                                                <!--end::Inputs-->
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Cancel-->
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                title="Kaldır">
															<i class="ki-duotone ki-cross fs-2">
																<span class="path1"></span>
																<span class="path2"></span>
															</i>
														</span>
                                            <!--end::Cancel-->
                                            <!--begin::Remove-->
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                title="Kaldır">
															<i class="ki-duotone ki-cross fs-2">
																<span class="path1"></span>
																<span class="path2"></span>
															</i>
														</span>
                                            <!--end::Remove-->
                                        </div>
                                    </div>
                                    <!--end::Image input-->
                                </div>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Category & tags-->
                    </div>
                    <!--end::Aside column-->
                    <!--begin::Main column-->
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <!--begin:::Tabs-->
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                                   href="#np_add_product_general">{{__("general_information")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 " data-bs-toggle="tab"
                                   href="#delivery_tab">Teslimat Bilgileri</a>
                            </li>
                            <!--end:::Tab item-->
                        </ul>
                        <!--end:::Tabs-->
                        <!--begin::Tab content-->
                        <div class="tab-content">
                            <!--begin::Tab pane-->
                            <div class="tab-pane fade show active" id="np_add_product_general">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <!--begin::General options-->
                                    <div class="card card-flush py-4">
                                        <!--begin::Card header-->
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>{{__("general_information")}}</h2>
                                            </div>
                                        </div>
                                        <!--end::Card header-->
                                        <!--begin::Card body-->
                                        <div class="card-body pt-0">
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <!--begin::Label-->
                                                <label class="required form-label">{{__("product_name")}}</label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <input type="text" name="product[name]" value="{{@$product->name}}"
                                                       class="form-control  mb-2"/>
                                                <!--end::Input-->
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="mb-5">
                                                <!--begin::Label-->
                                                <label class="form-label">{{__("properties")}}</label>
                                                <!--end::Label-->
                                                <!--begin::Input-->
                                                <textarea name="product[properties]" class="form-control"
                                                          rows="8">{!! @$product->properties ?? "" !!}</textarea>
                                                <!--end::Input-->
                                            </div>
                                            <!--end::Input group-->
                                        </div>
                                        <!--end::Card header-->
                                    </div>
                                    <!--end::General options-->
                                    <!--begin::Pricing-->
                                    <div class="card card-flush py-4" data-np-price="container">
                                        <!--begin::Card header-->
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>{{__("pricing")}}</h2>
                                            </div>
                                        </div>
                                        <!--end::Card header-->
                                        <!--begin::Card body-->
                                        <div class="card-body pt-0">
                                            <div class="table-responsive mb-10">
                                                <!--begin::Table-->
                                                <table
                                                    class="table table-row-bordered g-3 gs-0 mb-0 fw-bold text-gray-700"
                                                    data-np-price="items">
                                                    <!--begin::Table head-->
                                                    <thead>
                                                    <tr class="border-bottom fs-6 fw-bold text-gray-700">
                                                        <th>{{__("period")}}</th>
                                                        <th>{{__("loop")}}</th>
                                                        <th>{{__("amount")}}</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <!--end::Table head-->
                                                    <!--begin::Table body-->
                                                    <tbody>
                                                    @if(isset($product) && count($product->prices) > 0)
                                                        @foreach($product->prices as $price)
                                                            <tr class="border-bottom border-bottom-dashed"
                                                                data-np-price="item">
                                                                <td class="w-150px">
                                                                    <input type="hidden" name="pricing[old][price_id][]"
                                                                           value="{{$price->id}}">
                                                                    <input type="text" name="pricing[old][duration][]"
                                                                           value="{{$price->duration}}"
                                                                           placeholder="{{__("period")}}"
                                                                           class="form-control form-control-sm"/>
                                                                </td>
                                                                <td class="w-225px">
                                                                    <x-admin.form-elements.duration-unit-select
                                                                        name="pricing[old][duration_unit][]"
                                                                        :selectedOption="$price->duration_unit"
                                                                        customClass="form-select-sm"
                                                                        selectedOption="{{$price->duration_unit}}"/>
                                                                </td>
                                                                <td class="w-225px">
                                                                    <input
                                                                        class="form-control form-control-sm text-end"
                                                                        data-np-price="price"
                                                                        value="{{showBalance($price->price)}}"
                                                                        name="pricing[old][price][]" placeholder="0,00"
                                                                        type="text">
                                                                </td>
                                                                <td class="d-flex align-items-center">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" {{$price->is_test_product ? 'checked' : ''}} name="pricing[old][is_test_product][{{$price->id}}]" value="1" id="isTest{{$price->id}}" />
                                                                        <label class="form-check-label" for="isTest{{$price->id}}">
                                                                            Test Ürünü
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="mt-1">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-icon btn-active-color-primary"
                                                                                data-np-price="remove-item">
                                                                            <i class="ki-duotone ki-trash fs-3">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                                <span class="path4"></span>
                                                                                <span class="path5"></span>
                                                                            </i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr class="border-bottom border-bottom-dashed"
                                                            data-np-price="item">
                                                            <td class="w-150px">
                                                                <input type="text" name="pricing[new][duration][]"
                                                                       placeholder="{{__("period")}}"
                                                                       class="form-control form-control-sm"/>
                                                            </td>
                                                            <td class="w-225px">
                                                                <x-admin.form-elements.duration-unit-select
                                                                    name="pricing[new][duration_unit][]"
                                                                    customClass="form-select-sm"
                                                                    selectedOption="HOUR"/>
                                                            </td>
                                                            <td class="w-225px">
                                                                <input
                                                                    class="form-control form-control-sm text-end"
                                                                    data-np-price="price"
                                                                    name="pricing[new][price][]" placeholder="0,00"
                                                                    type="text">
                                                            </td>
                                                            <td>
                                                                <div class="mt-1">
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-icon btn-active-color-primary"
                                                                            data-np-price="remove-item">
                                                                        <i class="ki-duotone ki-trash fs-3">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                            <span class="path3"></span>
                                                                            <span class="path4"></span>
                                                                            <span class="path5"></span>
                                                                        </i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    </tbody>
                                                    <!--end::Table body-->
                                                    <!--begin::Table foot-->
                                                    <tfoot>
                                                    <tr class="border-top border-top-dashed align-top fs-6 fw-bold text-gray-700">
                                                        <th>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-light-primary me-3"
                                                                    data-np-price="add-item">
														<span class="svg-icon svg-icon-2">
															<i class="fa fa-plus"></i>
														</span>
                                                                {{__("add_row")}}
                                                            </button>
                                                        </th>
                                                    </tr>
                                                    </tfoot>
                                                    <!--end::Table foot-->
                                                </table>
                                                <table class="d-none" data-np-price="item-template">
                                                    <tr>
                                                        <td class="w-150px">
                                                            <input type="text" name="pricing[new][duration][]"
                                                                   placeholder="{{__("period")}}"
                                                                   class="form-control form-control-sm"/>
                                                        </td>
                                                        <td class="w-250px">
                                                            <x-admin.form-elements.duration-unit-select
                                                                customClass="form-select-sm"
                                                                name="pricing[new][duration_unit][]" :select2="false"/>
                                                        </td>
                                                        <td class="w-275px">
                                                            <input
                                                                class="form-control form-control-sm text-end"
                                                                data-np-price="price"
                                                                name="pricing[new][price][]" placeholder="0,00"
                                                                type="text">
                                                        </td>
                                                        <td>
                                                            <div class="mt-1">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-icon btn-active-color-primary"
                                                                        data-np-price="remove-item">
                                                                    <i class="ki-duotone ki-trash fs-3">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                        <span class="path3"></span>
                                                                        <span class="path4"></span>
                                                                        <span class="path5"></span>
                                                                    </i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <!--end::Card body-->
                                    </div>
                                    <!--end::Pricing-->
                                    <!--begin::Additional Services-->
                                    <div class="card card-flush py-4" data-np-additional-services="container">
                                        <!--begin::Card header-->
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>{{__("additional_services")}}</h2>
                                            </div>
                                        </div>
                                        <!--end::Card header-->
                                        <!--begin::Card body-->
                                        <div class="card-body pt-0">
                                            <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#np_as_tab_localtonet">Mobil Proxy / Localtonet</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#np_as_tab_threeproxy">3Proxy</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                            <div class="tab-pane fade show active" id="np_as_tab_localtonet">
                                            <div>
                                                <label class="form-label fw-bold fs-5">Sipariş oluşturma esnasında
                                                    kullanıcıya sorulur</label>
                                                <label class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input h-25px w-25px" type="checkbox"
                                                           {{isset($proxyTypeAttr) ? "checked" : ""}}
                                                           name="service_type[protocol_select][status]" value="1">
                                                    <span class="form-check-label fw-semibold">
                                                        Protocol Seçimi
                                                    </span>
                                                </label>
                                                <div class="row mt-5 g-2" data-np-additional-services="protocol-area">
                                                    <div class="col-xl-4">
                                                        <label class="fs-6 fw-bold text-gray-700">Protocol</label>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label
                                                            class="fs-6 fw-bold text-gray-700">{{__("price")}}</label>
                                                    </div>
                                                    <div class="col-12 mt-3">
                                                        <div class="separator"></div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="row mb-3">
                                                            <div class="col-xl-4 d-flex align-center">
                                                                HTTP Ücretsiz
                                                            </div>
                                                            <div class="col-xl-4 d-flex">
                                                                <input type="text"
                                                                       data-np-price="price"
                                                                       name="service_type[protocol_select][price][]"
                                                                       value="{{isset($proxyTypeAttr) ? showBalance(@$proxyTypeAttr["options"][0]["price"]) : null}}"
                                                                       placeholder="{{__("price")}}"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-xl-4 d-flex align-center">
                                                                Socks5
                                                            </div>
                                                            <div class="col-xl-4 d-flex">
                                                                <input type="text"
                                                                       data-np-price="price"
                                                                       name="service_type[protocol_select][price][]"
                                                                       value="{{isset($proxyTypeAttr) ? showBalance(@$proxyTypeAttr["options"][1]["price"]) : null}}"
                                                                       placeholder="{{__("price")}}"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="separator my-5"></div>
                                            <div>
                                                <label class="form-label fw-bold fs-5">Sipariş oluştuktan sonra kota
                                                    ekle seçenekleri arasında görünür</label>
                                                <label class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input h-25px w-25px" type="checkbox"
                                                           {{isset($quotaAttr) ? "checked" : ""}}
                                                           name="service_type[quota][status]" value="1">
                                                    <span class="form-check-label fw-semibold">
                                                        Ek Kota
                                                    </span>
                                                </label>
                                                <div class="row mt-5 g-2" data-np-additional-services='quota-area'>
                                                    <div class="col-xl-4">
                                                        <label class="fs-6 fw-bold text-gray-700">Başlık</label>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label class="fs-6 fw-bold text-gray-700">GB</label>
                                                    </div>
                                                    <div class="col-xl-4">
                                                        <label
                                                            class="fs-6 fw-bold text-gray-700">{{__("price")}}</label>
                                                    </div>
                                                    <div class="col-12 mt-3">
                                                        <div class="separator"></div>
                                                    </div>
                                                    <div class="col-12" data-np-quota="items">
                                                        @if(isset($quotaAttr))
                                                            @foreach(@$quotaAttr["options"] as $options)
                                                                <div class="row mb-3 g-2" data-np-quota="item">
                                                                    <div class="col-xl-4">
                                                                        <input type="text"
                                                                               name="service_type[quota][label][]"
                                                                               value="{{$options["label"]}}"
                                                                               placeholder="Başlık"
                                                                               class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <input type="text"
                                                                               name="service_type[quota][value][]"
                                                                               value="{{$options["value"]}}"
                                                                               placeholder="GB"
                                                                               class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-xl-4 d-flex">
                                                                        <input type="text"
                                                                               data-np-price="price"
                                                                               name="service_type[quota][price][]"
                                                                               value="{{showBalance($options["price"])}}"
                                                                               placeholder="{{__("price")}}"
                                                                               class="form-control form-control-sm">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-icon btn-active-color-primary"
                                                                                data-np-quota="remove-item">
                                                                            <i class="ki-duotone ki-trash fs-3">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                                <span class="path4"></span>
                                                                                <span class="path5"></span>
                                                                            </i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-sm btn-light-primary me-3"
                                                                data-np-quota="add-item">
														<span class="svg-icon svg-icon-2">
															<i class="fa fa-plus"></i>
														</span>
                                                            Satır Ekle
                                                        </button>
                                                    </div>
                                                    <div class="d-none" data-np-quota="item-template">
                                                        <div class="row mb-3 g-2" data-np-quota="item">
                                                            <div class="col-xl-4">
                                                                <input type="text" name="service_type[quota][label][]"
                                                                       value="" placeholder="Başlık"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                            <div class="col-xl-4">
                                                                <input type="text" name="service_type[quota][value][]"
                                                                       value="" placeholder="GB"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                            <div class="col-xl-4 d-flex">
                                                                <input type="text" name="service_type[quota][price][]"
                                                                       value="" placeholder="{{__("price")}}"
                                                                       data-np-price="price"
                                                                       class="form-control form-control-sm">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-icon btn-active-color-primary"
                                                                        data-np-quota="remove-item">
                                                                    <i class="ki-duotone ki-trash fs-3">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                        <span class="path3"></span>
                                                                        <span class="path4"></span>
                                                                        <span class="path5"></span>
                                                                    </i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="separator my-5"></div>
                                            <div>
                                                <label class="form-label fw-bold fs-5">Sipariş oluştuktan sonra kota ve
                                                    süre
                                                    ekle seçenekleri arasında görünür</label>
                                                <label class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input h-25px w-25px" type="checkbox"
                                                           {{isset($quotaDurationAttr) ? "checked" : ""}}
                                                           name="service_type[quota_duration][status]" value="1">
                                                    <span class="form-check-label fw-semibold">
                                                        Ek Kota ve Süre
                                                    </span>
                                                </label>
                                                <div class="row mt-5 g-2" data-np-additional-services='quota-duration-area'>
                                                    <div class="col-xl-3">
                                                        <label class="fs-6 fw-bold text-gray-700">Başlık</label>
                                                    </div>
                                                    <div class="col-xl-3">
                                                        <label class="fs-6 fw-bold text-gray-700">GB</label>
                                                    </div>
                                                    <div class="col-xl-3">
                                                        <label
                                                            class="fs-6 fw-bold text-gray-700">{{__("duration")}}</label>
                                                    </div>
                                                    <div class="col-xl-3">
                                                        <label
                                                            class="fs-6 fw-bold text-gray-700">{{__("price")}}</label>
                                                    </div>
                                                    <div class="col-12 mt-3">
                                                        <div class="separator"></div>
                                                    </div>
                                                    <div class="col-12" data-np-quota-duration="items">
                                                        @if(isset($quotaDurationAttr))
                                                            @foreach(@$quotaDurationAttr["options"] as $option)
                                                                <div class="row mb-3 g-2" data-np-quota-duration="item">
                                                                    <div class="col-xl-3">
                                                                        <input type="text"
                                                                               name="service_type[quota_duration][label][]"
                                                                               value="{{$option["label"]}}"
                                                                               placeholder="Başlık"
                                                                               class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-xl-3">
                                                                        <input type="text"
                                                                               name="service_type[quota_duration][gb][]"
                                                                               value="{{$option["gb"]}}"
                                                                               placeholder="GB"
                                                                               class="form-control form-control-sm">
                                                                    </div>
                                                                    <div class="col-xl-3 d-flex">
                                                                        <input type="text"
                                                                               name="service_type[quota_duration][duration][]"
                                                                               value="{{$option["duration"]}}"
                                                                               placeholder="{{__("duration")}}"
                                                                               class="form-control form-control-sm mw-50px">
                                                                        <x-admin.form-elements.duration-unit-select
                                                                            name="service_type[quota_duration][duration_unit][]"
                                                                            :selectedOption="$option['duration_unit']"
                                                                            :placeholder="__('loop')"
                                                                            customClass="form-select-sm"/>
                                                                    </div>
                                                                    <div class="col-xl-3 d-flex">
                                                                        <input type="text"
                                                                               name="service_type[quota_duration][price][]"
                                                                               value="{{showBalance($option["price"])}}"
                                                                               placeholder="{{__("price")}}"
                                                                               data-np-price="price"
                                                                               class="form-control form-control-sm">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-icon btn-active-color-primary"
                                                                                data-np-quota-duration="remove-item">
                                                                            <i class="ki-duotone ki-trash fs-3">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                                <span class="path4"></span>
                                                                                <span class="path5"></span>
                                                                            </i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="button" class="btn btn-sm btn-light-primary me-3"
                                                                data-np-quota-duration="add-item">
														<span class="svg-icon svg-icon-2">
															<i class="fa fa-plus"></i>
														</span>
                                                            Satır Ekle
                                                        </button>
                                                    </div>
                                                    <div class="d-none" data-np-quota-duration="item-template">
                                                        <div class="row mb-3 g-2" data-np-quota-duration="item">
                                                            <div class="col-xl-3">
                                                                <input type="text"
                                                                       name="service_type[quota_duration][label][]"
                                                                       value="" placeholder="Başlık"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                            <div class="col-xl-3">
                                                                <input type="text"
                                                                       name="service_type[quota_duration][gb][]"
                                                                       value="" placeholder="GB"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                            <div class="col-xl-3 d-flex">
                                                                <input type="text"
                                                                       name="service_type[quota_duration][duration][]"
                                                                       value="" placeholder="{{__("duration")}}"
                                                                       class="form-control form-control-sm mw-50px">
                                                                <x-admin.form-elements.duration-unit-select
                                                                    name="service_type[quota_duration][duration_unit][]"
                                                                    :placeholder="__('loop')"
                                                                    :select2="false"
                                                                    customClass="form-select-sm"/>
                                                            </div>
                                                            <div class="col-xl-3 d-flex">
                                                                <input type="text"
                                                                       name="service_type[quota_duration][price][]"
                                                                       value="" placeholder="{{__("price")}}"
                                                                       data-np-price="price"
                                                                       class="form-control form-control-sm">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-icon btn-active-color-primary"
                                                                        data-np-quota-duration="remove-item">
                                                                    <i class="ki-duotone ki-trash fs-3">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                        <span class="path3"></span>
                                                                        <span class="path4"></span>
                                                                        <span class="path5"></span>
                                                                    </i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                            <!--end::Localtonet tab-->

                                            <!--begin::3Proxy tab-->
                                            <div class="tab-pane fade" id="np_as_tab_threeproxy">
                                                <div class="mb-7">
                                                    <label class="form-check form-check-custom form-check-solid mb-5">
                                                        <input class="form-check-input h-25px w-25px" type="checkbox"
                                                               {{ isset($tpExtraDurationAttr) ? "checked" : "" }}
                                                               name="service_type[tp_extra_duration][status]" value="1">
                                                        <span class="form-check-label fw-semibold">Ek Süre</span>
                                                    </label>
                                                    <div class="row mt-3 g-2" data-np-tp-duration="area">
                                                        <div class="col-xl-3"><label class="fs-6 fw-bold text-gray-700">Başlık</label></div>
                                                        <div class="col-xl-2"><label class="fs-6 fw-bold text-gray-700">Süre</label></div>
                                                        <div class="col-xl-3"><label class="fs-6 fw-bold text-gray-700">Periyot</label></div>
                                                        <div class="col-xl-3"><label class="fs-6 fw-bold text-gray-700">{{ __("price") }}</label></div>
                                                        <div class="col-12 mt-3"><div class="separator"></div></div>
                                                        <div class="col-12" data-np-tp-duration="items">
                                                            @if(isset($tpExtraDurationAttr))
                                                                @foreach($tpExtraDurationAttr["options"] as $opt)
                                                                    <div class="row mb-3 g-2" data-np-tp-duration="item">
                                                                        <div class="col-xl-3"><input type="text" name="service_type[tp_extra_duration][label][]" value="{{ $opt['label'] }}" placeholder="Başlık" class="form-control form-control-sm"></div>
                                                                        <div class="col-xl-2"><input type="text" name="service_type[tp_extra_duration][duration][]" value="{{ $opt['duration'] }}" placeholder="Süre" class="form-control form-control-sm"></div>
                                                                        <div class="col-xl-3">
                                                                            <x-admin.form-elements.duration-unit-select name="service_type[tp_extra_duration][duration_unit][]" :selectedOption="$opt['duration_unit']" :placeholder="__('loop')" customClass="form-select-sm"/>
                                                                        </div>
                                                                        <div class="col-xl-3 d-flex">
                                                                            <input type="text" name="service_type[tp_extra_duration][price][]" value="{{ showBalance($opt['price']) }}" placeholder="{{ __('price') }}" data-np-price="price" class="form-control form-control-sm">
                                                                            <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-np-tp-duration="remove-item"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <div class="col-12">
                                                            <button type="button" class="btn btn-sm btn-light-primary me-3" data-np-tp-duration="add-item"><i class="fa fa-plus me-1"></i>Satır Ekle</button>
                                                        </div>
                                                        <div class="d-none" data-np-tp-duration="item-template">
                                                            <div class="row mb-3 g-2" data-np-tp-duration="item">
                                                                <div class="col-xl-3"><input type="text" name="service_type[tp_extra_duration][label][]" value="" placeholder="Başlık" class="form-control form-control-sm"></div>
                                                                <div class="col-xl-2"><input type="text" name="service_type[tp_extra_duration][duration][]" value="" placeholder="Süre" class="form-control form-control-sm"></div>
                                                                <div class="col-xl-3">
                                                                    <x-admin.form-elements.duration-unit-select name="service_type[tp_extra_duration][duration_unit][]" :placeholder="__('loop')" :select2="false" customClass="form-select-sm"/>
                                                                </div>
                                                                <div class="col-xl-3 d-flex">
                                                                    <input type="text" name="service_type[tp_extra_duration][price][]" value="" placeholder="{{ __('price') }}" data-np-price="price" class="form-control form-control-sm">
                                                                    <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-np-tp-duration="remove-item"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="separator my-5"></div>
                                                <div class="mb-7">
                                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                                        <input class="form-check-input h-25px w-25px" type="checkbox"
                                                               {{ isset($tpChangeIpsAttr) ? "checked" : "" }}
                                                               name="service_type[tp_change_ips][status]" value="1">
                                                        <span class="form-check-label fw-semibold">IP'leri Değiştir</span>
                                                    </label>
                                                    <div class="row mt-3 g-2">
                                                        <div class="col-xl-4">
                                                            <label class="fs-7 fw-semibold text-gray-600">Ücret</label>
                                                            <input type="text" name="service_type[tp_change_ips][price]" value="{{ isset($tpChangeIpsAttr) ? showBalance($tpChangeIpsAttr['price']) : '' }}" placeholder="{{ __('price') }}" data-np-price="price" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="separator my-5"></div>
                                                <div class="mb-7">
                                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                                        <input class="form-check-input h-25px w-25px" type="checkbox"
                                                               {{ isset($tpSubnetIpsAttr) ? "checked" : "" }}
                                                               name="service_type[tp_subnet_ips][status]" value="1">
                                                        <span class="form-check-label fw-semibold">Her Subnetten Farklı IP</span>
                                                    </label>
                                                    <div class="form-text mb-3">Havuzdaki IP'leri /24 subnet bazında analiz eder, her subnetten farklı IP atar.</div>
                                                    <div class="row g-2">
                                                        <div class="col-xl-4">
                                                            <label class="fs-7 fw-semibold text-gray-600">Ücret</label>
                                                            <input type="text" name="service_type[tp_subnet_ips][price]" value="{{ isset($tpSubnetIpsAttr) ? showBalance($tpSubnetIpsAttr['price']) : '' }}" placeholder="{{ __('price') }}" data-np-price="price" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="separator my-5"></div>
                                                <div class="mb-0">
                                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                                        <input class="form-check-input h-25px w-25px" type="checkbox"
                                                               {{ isset($tpClassIpsAttr) ? "checked" : "" }}
                                                               name="service_type[tp_class_ips][status]" value="1">
                                                        <span class="form-check-label fw-semibold">Her Class IP'den Farklı IP</span>
                                                    </label>
                                                    <div class="form-text mb-3">Havuzdaki IP'leri /16 Class bazında analiz eder, her class'tan farklı IP atar.</div>
                                                    <div class="row g-2">
                                                        <div class="col-xl-4">
                                                            <label class="fs-7 fw-semibold text-gray-600">Ücret</label>
                                                            <input type="text" name="service_type[tp_class_ips][price]" value="{{ isset($tpClassIpsAttr) ? showBalance($tpClassIpsAttr['price']) : '' }}" placeholder="{{ __('price') }}" data-np-price="price" class="form-control form-control-sm">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::3Proxy tab-->

                                            </div><!-- end tab-content -->
                                        </div>
                                        <!--end::Card body-->
                                    </div>
                                    <!--end::Additional Services-->
                                    @if(isset($product))
                                        <!--begin::Upgrade-->
                                        <div class="card card-flush py-4">
                                            <!--begin::Card header-->
                                            <div class="card-header">
                                                <div class="card-title">
                                                    <h2>{{__("upgrade")}}</h2>
                                                </div>
                                            </div>
                                            <!--end::Card header-->
                                            <!--begin::Card body-->
                                            <div class="card-body pt-0">
                                                <div class="alert alert-primary">
                                                    Fiyatlandırmada kaydedilmemiş bir değişiklik varsa önce fiyatları
                                                    kaydetip arından yükseltmelerde düzenleme yapmanız önerilir.
                                                </div>
                                                <div class="table-responsive mb-10">
                                                    <!--begin::Table-->
                                                    <table
                                                        class="table table-row-bordered g-3 gs-0 gy-5 mb-0 fw-bold text-gray-700">
                                                        <!--begin::Table head-->
                                                        <thead>
                                                        <tr class="border-bottom fs-6 fw-bold text-gray-700">
                                                            <th>{{__("product")}}</th>
                                                            <th>{{__("Yükseltilebilir Ürünler")}}</th>
                                                        </tr>
                                                        </thead>
                                                        <!--end::Table head-->
                                                        <!--begin::Table body-->
                                                        <tbody>
                                                        @if(isset($product) && count($product->prices) > 0)
                                                            @foreach($product->prices as $price)
                                                                @php
                                                                    $upgradePriceOptions = $product->prices->filter(function ($item) use ($price) {
                                                                        return $item->id != $price->id;
                                                                    })->map(function ($item) {
                                                                        return [
                                                                            'value' => $item->id,
                                                                            'label' => $item->duration . " " . __(mb_strtolower($item->duration_unit)) . " (" . showBalance($item->price, true) . ")",
                                                                        ];
                                                                    });
                                                                @endphp
                                                                <tr class="border-bottom border-bottom-dashed">
                                                                    <td>
                                                                        {{$price->duration}} {{__(mb_strtolower($price->duration_unit))}}
                                                                        ({{showBalance($price->price, true)}})
                                                                    </td>
                                                                    <td>
                                                                        <x-admin.form-elements.select
                                                                            name="upgrade[price_id][{{$price->id}}][]"
                                                                            customClass="form-select-sm"
                                                                            :options="$upgradePriceOptions"
                                                                            :selectedOption="$price->upgradeable_price_ids"
                                                                            customAttr="multiple"/>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else

                                                        @endif
                                                        </tbody>
                                                        <!--end::Table body-->
                                                    </table>
                                                </div>
                                            </div>
                                            <!--end::Card body-->
                                        </div>
                                        <!--end::Upgrade-->
                                    @endif
                                </div>
                            </div>
                            <!--end::Tab pane-->
                            <div class="tab-pane fade" id="delivery_tab">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <!--begin::General options-->
                                    <div class="card card-flush py-4">
                                        <!--begin::Card header-->
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Teslimat Bilgileri</h2>
                                            </div>
                                        </div>
                                        <!--end::Card header-->
                                        <!--begin::Card body-->
                                        <div class="card-body pt-0">
                                            <!--begin::Input group-->
                                            <!--begin::Heading-->
                                            <div class="mb-3">
                                                <!--begin::Label-->
                                                <label class="d-flex align-items-center fs-5 fw-semibold">
                                                    <span class="required">Teslimat Tipi</span>
                                                </label>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Heading-->
                                            <!--begin::Radio group-->
                                            <div class="btn-group w-100 w-lg-50 mb-3" data-kt-buttons="true"
                                                 data-kt-buttons-target="[data-kt-button]">
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-color-muted btn-active-success {{@$product->delivery_type == 'STACK' ? 'active' : ''}}"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio" name="product[delivery_type]"
                                                           {{@$product->delivery_type == 'STACK' ? 'checked="checked"' : ''}} value="STACK"/>
                                                    <!--end::Input-->
                                                    Stoktan Teslim
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-color-muted btn-active-success {{@$product->delivery_type == 'LOCALTONET' ? 'active' : ''}}"
                                                    data-kt-button="true">
                                                    <!--begin::Input-->
                                                    <input class="btn-check" type="radio" name="product[delivery_type]"
                                                           {{@$product->delivery_type == 'LOCALTONET' ? 'checked="checked"' : ''}} value="LOCALTONET"/>
                                                    <!--end::Input-->
                                                    Localtonet
                                                </label>
                                                <!--end::Radio-->
                                                <label
                                                    class="btn btn-outline btn-color-muted btn-active-success {{@$product->delivery_type == 'LOCALTONETV4' ? 'active' : ''}}"
                                                    data-kt-button="true">
                                                    <input class="btn-check" type="radio" name="product[delivery_type]"
                                                           {{@$product->delivery_type == 'LOCALTONETV4' ? 'checked="checked"' : ''}} value="LOCALTONETV4"/>
                                                    Localtonetv4
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-color-muted btn-active-success {{@$product->delivery_type == 'THREEPROXY' ? 'active' : ''}}"
                                                    data-kt-button="true">
                                                    <input class="btn-check" type="radio" name="product[delivery_type]"
                                                           {{@$product->delivery_type == 'THREEPROXY' ? 'checked="checked"' : ''}} value="THREEPROXY"/>
                                                    3Proxy
                                                </label>
                                                <!--end::Radio-->
                                                <!--begin::Radio-->
                                                <label
                                                    class="btn btn-outline btn-color-muted btn-active-success {{@$product->delivery_type == 'LOCALTONET_ROTATING' ? 'active' : ''}}"
                                                    data-kt-button="true">
                                                    <input class="btn-check" type="radio" name="product[delivery_type]"
                                                           {{@$product->delivery_type == 'LOCALTONET_ROTATING' ? 'checked="checked"' : ''}} value="LOCALTONET_ROTATING"/>
                                                    Localtonet Rotating
                                                </label>
                                                <!--end::Radio-->
                                            </div>
                                            <!--end::Radio group-->
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="show-on-stack">
                                                <div class="mb-5">
                                                    <!--begin::Label-->
                                                    <label class="form-label">Adet</label>
                                                    <!--end::Label-->
                                                    <!--begin::Input-->
                                                    <input type="number" class="form-control" name="delivery_count"
                                                           value="{{isset($product) && $product->delivery_type == "STACK" && isset($product->delivery_items["delivery_count"]) ? $product->delivery_items["delivery_count"] : ""}}">
                                                    <!--end::Input-->
                                                </div>
                                                <div class="mb-5">
                                                    <!--begin::Label-->
                                                    <label class="form-label">Proxy Listesi</label>
                                                    <!--end::Label-->
                                                    <!--begin::Input-->
                                                    <textarea name="product[delivery_items]" class="form-control"
                                                              rows="8">@if(isset($product) && $product->delivery_type == "STACK" && isset($product->delivery_items["proxies"])){!! implode('&#10;',$product->delivery_items["proxies"]) !!}@endif</textarea>
                                                    <!--end::Input-->
                                                </div>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::Input group-->
                                            <div class="show-on-localtonet-family">
                                                <div class="mb-5 alert alert-primary">
                                                    Kullanım limitinin sınırsız olmasını isterseniz 0 girmelisiniz.
                                                </div>
                                                <div class="mb-5">
                                                    <!--begin::Label-->
                                                    <label class="form-label">Kullanım Limiti</label>
                                                    <!--end::Label-->
                                                    <div class="row">
                                                        <div class="col-9">
                                                            <!--begin::Input-->
                                                            <input type="text" class="form-control" name="data_size"
                                                                   value="{{isset($product) && in_array($product->delivery_type, ['LOCALTONET','LOCALTONETV4'], true) && isset($product->delivery_items['bandwidth_limit']) ? @$product->delivery_items['bandwidth_limit']['data_size'] : ""}}">
                                                            <!--end::Input-->
                                                        </div>
                                                        <div class="col-3">
                                                            <!--begin::Select-->
                                                            <x-admin.form-elements.select name="data_size_type"
                                                                                          :hideSearch="true"
                                                                                          :options="[
                                                                                    ['label' => 'MB', 'value' => '3'],
                                                                                    ['label' => 'GB', 'value' => '4'],
                                                                                    ]"
                                                                                          :selectedOption="isset($product) && in_array($product->delivery_type, ['LOCALTONET','LOCALTONETV4'], true) && isset($product->delivery_items['bandwidth_limit']) ? @$product->delivery_items['bandwidth_limit']['data_size_type'] : '4'"/>
                                                            <!--end::Select-->
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="show-on-localtonet-only">
                                                    <div class="mb-5">
                                                        <!--begin::Label-->
                                                        <label class="form-label required">Token Havuzu</label>
                                                        <!--end::Label-->
                                                        <select name="product[token_pool_id]" id="" data-control="select2" class="form-control">
                                                            <option value="">Seçim yok</option>
                                                            @foreach($tokenPools as $tokenPool)
                                                                <option {{isset($product) && @$product->delivery_items['token_pool_id'] == $tokenPool->id ? 'selected' : ''}} value="{{$tokenPool->id}}">#{{$tokenPool->id}}-{{$tokenPool->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <!--begin::Input-->
                                                        <x-admin.form-elements.auth-token-select
                                                            name="product[auth_tokens][]"
                                                            customAttr="multiple"
                                                            customClass="mw-100 d-none"
                                                            :selectedOption="isset($product) && $product->delivery_type == 'LOCALTONET' && isset($product->delivery_items['auth_tokens']) ? $product->delivery_items['auth_tokens'] : ''"/>
                                                        <!--end::Input-->
                                                    </div>
                                                </div>
                                                <div class="show-on-localtonet-v4-only">
                                                    <div class="row mb-5">
                                                        <div class="col-md-6 mb-5 mb-md-0">
                                                            <label class="form-label">Localtonet API URL</label>
                                                            <input type="text" class="form-control" name="v4_api_url"
                                                                   value="{{ isset($product) && $product->delivery_type == 'LOCALTONETV4' ? ($product->delivery_items['api_url'] ?? 'https://localtonet.com/api') : 'https://localtonet.com/api' }}"
                                                                   placeholder="https://localtonet.com/api">
                                                            <div class="form-text">Localtonet API base URL (varsayılan: https://localtonet.com/api)</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label required">Localtonet API Key</label>
                                                            <input type="text" class="form-control" name="v4_api_key" autocomplete="off"
                                                                   value="{{ isset($product) && $product->delivery_type == 'LOCALTONETV4' ? ($product->delivery_items['api_key'] ?? '') : '' }}">
                                                            <div class="form-text">Localtonet API anahtarı (Bearer token).</div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-5">
                                                        <div class="col-md-6 mb-5 mb-md-0">
                                                            <label class="form-label">Server Code</label>
                                                            <input type="text" class="form-control" name="v4_server_code"
                                                                   value="{{ isset($product) && $product->delivery_type == 'LOCALTONETV4' ? ($product->delivery_items['server_code'] ?? 'app') : 'app' }}">
                                                            <div class="form-text">Localtonet sunucu kodu (varsayılan: app).</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Teslimat sayısı</label>
                                                            <input type="number" min="1" max="1000" class="form-control" name="v4_delivery_count"
                                                                   value="{{ isset($product) && $product->delivery_type == 'LOCALTONETV4' ? (int)($product->delivery_items['delivery_count'] ?? 1) : 1 }}">
                                                            <div class="form-text">Her siparişte kaç adet teslim edilmeli? (şu an sipariş başına tek tünel; değer kayıt için saklanır.)</div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-5">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Yerel sunucu IP (Local Server IP)</label>
                                                            <input type="text" class="form-control" name="v4_local_server_ip"
                                                                   value="{{ isset($product) && $product->delivery_type == 'LOCALTONETV4' ? ($product->delivery_items['local_server_ip'] ?? '') : '' }}"
                                                                   placeholder="Örn. 127.0.0.1 veya çıkış makinenizin LAN IP’si">
                                                            <div class="form-text">IP listesi doluysa önce listeden seçilen IP kullanılır. Liste boşsa bu alan veya API sunucu listesi / 127.0.0.1 yedeklenir.</div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-5">
                                                        <label class="form-label fw-bold required">IP Havuzu Seçimi</label>
                                                        <select name="v4_ip_pool_id" class="form-select">
                                                            <option value="">Seçiniz...</option>
                                                            @foreach(\App\Models\IpPool::orderBy('name')->get() as $ipPool)
                                                                <option value="{{ $ipPool->id }}"
                                                                    {{ isset($product) && ($product->delivery_items['ip_pool_id'] ?? null) == $ipPool->id ? 'selected' : '' }}>
                                                                    {{ $ipPool->name }} ({{ $ipPool->getEntryCount() }} token / {{ $ipPool->getTotalIpCount() }} IP)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="form-text">
                                                            Havuz içeriği <a href="{{ route('admin.tokenPools.index') }}" target="_blank">Havuz Yönetimi &gt; IP Havuzları</a> sekmesinden düzenlenir.
                                                            Teslimatta havuzdan rastgele token ve IP seçilir.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::Input group-->
                                            <!--begin::3Proxy Input group-->
                                            <div class="show-on-threeproxy">
                                                <div class="mb-5">
                                                    <label class="form-label fw-bold required">3Proxy Havuzu Seçimi</label>
                                                    <select name="three_proxy_pool_id" class="form-select" data-control="select2">
                                                        <option value="">Seçiniz...</option>
                                                        @foreach(\App\Models\ThreeProxyPool::orderBy('name')->get() as $tpPool)
                                                            <option value="{{ $tpPool->id }}"
                                                                {{ isset($product) && ($product->delivery_items['three_proxy_pool_id'] ?? null) == $tpPool->id ? 'selected' : '' }}>
                                                                {{ $tpPool->name }} (Port: {{ $tpPool->port }} / {{ $tpPool->getIpCount() }} IP)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">
                                                        Havuz içeriği <a href="{{ route('admin.tokenPools.index') }}" target="_blank">Havuz Yönetimi &gt; 3Proxy Havuzu</a> sekmesinden düzenlenir.
                                                    </div>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="form-label">Teslimat Sayısı</label>
                                                    <input type="number" min="1" max="1000" class="form-control" name="tp_delivery_count"
                                                           value="{{ isset($product) && $product->delivery_type == 'THREEPROXY' ? (int)($product->delivery_items['delivery_count'] ?? 1) : 1 }}">
                                                    <div class="form-text">Her siparişte kaç adet proxy teslim edilmeli?</div>
                                                </div>
                                            </div>
                                            <!--end::3Proxy Input group-->
                                            <!--begin::Localtonet Rotating Input group-->
                                            <div class="show-on-localtonet-rotating">
                                                <div class="mb-5">
                                                    <label class="form-label fw-bold required">Localtonet Rotating Havuzu Seçimi</label>
                                                    <select name="lr_pool_id" class="form-select" data-control="select2">
                                                        <option value="">Seçiniz...</option>
                                                        @foreach(\App\Models\LocaltonetRotatingPool::orderBy('name')->get() as $lrPool)
                                                            <option value="{{ $lrPool->id }}"
                                                                {{ isset($product) && ($product->delivery_items['lr_pool_id'] ?? null) == $lrPool->id ? 'selected' : '' }}>
                                                                {{ $lrPool->name }} ({{ $lrPool->getTypeLabel() }} / {{ $lrPool->getTunnelCount() }} Tunnel)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">
                                                        Havuz içeriği <a href="{{ route('admin.tokenPools.index') }}" target="_blank">Havuz Yönetimi &gt; Localtonet Rotating</a> sekmesinden düzenlenir.
                                                    </div>
                                                </div>
                                                <div class="row mb-5">
                                                    <div class="col-md-6 mb-5 mb-md-0">
                                                        <label class="form-label required">Server Code</label>
                                                        <input type="text" class="form-control" name="lr_server_code"
                                                               value="{{ isset($product) && $product->delivery_type == 'LOCALTONET_ROTATING' ? ($product->delivery_items['server_code'] ?? 'app') : 'app' }}"
                                                               placeholder="Örn: app">
                                                        <div class="form-text">Localtonet sunucu kodu (varsayılan: app).</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Teslimat Sayısı</label>
                                                        <input type="number" min="1" max="1000" class="form-control" name="lr_delivery_count"
                                                               value="{{ isset($product) && $product->delivery_type == 'LOCALTONET_ROTATING' ? (int)($product->delivery_items['delivery_count'] ?? 1) : 1 }}">
                                                        <div class="form-text">Her siparişte kaç adet proxy teslim edilmeli?</div>
                                                    </div>
                                                </div>
                                                <div class="row mb-5">
                                                    <div class="col-md-6 mb-5 mb-md-0">
                                                        <label class="form-label required">Host Domain / Server IP</label>
                                                        <input type="text" class="form-control" name="lr_host"
                                                               value="{{ isset($product) && $product->delivery_type == 'LOCALTONET_ROTATING' ? ($product->delivery_items['host'] ?? '') : '' }}"
                                                               placeholder="Örn: proxy.example.com veya 85.44.12.100">
                                                        <div class="form-text">Kullanıcıya gösterilecek host domain veya sunucu IP adresi.</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Port</label>
                                                        <input type="number" class="form-control" name="lr_port"
                                                               value="{{ isset($product) && $product->delivery_type == 'LOCALTONET_ROTATING' ? ($product->delivery_items['port'] ?? '') : '' }}"
                                                               placeholder="Örn: 8080">
                                                        <div class="form-text">Kullanıcının bağlanacağı port numarası.</div>
                                                    </div>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="form-label">Kota (GB)</label>
                                                    <div class="row">
                                                        <div class="col-9">
                                                            <input type="text" class="form-control" name="lr_quota"
                                                                   value="{{ isset($product) && $product->delivery_type == 'LOCALTONET_ROTATING' ? ($product->delivery_items['quota']['data_size'] ?? '') : '' }}"
                                                                   placeholder="0 = Sınırsız">
                                                        </div>
                                                        <div class="col-3">
                                                            <x-admin.form-elements.select name="lr_quota_type"
                                                                                          :hideSearch="true"
                                                                                          :options="[
                                                                                    ['label' => 'MB', 'value' => '3'],
                                                                                    ['label' => 'GB', 'value' => '4'],
                                                                                    ]"
                                                                                          :selectedOption="isset($product) && $product->delivery_type == 'LOCALTONET_ROTATING' && isset($product->delivery_items['quota']) ? ($product->delivery_items['quota']['data_size_type'] ?? '4') : '4'"/>
                                                        </div>
                                                    </div>
                                                    <div class="form-text">Kullanıcıya atanacak bandwidth limiti. Sınırsız için 0 girin.</div>
                                                </div>
                                            </div>
                                            <!--end::Localtonet Rotating Input group-->
                                        </div>
                                        <!--end::Card header-->
                                    </div>
                                    <!--end::General options-->

                                </div>
                            </div>

                        </div>
                        <!--end::Tab content-->

                        <div class="d-flex justify-content-end">
                            <!--begin::Button-->
                            <button type="submit" class="btn btn-primary w-100" id="form_submit_btn">
                                        <span class="indicator-label">
                                            <span class="d-flex flex-center gap-2">
                                                <i class="ki-duotone ki-triangle fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i> {{__("save")}}
                                            </span>
                                        </span>
                                <span class="indicator-progress">
                                            {{__("please_wait")}}... <span
                                        class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                            </button>
                            <!--end::Button-->
                        </div>
                    </div>
                    <!--end::Main column-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    <!--begin::Modals-->
    {{-- v4 entries template kaldırıldı — artık IP havuzu dropdown kullanılıyor --}}
    <!--end::Modals-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            function npApplyProductDeliveryTypeUi() {
                var v = $('[name="product[delivery_type]"]:checked').val();
                $('.show-on-stack').hide(500);
                $('.show-on-localtonet-family').hide(500);
                $('.show-on-threeproxy').hide(500);
                $('.show-on-localtonet-rotating').hide(500);
                if (v === 'STACK') {
                    $('.show-on-stack').show(500);
                } else if (v === 'LOCALTONETV4') {
                    $('.show-on-localtonet-family').show(500);
                    $('.show-on-localtonet-only').hide();
                    $('.show-on-localtonet-v4-only').show();
                } else if (v === 'THREEPROXY') {
                    $('.show-on-threeproxy').show(500);
                } else if (v === 'LOCALTONET_ROTATING') {
                    $('.show-on-localtonet-rotating').show(500);
                } else {
                    $('.show-on-localtonet-family').show(500);
                    $('.show-on-localtonet-only').show();
                    $('.show-on-localtonet-v4-only').hide();
                }
            }

            $(document).on('change', '[name="product[delivery_type]"]', npApplyProductDeliveryTypeUi);
            npApplyProductDeliveryTypeUi();

            /* v4 entry add/remove kaldırıldı — artık IP havuzu dropdown kullanılıyor */
            const isCreate = "{{!isset($product)}}";

            $(document).on("change", "[name='product[status]']", function () {
                let icon = $("#np_add_product_status");
                console.log("aa")
                if (icon.hasClass("bg-success")) {
                    icon.removeClass("bg-success").addClass("bg-danger")
                } else {
                    icon.removeClass("bg-danger").addClass("bg-success")
                }
            })

            $(document).on('blur', '[data-np-price="price"]', function () {
                if ($(this).val() && (/\d/.test($(this).val()))) {
                    $(this).val(priceFormat.to(priceFormat.from($(this).val())))
                } else {
                    $(this).val("")
                }
            })

            $(document).on("click", "[data-np-price='add-item']", function () {
                let table = $("[data-np-price='items']");
                table.append($("[data-np-price='item-template'] tbody").html())

                table.find("tbody tr:last select").select2();
            })

            $(document).on("click", "[data-np-price='remove-item']", function () {
                let item = $(this);
                item.closest("tr").remove();
            })

            $(document).on("submit", "#primaryForm", function (e) {
                e.preventDefault()
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
                        propSubmitButton(form.find("[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton(form.find("[type='submit']"), 0);
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => {
                                if (isCreate) {
                                    window.location.href = res.redirectUrl;
                                } else {
                                    window.location.reload();
                                }
                            })
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                    }
                })
            })


            /* <!-- START::Additional Services -!> */
            $(document).on("change", "[data-np-additional-services='container'] [name='service_type[protocol_select][status]']", function () {
                let element = $(this),
                    area = $("[data-np-additional-services='protocol-area']");
                if(element.is(":checked")){
                    area.show(300)
                }else{
                    area.hide(300)
                }
            })
            $(document).on("change", "[data-np-additional-services='container'] [name='service_type[quota][status]']", function () {
                let element = $(this),
                    area = $("[data-np-additional-services='quota-area']");
                if(element.is(":checked")){
                    area.show(300)
                }else{
                    area.hide(300)
                }
            })
            $(document).on("change", "[data-np-additional-services='container'] [name='service_type[quota_duration][status]']", function () {
                let element = $(this),
                    area = $("[data-np-additional-services='quota-duration-area']");
                if(element.is(":checked")){
                    area.show(300)
                }else{
                    area.hide(300)
                }
            })

            $("[data-np-additional-services='container'] [name='service_type[protocol_select][status]']").trigger("change")
            $("[data-np-additional-services='container'] [name='service_type[quota][status]']").trigger("change")
            $("[data-np-additional-services='container'] [name='service_type[quota_duration][status]']").trigger("change")

            $(document).on("click", "[data-np-quota='add-item']", function () {
                let items = $("[data-np-quota='items']");
                items.append($("[data-np-quota='item-template']").html())
            })

            $(document).on("click", "[data-np-quota='remove-item']", function () {
                let item = $(this);
                item.closest("[data-np-quota='item']").remove();
            })

            $(document).on("click", "[data-np-quota-duration='add-item']", function () {
                let items = $("[data-np-quota-duration='items']");
                items.append($("[data-np-quota-duration='item-template']").html())

                $("[data-np-quota-duration='items'] [data-np-quota-duration='item']:last select").select2();
            })

            $(document).on("click", "[data-np-quota-duration='remove-item']", function () {
                let item = $(this);
                item.closest("[data-np-quota-duration='item']").remove();
            })

            $(document).on("click", "[data-np-tp-duration='add-item']", function () {
                let items = $("[data-np-tp-duration='items']");
                items.append($("[data-np-tp-duration='item-template']").html());
                $("[data-np-tp-duration='items'] [data-np-tp-duration='item']:last select").select2();
            })

            $(document).on("click", "[data-np-tp-duration='remove-item']", function () {
                $(this).closest("[data-np-tp-duration='item']").remove();
            })
            /* <!-- END::Additional Services -!> */

        })
    </script>

    @if(!isset($product))
        <script>
            $(document).ready(function () {
                $('[name="product[delivery_type]"]:first').closest("label").trigger("click")
                console.log($('[name="product[delivery_type]"]:first').closest("label"), "asdsad")
            })
        </script>
    @endif
@endsection
