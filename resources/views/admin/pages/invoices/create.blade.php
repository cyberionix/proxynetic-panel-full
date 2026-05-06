@extends("admin.template")
@section("title", __("create_invoice"))
@section("css")
    <style>
        .address {
            white-space: pre-line;
        }
    </style>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="[__('create_invoice'), __('invoices') => route('admin.invoices.index')]"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Form-->
                <form id="invoiceForm" class="d-flex flex-column flex-lg-row">
                @csrf
                <!--begin::Content-->
                    <div class="flex-lg-row-fluid mb-10 mb-lg-0 me-lg-7 me-xl-10">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card body-->
                            <div class="card-body p-12">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-column align-items-start flex-xxl-row">
                                    <!--begin::Input group-->
                                    <div class="d-flex align-items-center flex-equal fw-row me-4 order-2"
                                         data-bs-toggle="tooltip" data-bs-trigger="hover"
                                         title="{{__("specify_invoice_date")}}">
                                        <!--begin::Date-Input-->
                                        <x-admin.form-elements.date-input name="invoice_date"
                                                                          :placeholder="__('custom_select', ['name' => __('date')])"
                                                                          :value="date('d-m-Y')"
                                                                          required="required"/>

                                        <!--end::Date-Input-->
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div
                                        class="d-flex flex-center flex-equal fw-row text-nowrap order-1 order-xxl-2 me-4"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        title="{{__("enter_invoice_number")}}">
                                        <span class="fs-2x fw-bold text-gray-800">#</span>
                                        <input type="text" name="invoice_number"
                                               class="form-control form-control-flush fw-bold text-muted fs-3 w-125px"
                                               value="{{$invoiceNumber}}" placeholder="..." required/>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div
                                        class="d-flex align-items-center justify-content-end flex-equal order-3 fw-row"
                                        data-bs-toggle="tooltip" data-bs-trigger="hover"
                                        title="Specify invoice due date"></div>
                                    <!--end::Input group-->
                                </div>
                                <!--end::Top-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-10"></div>
                                <!--end::Separator-->
                                <!--begin::Wrapper-->
                                <div class="mb-0">
                                    <!--begin::Row-->
                                    <div class="row gx-10 mb-9">
                                        <!--begin::Col-->
                                        <div class="col-lg-6">

                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-lg-6">
                                            <x-admin.form-elements.user-select name="user_id"
                                                                               required="required"
                                                                               :selectedOption="$selected_user ?: []"
                                                                               :placeholder="__(':name_selection', ['name' => __('Müşteri')])"/>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                    <!--begin::Row-->
                                    <div class="row gx-10 mb-9">
                                        <!--begin::Col-->
                                        <div class="col-lg-6">
                                            <!--end::Text-->
                                            <div class="fw-bold fs-6 text-gray-800">
                                                SAĞLAM PROXY YAZILIM LİMİTED ŞİRKETİ
                                            </div>
                                            <!--end::Text-->
                                            <!--end::Description-->
                                            <div class="fw-semibold fs-7 text-gray-600">
                                                YAKUPLU MAH. HÜRRİYET BLV. SKYPORT Skyport Residence NO: 1 İÇ KAPI NO:
                                                62
                                                <br>
                                                BEYLİKDÜZÜ / İSTANBUL
                                                <br>
                                                7381261591 - BEYLİKDÜZÜ V.D.
                                                <br>
                                                0530 132 02 95 - info@saglamproxy.com
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-lg-6 text-end customerInformation d-none">
                                            <h5 class="name mb-0"></h5>
                                            <div class="fw-bold text-muted mb-2 student"></div>
                                            <div class="text-end mb-2">
                                                <span
                                                    class="badge badge-success cursor-pointer editAddressBtn">{{__("edit_:name", ["name" => __("address")])}}</span>
                                            </div>
                                            <div class="mb-4 invoiceAddressArea">
                                                <span class="d-none invoice_type"></span>
                                                <span class="address"></span>
                                                <div>
                                                    <span class="district"></span> <span class="city"></span>
                                                </div>
                                                <div>
                                                    <span class="country"></span>
                                                </div>
                                                <div>
                                                    <span class="tax_number"></span> <span class="tax_office"></span>
                                                </div>
                                                <div>
                                                    <span class="company_name"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                    <!--begin::Table wrapper-->
                                    <div class="table-responsive mb-10">
                                        <!--begin::Table-->
                                        <table class="table g-3 gs-0 mb-0 fw-bold text-gray-700"
                                               data-kt-element="items">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr class="border-bottom fs-6 fw-bold text-gray-700">
                                                <th class="w-300px">{{__("product")}}/{{__("service")}}</th>
                                                <th class="w-300px d-none">{{__("Periyot")}}</th>
                                                <th class="w-125px d-none">{{__("quantity")}}</th>
                                                <th class="w-125px">{{__("price")}}</th>
                                                <th class="w-100px">{{__("vat")}}</th>
                                                <th class="w-300px">{{__("amount")}}</th>
                                                <th class="w-50px"></th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr class="border-bottom border-bottom-dashed" data-kt-element="item">
                                                <td>
                                                    <div class="w-300px d-none">
                                                        <select name="product[id][]"
                                                                class="form-control form-control-solid productSelect">
                                                            <option value=""></option>
                                                        </select>
                                                    </div>
                                                    <div class="w-300px ">
                                                        <input name="product[name][]" placeholder="Ürün ismi"
                                                                class="form-control form-control-solid"/>


                                                    </div>
                                                </td>
                                                <td class="d-none">
                                                    <div class="mw-300px">
                                                        <select data-kt-element="period" name="product[price_id][]"
                                                                id=""
                                                                class="form-control form-control-solid"></select>
                                                    </div>
                                                </td>
                                                <td class="d-none">
                                                    <div class="w-75px">
                                                        <input class="form-control form-control-solid" type="number"
                                                               min="1" name="product[quantity][]" placeholder="1"
                                                               value="1" data-kt-element="quantity"/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="w-125px">
                                                        <div class="fs-6 ">
                                                            {{defaultCurrencySymbol()}} <span
                                                                data-kt-element="price">0,00</span>
                                                        </div>
                                                        <input
                                                            class="form-control form-control-solid text-end d-none"

                                                            name="product[price][]" placeholder="0,00"
                                                            type="text">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="w-75px">
                                                        <select name="product[vat_percent][]"
                                                                class="form-select form-select-solid"
                                                                data-kt-element="vat_percent">
                                                            @foreach(getVats() as $vat)
                                                                <option
                                                                    value="{{$vat}}" {{$vat == 20 ? "selected" : ""}}>{{$vat}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>

                                                    <div class="">
                                                        <div class="fs-6 d-none">
                                                            {{defaultCurrencySymbol()}} <span
                                                            >0,00</span>
                                                        </div>
                                                        <input class="form-control form-control-solid "
                                                               data-kt-element="total"
                                                               name="product[amount][]" placeholder="0,00"
                                                               type="text">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="mt-1">
                                                        <button type="button"
                                                                class="btn btn-sm btn-icon btn-active-color-primary"
                                                                data-kt-element="remove-item">
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
                                            </tbody>
                                            <!--end::Table body-->
                                            <!--begin::Table foot-->
                                            <tfoot>
                                            <tr class="border-top border-top-dashed align-top fs-6 fw-bold text-gray-700">
                                                <th>
                                                    <button type="button" class="btn btn-light-primary me-3"
                                                            data-kt-element="add-item">
														<span class="svg-icon svg-icon-2">
															<i class="fa fa-plus"></i>
														</span>
                                                        Ekle
                                                    </button>
                                                </th>
                                                <th colspan="2" class="border-bottom border-bottom-dashed ps-0">
                                                    <div class="d-flex flex-column align-items-start">
                                                        <div class="fs-6">{{__("subtotal")}}</div>
                                                        <div class="fs-6">{{__("total_vat")}}</div>
                                                    </div>
                                                </th>
                                                <th colspan="2" class="border-bottom border-bottom-dashed">
                                                    <div class="d-flex flex-column align-items-end">
                                                        <div>
                                                            {{defaultCurrencySymbol()}}<span
                                                                data-kt-element="sub-total">0.00</span>
                                                        </div>
                                                        <div>
                                                            {{defaultCurrencySymbol()}}<span
                                                                data-kt-element="vat-total">0.00</span>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr class="align-top fw-bold text-gray-700">
                                                <th></th>
                                                <th colspan="2" class="fs-4 ps-0">{{__("total")}}</th>
                                                <th colspan="2" class="text-end fs-4 text-nowrap">
                                                    {{defaultCurrencySymbol()}}<span
                                                        data-kt-element="grand-total">0.00</span>
                                                </th>
                                            </tr>
                                            </tfoot>
                                            <!--end::Table foot-->
                                        </table>
                                    </div>
                                    <!--end::Table-->
                                    <!--begin::Item template-->
                                    <table class="table d-none" data-kt-element="item-template">
                                        <tr class="border-bottom border-bottom-dashed" data-kt-element="item">
                                            <td>
                                                <div class="w-300px d-none">
                                                    <select name="product[id][]"
                                                            class="form-control form-control-solid productSelect">
                                                        <option value=""></option>
                                                    </select>
                                                </div>
                                                <div class="w-300px ">
                                                    <input name="product[name][]" placeholder="Ürün ismi"
                                                           class="form-control form-control-solid"/>


                                                </div>
                                            </td>
                                            <td class="d-none">
                                                <div class="mw-300px">
                                                    <select data-kt-element="period" name="product[price_id][]"
                                                            id=""
                                                            class="form-control form-control-solid"></select>
                                                </div>
                                            </td>
                                            <td class="d-none">
                                                <div class="w-75px">
                                                    <input class="form-control form-control-solid" type="number"
                                                           min="1" name="product[quantity][]" placeholder="1"
                                                           value="1" data-kt-element="quantity"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="w-125px">
                                                    <div class="fs-6 ">
                                                        {{defaultCurrencySymbol()}} <span
                                                            data-kt-element="price">0,00</span>
                                                    </div>
                                                    <input
                                                        class="form-control form-control-solid text-end d-none"

                                                        name="product[price][]" placeholder="0,00"
                                                        type="text">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="w-75px">
                                                    <select name="product[vat_percent][]"
                                                            class="form-select form-select-solid"
                                                            data-kt-element="vat_percent">
                                                        @foreach(getVats() as $vat)
                                                            <option
                                                                value="{{$vat}}" {{$vat == 20 ? "selected" : ""}}>{{$vat}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td>

                                                <div class="">
                                                    <div class="fs-6 d-none">
                                                        {{defaultCurrencySymbol()}} <span
                                                        >0,00</span>
                                                    </div>
                                                    <input class="form-control form-control-solid "
                                                           data-kt-element="total"
                                                           name="product[amount][]" placeholder="0,00"
                                                           type="text">
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-icon btn-active-color-primary"
                                                        data-kt-element="remove-item">
                                                    <i class="ki-duotone ki-trash fs-3">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                    </i>
                                                </button>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="table d-none" data-kt-element="empty-template">
                                        <tr data-kt-element="empty">
                                            <th colspan="5" class="text-muted text-center py-10">{{__("no_items")}}</th>
                                        </tr>
                                    </table>
                                    <!--end::Item template-->
                                    <div class="mb-0">
                                        <button type="submit" class="btn btn-primary w-100" id="form_submit_btn">
                                        <span class="indicator-label">
                                            <span class="d-flex flex-center gap-2">
                                                <i class="ki-duotone ki-triangle fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i> {{__("create")}}
                                            </span>
                                        </span>
                                            <span class="indicator-progress">
                                            {{__("please_wait")}}... <span
                                                    class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                        </button>
                                    </div>
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Content-->
                    <!--begin::Sidebar-->

                    <!--end::Sidebar-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    <!--begin::Modals-->
    <div class="modal fade" id="editAddressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-700px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>{{__("edit_:name", ["name" => __("address")])}}</h2>
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
                    <form id="editAddressForm">
                        @csrf
                        <div
                            class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                            <!--begin::Icon-->
                            <i class="ki-duotone ki-information fs-2tx text-primary me-4"><span
                                    class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <!--end::Icon-->
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack flex-grow-1 ">
                                <!--begin::Content-->
                                <div class=" fw-semibold">
                                    <div class="fs-6 text-gray-700 ">Düzenlemeler bu fatura üzerindedir. (Kullanıcının
                                        kayıtlı adres bilgileri güncellenmez)
                                    </div>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <div class="row g-3">
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("city")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-admin.form-elements.city-select name="city_id"
                                                                   dropdownParent="#editAddressModal"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("district")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-admin.form-elements.district-select name="district_id"
                                                                       dropdownParent="#editAddressModal"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-12">
                                <!--begin::Label-->
                                <label class="form-label">{{__("invoice_address")}}</label>
                                <!--end::Label-->
                                <div class="mb-3 d-none">
                                    <!--begin::Label-->
                                    <label class="required form-label">{{__("invoice_address")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Select-->
                                    <x-admin.form-elements.select name='invoice_address_id'
                                                                  customClass="userAddressSelect"
                                                                  dropdownParent="#editAddressModal"/>
                                    <!--end::Select-->
                                </div>
                                <div>
                                    <textarea class="form-control form-control-solid" name="invoice_address"
                                              rows="3"></textarea>
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <!--begin::Label-->
                                <label
                                    class="form-label required">{{__("invoice_type")}}</label>
                                <!--end::Label-->
                                <!--begin::Radio group-->
                                <div class="btn-group w-100" data-kt-buttons="true"
                                     data-kt-buttons-target="[data-kt-button]">
                                    <!--begin::Radio-->
                                    <label
                                        class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea active"
                                        data-kt-button="true">
                                        <!--begin::Input-->
                                        <input class="btn-check" type="radio"
                                               name="invoice_type" checked
                                               value="INDIVIDUAL"/>
                                        <!--end::Input-->
                                        {{__("individual")}}
                                    </label>
                                    <!--end::Radio-->
                                    <!--begin::Radio-->
                                    <label
                                        class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea"
                                        data-kt-button="true">
                                        <!--begin::Input-->
                                        <input class="btn-check" type="radio"
                                               name="invoice_type"
                                               value="CORPORATE"/>
                                        <!--end::Input-->
                                        {{__("corporate")}}
                                    </label>
                                    <!--end::Radio-->
                                </div>
                                <!--end::Radio group-->
                            </div>
                            <div class="col-xl-6 corporate-area" style="display: none;">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("tax_number")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="tax_number"
                                       class="form-control form-control form-control-solid">
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 corporate-area" style="display: none;">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("tax_office")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="tax_office"
                                       class="form-control form-control form-control-solid">
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 corporate-area" style="display: none;">
                                <!--begin::Label-->
                                <label
                                    class="form-label required">{{__("company_name")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="company_name"
                                       class="form-control form-control form-control-solid">
                                <!--end::Select-->
                            </div>
                        </div>
                        <!--begin::Actions-->
                        <div class="d-flex flex-center flex-row-fluid pt-12">
                            <button type="button" class="btn btn-light me-3"
                                    data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="add_user_submit_btn">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{__("edit")}}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modals-->
@endsection
@section("js")
    <script src="{{assetAdmin("/js/custom/apps/invoices/createi.js")}}"></script>
    <script>
        $(document).ready(function () {

            @if($selected_user)
            setTimeout(function(){
                $('select.userSelect').trigger('change')
            },500);
                @endif

            $(document).on('change', '[data-kt-element="period"]', function (e) {
                console.log('period changed');
                let drawPrice = $(this).find('option:selected').attr('data-draw-price');
                let area = $(this).closest("tr")

                area.find("[name='product[amount][]']").val(drawPrice).trigger("change")


            })
            let userDefInvoiceAddress;
            let selectProductInstance = function () {
                $("select.productSelect").each(function (index, item) {
                    if (!(($(item).attr('data-select2-id') && $(item).attr('data-select2-id').length > 0) || $(item).closest('table').hasClass('d-none'))) {
                        $(item).select2({
                            tags: false,
                            language: {
                                searching: function () {
                                    return "{{__("searching")}}...";
                                },
                                inputTooShort: function () {
                                    return "{{__("custom_field_is_min_size", ["name" => __("search"), "size" => 3])}}";
                                },
                                "noResults": function () {
                                    return "{{__("result_not_found")}}";
                                }
                            },
                            placeholder: "{{__(':name_selection', ['name' => __('product') . '/' . __('service')])}}",
                            ajax: {
                                url: '{{route("admin.invoices.productSearch")}}',
                                dataType: 'json',
                                type: "GET",
                                quietMillis: 50,
                                data: function (term) {
                                    return {
                                        term: term
                                    };
                                },
                                processResults: function (data) {
                                    var res = data.items.map(function (item) {
                                        return {
                                            id: item.id,
                                            text: item.name
                                        };
                                    });
                                    return {
                                        results: res
                                    };
                                }
                            }
                        }).trigger("select2")
                    }
                })
            }
            selectProductInstance();
            $(document).on('click', '[data-kt-element="add-item"]', function () {
                selectProductInstance();
            });
            $(document).on('change', 'select.productSelect', function () {
                let area = $(this).closest("tr"),
                    id = $(this).find(":selected").val(),
                    url = `{{ route('admin.invoices.productFind', ['id' => '__id_placeholder__']) }}`;
                url = url.replace('__id_placeholder__', id);

                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            area.find("[name='product[price][]']").val(res.data.price).trigger("change")
                            area.find("[name='product[vat_percent][]']").val(res.data.vat_percent).trigger("change")
                            area.find("[name='product[price_id][]']").html('');
                            $.each(res.data.prices, function (index, item) {
                                area.find("[name='product[price_id][]']").append('<option data-draw-price="'+item.draw_price+'" data-price="' + item.price + '" value="' + item.id + '">' + item.name + '</option>');
                            })
                            area.find("[name='product[price_id][]']").find('option:first').prop('selected', true);
                            area.find("[name='product[price_id][]']").trigger('change');
                            // area.find("[name='product[amount][]']").val(res.data.prices[0].price).trigger("change")

                        } else {
                            toastr.error("{{__("an_error_occurred")}}");
                        }
                    }
                })
            });
            $(document).on('change', 'select.userSelect', function () {
                let area = $(".customerInformation"),
                    userId = $(this).find(":selected").val(),
                    url = `{{ route('admin.users.find', ['user' => '__user_placeholder__']) }}`,
                    invoiceAddressArea = area.find(".invoiceAddressArea");
                url = url.replace('__user_placeholder__', userId);

                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            area.find(".name").text(`${res.data.first_name} ${res.data.last_name}`)
                            area.find(".student").text(`(${res.data?.student?.name ?? ""} ${res.data?.student?.age ?? ""})`)

                            userDefInvoiceAddress = {
                                "id": res.data?.address?.id ?? "",
                                "invoice_type": res.data.address?.invoice_type ?? "",
                                "address": res.data.address?.address ?? "",
                                "country": {
                                    "id": res.data.address?.country?.id ?? "",
                                    "title": res.data.address?.country?.title ?? ""
                                },
                                "city": {
                                    "id": res.data.address?.city?.id ?? "",
                                    "title": res.data.address?.city?.title ?? ""
                                },
                                "district": {
                                    "id": res.data.address?.district?.id ?? "",
                                    "title": res.data.address?.district?.title ?? ""
                                },
                                "tax_number": res.data.address?.tax_number ?? "",
                                "tax_office": res.data.address?.tax_office ?? "",
                                "company_name": res.data.address?.company_name ?? "",
                            }; //for edit address

                            invoiceAddressArea.find(".invoice_type, .address, .country, .city, .district, .tax_number, .tax_office, .company_name").text("");
                            if (res.data.address) {
                                invoiceAddressArea.find(".invoice_type").text(res.data.address?.invoice_type);
                                invoiceAddressArea.find(".address").html(res.data.address?.address);
                                invoiceAddressArea.find(".country").text(res.data.address?.country?.title);
                                invoiceAddressArea.find(".city").text(res.data.address?.city?.title);
                                invoiceAddressArea.find(".district").text(res.data.address?.district?.title);
                                invoiceAddressArea.find(".tax_number").text(res.data.address?.tax_number);
                                invoiceAddressArea.find(".tax_office").text(res.data.address?.tax_office);
                                invoiceAddressArea.find(".company_name").text(res.data.address?.company_name);
                            }
                            area.removeClass("d-none")
                        } else {
                            toastr.error("{{__("an_error_occurred")}}");
                        }
                    }
                })
            });
            $(document).on('click', '.editAddressBtn', function () {
                let form = $("#editAddressForm");
                form.find("[name='invoice_address']").html(userDefInvoiceAddress.address);
                form.find("[name='city_id']").val(userDefInvoiceAddress.city.id).trigger("change");
                form.find("[name='district_id']").append(`<option value="${userDefInvoiceAddress.district.id}" selected="selected">${userDefInvoiceAddress.district.title}</option>`).trigger("change");
                form.find(`[name='invoice_type'][value='${userDefInvoiceAddress.invoice_type}']`).prop("checked", true).trigger("change");
                form.find(`[name='invoice_type'][value='${userDefInvoiceAddress.invoice_type}']`).closest("label").trigger("click")
                form.find("[name='tax_number']").val(userDefInvoiceAddress.tax_number);
                form.find("[name='tax_office']").val(userDefInvoiceAddress.tax_office);
                form.find("[name='company_name']").val(userDefInvoiceAddress.company_name);

                $("#editAddressModal").modal("show");

                /* for address select2
                let userAddressSelectUrl = `{{route('admin.users.addresses.search', ['user' => '__user_placeholder__'])}}`,
                    user_id = $("#invoiceForm [name='user_id']").val();
                userAddressSelectUrl = userAddressSelectUrl.replace('__user_placeholder__', user_id);
                $("select.userAddressSelect").select2({
                    tags: false,
                    language: {
                        searching: function () {
                            return "{{__("searching")}}...";
                        },
                        inputTooShort: function () {
                            return "{{__("custom_field_is_min_size", ["name" => __("search"), "size" => 3])}}";
                        },
                        "noResults": function () {
                            return "{{__("result_not_found")}}";
                        }
                    },
                    placeholder: "{{__("Müşteri Seçimi")}}",
                    ajax: {
                        url: userAddressSelectUrl,
                        dataType: 'json',
                        type: "GET",
                        quietMillis: 50,
                        data: function (term) {
                            return {
                                term: term
                            };
                        },
                        processResults: function (data) {
                            var res = data.items.map(function (item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            });
                            return {
                                results: res
                            };
                        }
                    }
                }).trigger("select2");
                form.find("[name='invoice_address']").append(`<option value='${userDefInvoiceAddress.id}' selected>${userDefInvoiceAddress.label}</option>`).trigger("change");
                */
            });
            $(document).on("change", "#editAddressForm [name='invoice_type']", function () {
                let form = $("#editAddressForm");
                if ($(this).val() == "INDIVIDUAL") {
                    form.find(".corporate-area").hide();
                } else {
                    form.find(".corporate-area").fadeIn();
                }
            })
            $(document).on('submit', '#editAddressForm', function (e) {
                e.preventDefault();
                let form = $("#editAddressForm"),
                    area = $(".customerInformation"),
                    invoiceAddressArea = area.find(".invoiceAddressArea");
                userDefInvoiceAddress.invoice_type = form.find(".invoiceTypeArea.active input").val()
                userDefInvoiceAddress.address = form.find("[name='invoice_address']").val()
                userDefInvoiceAddress.city.id = form.find("[name='city_id']").val()
                userDefInvoiceAddress.city.title = form.find("[name='city_id'] option:selected").text()
                userDefInvoiceAddress.district.id = form.find("[name='district_id']").val()
                userDefInvoiceAddress.district.title = form.find("[name='district_id'] option:selected").text()
                userDefInvoiceAddress.tax_number = form.find("[name='tax_number']").val()
                userDefInvoiceAddress.tax_office = form.find("[name='tax_office']").val()
                userDefInvoiceAddress.company_name = form.find("[name='company_name']").val()

                invoiceAddressArea.find(".invoice_type").text(userDefInvoiceAddress.invoice_type);
                invoiceAddressArea.find(".address").html(userDefInvoiceAddress.address);
                invoiceAddressArea.find(".city").text(userDefInvoiceAddress.city.title);
                invoiceAddressArea.find(".district").text(userDefInvoiceAddress.district.title);
                invoiceAddressArea.find(".tax_number").text(userDefInvoiceAddress.invoice_type === "CORPORATE" ? userDefInvoiceAddress.tax_number : "");
                invoiceAddressArea.find(".tax_office").text(userDefInvoiceAddress.invoice_type === "CORPORATE" ? userDefInvoiceAddress.tax_office : "");
                invoiceAddressArea.find(".company_name").text(userDefInvoiceAddress.invoice_type === "CORPORATE" ? userDefInvoiceAddress.company_name : "");
                $("#editAddressModal").modal("hide");
            });

            $(document).on('submit', '#invoiceForm', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.append("invoice_address[invoice_type]", userDefInvoiceAddress.invoice_type)
                formData.append("invoice_address[address]", userDefInvoiceAddress.address)
                formData.append("invoice_address[district][id]", userDefInvoiceAddress.district.id)
                formData.append("invoice_address[district][title]", userDefInvoiceAddress.district.title)
                formData.append("invoice_address[city][id]", userDefInvoiceAddress.city.id)
                formData.append("invoice_address[city][title]", userDefInvoiceAddress.city.title)
                formData.append("invoice_address[country][id]", userDefInvoiceAddress.country.id)
                formData.append("invoice_address[country][title]", userDefInvoiceAddress.country.title)
                formData.append("invoice_address[tax_number]", userDefInvoiceAddress.tax_number)
                formData.append("invoice_address[tax_office]", userDefInvoiceAddress.tax_office)
                formData.append("invoice_address[company_name]", userDefInvoiceAddress.company_name)
                $.ajax({
                    type: 'POST',
                    url: "{{route("admin.invoices.store")}}",
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton($("#form_submit_btn"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton($("#form_submit_btn"), 0);
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res.message,
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
                    }
                })

            });
        })
    </script>
@endsection
