@extends("admin.template")
@section("title", __("invoice_detail"))
@section("css")
    <style>
        .address {
            white-space: pre-line;
        }
    </style>
@endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('invoice_detail') . ' #' . $invoice->invoice_number"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card body-->
        <div class="card-body p-12">
            <!--begin:::Tab content-->
            <div class="tab-content" id="myTabContent">
                <!--begin:::Tab pane-->
                <div class="tab-pane fade show active" id="invoice_info_tab" role="tabpanel">
                    <form id="invoiceForm"
                          data-url="{{route("admin.invoices.update", ["invoice" => $invoice->id])}}">
                    @csrf
                    <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start flex-xxl-row">
                            <!--begin::Input group-->
                            <div class="d-flex align-items-center flex-equal fw-row me-4 order-2"
                                 data-bs-toggle="tooltip" data-bs-trigger="hover"
                                 title="{{__("specify_invoice_date")}}">
                                <!--begin::Date-Input-->
                                <x-admin.form-elements.date-input name="invoice_date"
                                                                  :placeholder="__('custom_select', ['name' => __('date')])"
                                                                  :value="$invoice->invoice_date"
                                                                  required="required"/>

                                <!--end::Date-Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div
                                class="d-flex flex-center flex-equal fw-row text-nowrap order-1 order-xxl-2 me-4"
                                data-bs-toggle="tooltip" data-bs-trigger="hover"
                                title="{{__("invoice_number")}}">
                                <span class="fs-2x fw-bold text-gray-800">#</span>
                                <input type="text" name="invoice_number"
                                       class="form-control form-control-flush fw-bold text-muted fs-3 w-125px"
                                       value="{{$invoice->invoice_number}}"
                                       readonly/>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div
                                class="d-flex align-items-center justify-content-end flex-equal order-3 fw-row gap-2 flex-wrap">
                                @if($invoice->status == 'PAID')
                                    <button type="button" class="btn btn-sm btn-success pe-none text-nowrap">
                                        <i class="fa fa-check-circle text-white me-1"></i>Ödendi
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-light-warning text-nowrap togglePaymentStatusBtn"
                                            data-url="{{route("admin.invoices.togglePaymentStatus", ["invoice" => $invoice->id])}}"
                                            data-status="PENDING">
                                        <i class="fa fa-times-circle me-1"></i>Ödenmedi Yap
                                    </button>
                                @elseif($invoice->status == 'CANCELLED')
                                    <button type="button" class="btn btn-sm btn-secondary pe-none text-nowrap">
                                        <i class="fa fa-ban text-white me-1"></i>İptal Edildi
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-light-success text-nowrap togglePaymentStatusBtn"
                                            data-url="{{route("admin.invoices.togglePaymentStatus", ["invoice" => $invoice->id])}}"
                                            data-status="PAID">
                                        <i class="fa fa-check-circle me-1"></i>Ödendi Yap
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-danger pe-none text-nowrap">
                                        <i class="fa fa-clock text-white me-1"></i>Ödenmedi
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-light-success text-nowrap togglePaymentStatusBtn"
                                            data-url="{{route("admin.invoices.togglePaymentStatus", ["invoice" => $invoice->id])}}"
                                            data-status="PAID">
                                        <i class="fa fa-check-circle me-1"></i>Ödendi Yap
                                    </button>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-light-danger text-nowrap invoiceDeleteBtn"
                                        data-url="{{route("admin.invoices.delete", ["invoice" => $invoice->id])}}">
                                    <i class="fa fa-trash me-1"></i>Sil
                                </button>
                                @if(!$invoice->formalized_at)
                                    <button type="button"
                                            class="btn btn-sm btn-light-success text-nowrap sendToParachuteBtn"
                                            data-url="{{route("admin.invoices.formalize", ["invoice" => $invoice->id])}}">
                                        <i class="fa fa-paper-plane me-1"></i>Resmileştir
                                    </button>
                                @else
                                    <a class="btn btn-sm btn-light-success text-nowrap"
                                       href="{{route("admin.invoices.showPdf", ["invoice" => $invoice->id])}}">
                                        <i class="fa fa-file-pdf me-1"></i>PDF
                                    </a>
                                @endif
                                @if($invoice->share_token)
                                <a href="{{route("public.invoice.show", ["token" => $invoice->share_token])}}"
                                   target="_blank"
                                   class="btn btn-sm btn-light-primary text-nowrap">
                                    <i class="fa fa-external-link-alt me-1"></i>Faturaya Git
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-light-info text-nowrap npShareLinkBtn"
                                        data-link="{{route("public.invoice.show", ["token" => $invoice->share_token])}}"
                                        title="Linki Kopyala">
                                    <i class="fa fa-copy me-1"></i>Paylaş
                                </button>
                                @endif
                            </div>
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
                                    <x-invoice-address-area/>
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-lg-6 text-end customerInformation">
                                    <div class="mb-1">
                                        <h6><span class="name">{{$invoice->user?->fullName}}</span>
                                        </h6>
                                    </div>
                                    <div class="text-end mb-2">
                                                            <span
                                                                class="badge badge-success cursor-pointer editAddressBtn">{{__("edit_:name", ["name" => __("address")])}}</span>
                                    </div>
                                    <div class="mb-4 invoiceAddressArea">
                                                            <span
                                                                class="d-none invoice_type">{{@$invoice->invoice_address["invoice_type"]}}</span>
                                        <span
                                            class="address">{!! @$invoice->invoice_address["address"] !!}</span>
                                        <div>
                                                                <span
                                                                    class="district">{{@$invoice->invoice_address["district"]["title"]}}</span>
                                            <span
                                                class="city">{{@$invoice->invoice_address["city"]["title"]}}</span>
                                        </div>
                                        <div>
                                                                <span
                                                                    class="country">{{@$invoice->invoice_address["country"]["title"]}}</span>
                                        </div>
                                        <div
                                            class="{{@$invoice->invoice_address["invoice_type"] == "CORPORATE" ? "d-none" : ""}}">
                                                                <span
                                                                    class="identity_number">{{@$invoice->invoice_address["tax_number"]}}</span>
                                        </div>
                                        <div
                                            class="{{@$invoice->invoice_address["invoice_type"] == "INDIVIDUAL" ? "d-none" : ""}}">
                                                                <span
                                                                    class="tax_number">{{@$invoice->invoice_address["tax_number"]}}</span>
                                            <span
                                                class="tax_office">{{@$invoice->invoice_address["tax_office"]}}</span>
                                        </div>
                                        <div
                                            class="{{@$invoice->invoice_address["invoice_type"] == "INDIVIDUAL" ? "d-none" : ""}}">
                                                                <span
                                                                    class="company_name">{{@$invoice->invoice_address["company_name"]}}</span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Table wrapper-->
                            <div class="table-responsive mb-10">
                                <!--begin::Table-->
                                <table class="table g-3 gs-0 mb-0 fw-bold text-gray-700" id="invoiceItemTable"
                                       data-kt-element="items">
                                    <!--begin::Table head-->
                                    <thead>
                                    <tr class="border-bottom fs-6 fw-bold text-gray-700">
                                        <th class="w-300px">{{__("product")}}/{{__("service")}}</th>
                                        <th class="w-125px">{{__("price")}}</th>
                                        <th class="w-100px">{{__("vat")}}</th>
                                        <th class="w-300px">{{__("amount")}}</th>
                                        <th class="w-50px"></th>
                                    </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                    @foreach($invoice->items as $item)
                                        <tr class="border-bottom border-bottom-dashed"
                                            data-kt-element="item">
                                            <td>
                                                <div class="mt-3 fs-6 d-flex align-items-center gap-2">
                                                    {{$item->name}}
                                                    @if($item->order_id)
                                                        - <a target="_blank"
                                                             href="{{route("admin.orders.show", ["order" => $item->order_id])}}">#{{$item->order_id}}</a>
                                                    @endif
                                                    <span
                                                        class="badge badge-primary badge-sm">{{__("invoice_item_types.".mb_strtolower($item->type))}}</span>
                                                </div>
                                                <input type="hidden" name="invoice_item[id][]"
                                                       value="{{$item->id}}">
                                            </td>
                                            <td>
                                                <div class="w-125px">
                                                    <input
                                                        class="form-control  text-end priceInput"
                                                        data-kt-element="price"
                                                        value="{{showBalance($item->total_price)}}"
                                                        name="invoice_item[price][]" placeholder="0,00"
                                                        type="text">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="w-75px">
                                                    <select name="invoice_item[vat_percent][]"
                                                            class="form-select "
                                                            data-kt-element="vat_percent">
                                                        @foreach(getVats() as $vat)
                                                            <option
                                                                value="{{$vat}}" {{$vat == $item->vat_percent ? "selected" : ""}}>{{$vat}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group mb-5">
                                                    <span class="input-group-text">{{defaultCurrencySymbol()}}</span>
                                                    <input
                                                        class="form-control priceInput"
                                                        data-kt-element="total"
                                                        value="{{showBalance($item->total_price_with_vat)}}"
                                                        name="invoice_item[amount][]" placeholder="0,00"
                                                        type="text">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mt-1">
                                                    <button type="button"
                                                            class="btn btn-sm btn-icon btn-active-color-primary d-none"
                                                            data-kt-element="remove-item">
                                                        <i class="ki-duotone ki-trash fs-3 ">
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
                                    </tbody>
                                    <!--end::Table body-->
                                    <!--begin::Table foot-->
                                    <tfoot>
                                    <tr class="border-top border-top-dashed align-top fs-6 fw-bold text-gray-700">
                                        <th>
                                            <button type="button" class="btn btn-light-primary me-3 d-none"
                                                    data-kt-element="add-item">
														<span class="svg-icon svg-icon-2">
															<i class="fa fa-plus"></i>
														</span>
                                                {{__("add")}}
                                            </button>
                                        </th>
                                        <th colspan="2"
                                            class="border-bottom border-bottom-dashed">
                                            <div class="d-flex flex-column align-items-start">
                                                <div class="fs-6">{{__("subtotal")}}</div>
                                                <div class="fs-6">{{__("total_vat")}}</div>
                                                @if($invoice->discount_amount)
                                                    <div class="fs-6">{{__("İndirim Toplamı")}}</div>

                                                @endif
                                            </div>
                                        </th>
                                        <th colspan="2" class="border-bottom border-bottom-dashed">
                                            <div class="d-flex flex-column align-items-end">
                                                <div>
                                                    {{defaultCurrencySymbol()}}<span
                                                        data-kt-element="sub-total">{{showBalance($invoice->total_price)}}</span>
                                                </div>
                                                <div>
                                                    {{defaultCurrencySymbol()}}<span
                                                        data-kt-element="vat-total">{{showBalance($invoice->total_vat)}}</span>
                                                </div>
                                                @if($invoice->discount_amount)
                                                <div>
                                                    <span
                                                        data-kt-element="vat-total"><span class="badge badge-success">-{{defaultCurrencySymbol()}}{{showBalance($invoice->discount_amount)}}</span></span>
                                                </div>
                                                    @endif
                                            </div>
                                        </th>
                                    </tr>
                                    <tr class="align-top fw-bold text-gray-700">
                                        <th></th>
                                        <th colspan="2" class="fs-4">{{__("total")}}</th>
                                        <th colspan="2" class="text-end fs-4 text-nowrap">
                                            {{defaultCurrencySymbol()}}<span
                                                data-kt-element="grand-total">{{showBalance($invoice->total_price_with_vat)}}</span>
                                        </th>
                                    </tr>
                                    </tfoot>
                                    <!--end::Table foot-->
                                </table>
                            </div>
                            <!--end::Table-->
                            <!--begin::Item template-->
                            <table class="table d-none" data-kt-element="empty-template">
                                <tr data-kt-element="empty">
                                    <th colspan="5"
                                        class="text-muted text-center py-10">{{__("no_items")}}</th>
                                </tr>
                            </table>
                            <!--end::Item template-->
                            <!--begin::Notes-->
                            <div class="d-none">
                                <label
                                    class="form-label fs-6 fw-bold text-gray-700">{{__("notes")}}</label>
                                <textarea name="notes" class="form-control "
                                          rows="3">{!! $invoice->notes !!}</textarea>
                            </div>
                            <!--end::Notes-->

                            <!--begin::Notes-->
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary" id="form_submit_btn">
                                                        <span class="indicator-label">
                                                            <span class="d-flex flex-center gap-2">
                                                                <i class="ki-duotone ki-triangle fs-3">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                            </i> {{__("save_changes")}}
                                                            </span>
                                                        </span>
                                    <span class="indicator-progress">
                                                            {{__("please_wait")}}... <span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                                        </span>
                                </button>
                            </div>
                            <!--end::Notes-->
                        </div>
                        <!--end::Wrapper-->
                    </form>
                </div>
                <!--end:::Tab pane-->
            </div>
            <!--end:::Tab content-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
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
                            <div class="col-xl-12">
                                <!--begin::Label-->
                                <label class="form-label">Kayıtlı Adresler</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-admin.form-elements.user-address-select :userId='$invoice->user?->id'
                                                                           hideSearch="true"
                                                                           customClass="userAddressSelect"
                                                                           dropdownParent="#editAddressModal"/>
                                <!--end::Select-->
                            </div>
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
                                <textarea class="form-control " name="invoice_address"
                                          rows="3"></textarea>
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
                            <div class="col-xl-6 individual-area" style="">
                                <!--begin::Label-->
                                <label class="form-label required">TC Kimlik Numarası</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="identity_number" required
                                       class="form-control form-control ">
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 corporate-area" style="display: none;">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("tax_number")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="tax_number"
                                       class="form-control form-control ">
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 corporate-area" style="display: none;">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("tax_office")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="tax_office"
                                       class="form-control form-control ">
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 corporate-area" style="display: none;">
                                <!--begin::Label-->
                                <label
                                    class="form-label required">{{__("company_name")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="company_name"
                                       class="form-control form-control ">
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
    <script src="{{assetAdmin("/js/custom/apps/invoices/create.js")}}"></script>
    <script>
        $(document).ready(function () {
            let userDefInvoiceAddress = {
                "invoice_type": "{{@$invoice->invoice_address["invoice_type"]}}",
                "address": `{{@$invoice->invoice_address["address"]}}`,
                "country": {
                    "id": "{{@$invoice->invoice_address["country"]["id"]}}",
                    "title": "{{@$invoice->invoice_address["country"]["title"]}}"
                },
                "city": {
                    "id": "{{@$invoice->invoice_address["city"]["id"]}}",
                    "title": "{{@$invoice->invoice_address["city"]["title"]}}"
                },
                "district": {
                    "id": "{{@$invoice->invoice_address["district"]["id"]}}",
                    "title": "{{@$invoice->invoice_address["district"]["title"]}}"
                },
                "tax_number": "{{@$invoice->invoice_address["tax_number"]}}",
                "tax_office": "{{@$invoice->invoice_address["tax_office"]}}",
                "company_name": "{{@$invoice->invoice_address["company_name"]}}",
            };
            $(document).on('click', '.editAddressBtn', function () {
                let form = $("#editAddressForm"),
                    area = $(".customerInformation"),
                    invoiceAddressArea = area.find(".invoiceAddressArea");

                form.find("[name='invoice_address']").html(userDefInvoiceAddress.address);
                form.find("[name='city_id']").val(userDefInvoiceAddress.city.id).trigger("change");
                form.find("[name='district_id']").append(`<option value="${userDefInvoiceAddress.district.id}" selected="selected">${userDefInvoiceAddress.district.title}</option>`).trigger("change");
                form.find(`[name='invoice_type'][value='${userDefInvoiceAddress.invoice_type}']`).prop("checked", true).trigger("change");
                if (userDefInvoiceAddress.invoice_type == "CORPORATE") {
                    form.find("[name='identity_number']").val("")
                    form.find("[name='tax_number']").val(userDefInvoiceAddress.tax_number)
                } else {
                    form.find("[name='tax_number']").val("")
                    form.find("[name='identity_number']").val(userDefInvoiceAddress.tax_number)
                }
                form.find("[name='tax_office']").val(userDefInvoiceAddress.tax_office);
                form.find("[name='company_name']").val(userDefInvoiceAddress.company_name);
                form.find(`[name='invoice_type'][value='${userDefInvoiceAddress.invoice_type}']`).closest("label").trigger("click")

                $("#editAddressModal").modal("show");
            });
            $(document).on('change', '.userAddressSelect', function () {
                let extraParams = JSON.parse(atob($(this).find('option:selected').data("extra-params"))),
                    form = $("#editAddressForm");
                console.log(extraParams);

                form.find("[name='invoice_address']").html(extraParams.address);
                form.find("[name='city_id']").val(extraParams.city.id).trigger("change");
                form.find("[name='district_id']").append(`<option value="${extraParams.district.id}" selected="selected">${extraParams.district.title}</option>`).trigger("change");
                form.find(`[name='invoice_type'][value='${extraParams.invoice_type}']`).prop("checked", true).trigger("change");
                if (extraParams.invoice_type == "CORPORATE") {
                    form.find("[name='identity_number']").val("")
                    form.find("[name='tax_number']").val(extraParams.tax_number)
                } else {
                    form.find("[name='tax_number']").val("")
                    form.find("[name='identity_number']").val(extraParams.tax_number)
                }
                form.find("[name='tax_office']").val(extraParams.tax_office);
                form.find("[name='company_name']").val(extraParams.company_name);
                form.find(`[name='invoice_type'][value='${extraParams.invoice_type}']`).closest("label").trigger("click")

            });
            $(document).on("change", "#editAddressForm .invoiceTypeArea", function () {
                let form = $("#editAddressForm");
                if ($(this).find("[name='invoice_type']").val() == "INDIVIDUAL") {
                    form.find(".individual-area").find('input').prop('disabled', false);
                    form.find(".individual-area").find('input').prop('required', true);
                    form.find(".corporate-area").find('input').prop('required', false);
                    form.find(".corporate-area").find('input').prop('disabled', true);
                    form.find(".individual-area").fadeIn();
                    form.find(".corporate-area").hide();
                } else {
                    form.find(".corporate-area").find('input').prop('disabled', false);
                    form.find(".corporate-area").find('input').prop('required', true);
                    form.find(".individual-area").find('input').prop('required', false);
                    form.find(".individual-area").find('input').prop('disabled', true);
                    form.find(".corporate-area").fadeIn();
                    form.find(".individual-area").hide();
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
                userDefInvoiceAddress.tax_number = userDefInvoiceAddress.invoice_type == "CORPORATE" ? form.find("[name='tax_number']").val() : null
                userDefInvoiceAddress.identity_number = userDefInvoiceAddress.invoice_type == "INDIVIDUAL" ? form.find("[name='identity_number']").val() : null
                userDefInvoiceAddress.tax_office = form.find("[name='tax_office']").val()
                userDefInvoiceAddress.company_name = form.find("[name='company_name']").val()

                invoiceAddressArea.find(".invoice_type").text(userDefInvoiceAddress.invoice_type);
                invoiceAddressArea.find(".address").html(userDefInvoiceAddress.address);
                invoiceAddressArea.find(".city").text(userDefInvoiceAddress.city.title);
                invoiceAddressArea.find(".district").text(userDefInvoiceAddress.district.title);
                invoiceAddressArea.find(".tax_number").text(userDefInvoiceAddress.invoice_type == "CORPORATE" ? userDefInvoiceAddress.tax_number : "");
                invoiceAddressArea.find(".identity_number").text(userDefInvoiceAddress.invoice_type == "INDIVIDUAL" ? userDefInvoiceAddress.identity_number : "");
                invoiceAddressArea.find(".tax_office").text(userDefInvoiceAddress.invoice_type == "CORPORATE" ? userDefInvoiceAddress.tax_office : "");
                invoiceAddressArea.find(".company_name").text(userDefInvoiceAddress.invoice_type == "CORPORATE" ? userDefInvoiceAddress.company_name : "");

                if (userDefInvoiceAddress.invoice_type == "CORPORATE") {
                    invoiceAddressArea.find(".identity_number").closest("div").addClass("d-none")
                    invoiceAddressArea.find(".tax_number").closest("div").removeClass("d-none")
                    invoiceAddressArea.find(".company_name").closest("div").removeClass("d-none")
                } else {
                    invoiceAddressArea.find(".tax_number").closest("div").addClass("d-none")
                    invoiceAddressArea.find(".company_name").closest("div").addClass("d-none")
                    invoiceAddressArea.find(".identity_number").closest("div").removeClass("d-none")
                }
                $("#editAddressModal").modal("hide");
            });
            $(document).on('submit', '#invoiceForm', function (e) {
                e.preventDefault();
                let form = $("#invoiceForm"),
                    formData = new FormData(this);
                formData.append("invoice_address[invoice_type]", userDefInvoiceAddress.invoice_type)
                formData.append("invoice_address[address]", userDefInvoiceAddress.address)
                formData.append("invoice_address[district][id]", userDefInvoiceAddress.district.id)
                formData.append("invoice_address[district][title]", userDefInvoiceAddress.district.title)
                formData.append("invoice_address[city][id]", userDefInvoiceAddress.city.id)
                formData.append("invoice_address[city][title]", userDefInvoiceAddress.city.title)
                formData.append("invoice_address[country][id]", userDefInvoiceAddress.country.id)
                formData.append("invoice_address[country][title]", userDefInvoiceAddress.country.title)
                formData.append("invoice_address[tax_number]", userDefInvoiceAddress.tax_number)
                formData.append("invoice_address[identity_number]", userDefInvoiceAddress.identity_number)
                formData.append("invoice_address[tax_office]", userDefInvoiceAddress.tax_office)
                formData.append("invoice_address[company_name]", userDefInvoiceAddress.company_name)
                $.ajax({
                    type: 'POST',
                    url: form.data("url"),
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => window.location.reload());
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                            propSubmitButton(form.find("button[type='submit']"), 0);
                        }
                    }
                })

            });
            $(document).on("click", ".invoiceDeleteBtn", function () {
                let url = $(this).data("url");

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: '{{__("are_you_sure_you_want_to_delete_it")}}',
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
                            complete: function (data, status) {
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
                                        text: res.message ? res.message : "{{__('form_has_errors')}}",
                                        icon: "error",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}",
                                    })
                                }
                            }
                        })
                    }
                });
            })
            $(document).on("click", ".togglePaymentStatusBtn", function () {
                let url = $(this).data("url"),
                    status = $(this).data("status"),
                    statusText = status === 'PAID' ? 'Ödendi' : 'Ödenmedi';

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: 'Fatura durumunu "' + statusText + '" olarak değiştirmek istediğinize emin misiniz?',
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                status: status
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res.message,
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then((r) => window.location.reload());
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
                            }
                        })
                    }
                });
            })
            $(document).on("click", ".npShareLinkBtn", function () {
                var link = $(this).data("link");
                navigator.clipboard.writeText(link).then(function() {
                    toastr.success("Link panoya kopyalandı!");
                }).catch(function() {
                    var tmp = $('<input>');
                    $('body').append(tmp);
                    tmp.val(link).select();
                    document.execCommand('copy');
                    tmp.remove();
                    toastr.success("Link panoya kopyalandı!");
                });
            });
            $(document).on("click", ".sendToParachuteBtn", function () {
                let url = $(this).data("url");

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: 'Faturayı resmileştirmek istediğinize emin misiniz?',
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
                            complete: function (data, status) {
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: res.message,
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then((r) => window.location.reload());
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
                            }
                        })
                    }
                });
            })
        })
    </script>
@endsection
