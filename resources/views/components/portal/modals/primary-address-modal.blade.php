@props([
"modalId" => "primaryAddressModal",
"formId" => "primaryAddressForm",
"data" => null,
"hardly" => false
])
<div class="modal fade" id="{{$modalId}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="{{$modalId}}_header"
                 data-add-text="{{__("add_:name", ["name" => __("address")])}}"
                 data-edit-text="{{__("edit_:name", ["name" => __("address")])}}">
                <!--begin::Modal title-->
                <h2>{{$data ? __("edit_:name", ["name" => __("address")]) : __("add_:name", ["name" => __("address")])}}</h2>
                <!--begin::Close-->
                @if(!$hardly)
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                @endif
            <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body py-lg-10 px-lg-15">
                <form id="{{$formId}}" action="{{route('portal.users.addresses.store')}}">
                @csrf
                <!--begin::Scroll-->
                    <div class="scroll-y me-n7 pe-7" id="{{$modalId}}_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#{{$modalId}}_header"
                         data-kt-scroll-wrappers="#{{$modalId}}_scroll" data-kt-scroll-offset="300px">
                        <div class="row g-3">
                            <div class="col-xl-12">
                                @if($hardly)
                                    <div
                                        class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-3 p-6">
                                        <!--begin::Icon-->
                                        <i class="ki-duotone ki-information fs-2tx text-primary me-4"><span
                                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <!--end::Icon-->
                                        <!--begin::Wrapper-->
                                        <div class="d-flex flex-stack flex-grow-1 ">
                                            <!--begin::Content-->
                                            <div class=" fw-semibold">
                                                <div class="fs-6 text-gray-700 ">
                                                    Faturanızın düzenlenmesi için bir adres bilgisi tanımlamalısınız.
                                                </div>
                                            </div>
                                            <!--end::Content-->
                                        </div>
                                        <!--end::Wrapper-->
                                    </div>
                                @endif
                            </div>
                            @if(!$hardly)
                            <div class="col-xl-12">
                                <!--begin::Checkbox-->
                                <label class="form-check form-check-custom ">
                                    <input class="form-check-input" name="default_invoice_address" type="checkbox"
                                           checked value="1"/>
                                    <span class="form-check-label text-gray-800 fw-semibold">
                                            {{__("define_as_default_invoice_address")}}
                                        </span>
                                </label>
                                <!--end::Checkbox-->
                            </div>
                            @endif
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label">{{__("address_title")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" class="form-control form-control-lg "
                                       name="title">
                                <!--end::Input-->
                            </div>
                            <div class="col-xl-6">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("country")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-portal.form-elements.country-select name="country_id" selectedOption="1"
                                                                       id="selectCountryPrimaryModal"
                                                                    dropdownParent="#{{$modalId}}"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 show-on-turkey-area">
                                <!--begin::Label-->
                                <label class="form-label">{{__("city")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-portal.form-elements.city-select name="city_id"
                                                                    id="selectCityPrimaryModal"
                                                                    dropdownParent="#{{$modalId}}"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-6 show-on-turkey-area">
                                <!--begin::Label-->
                                <label class="form-label">{{__("district")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <x-portal.form-elements.district-select name="district_id"
                                                                        dropdownParent="#{{$modalId}}"/>
                                <!--end::Select-->
                            </div>
                            <div class="col-xl-12">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("address")}}</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea name="address" cols="30" rows="3" class="form-control "
                                          required></textarea>
                                <!--end::Textarea-->
                            </div>
                            <div class="col-xl-12">
                                <!--begin::Label-->
                                <label class="form-label required">{{__("invoice_type")}}</label>
                                <!--end::Label-->
                                <!--begin::Radio group-->
                                <div class="btn-group w-100" data-kt-buttons="true"
                                     data-kt-buttons-target="[data-kt-button]">
                                    <!--begin::Radio-->
                                    <label
                                        class="btn btn-outline btn-active-primary btn-color-muted invoiceTypeArea active"
                                        data-kt-button="true">
                                        <!--begin::Input-->
                                        <input class="btn-check" type="radio" name="invoice_type" checked
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
                                        <input class="btn-check" type="radio" name="invoice_type"
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
                                <label class="form-label">TC Kimlik Numarası</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="identity_number"
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
                                <label class="form-label required">{{__("company_name")}}</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <input type="text" name="company_name"
                                       class="form-control form-control ">
                                <!--end::Select-->
                            </div>
                        </div>
                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="d-flex flex-center flex-row-fluid pt-12">
                        @if(!$hardly)
                            <button type="reset" class="btn btn-light me-3"
                                    data-bs-dismiss="modal">{{__("cancel")}}</button>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">{{__("save")}}</span>
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

@push("js")
    <script>
        $(document).ready(function () {


            $(document).on('change','#selectCountryPrimaryModal',function(){
                if($(this).val() == 1){
                    $('.show-on-turkey-area').show(500);
                }else{
                    $('.show-on-turkey-area').hide(500);

                }
            })
            $('#selectCountryPrimaryModal').trigger('change');
            $(document).on("submit", "#primaryAddressForm", function (e) {
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
                            }).then(r => window.location.reload());
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })
            $(document).on("click", "#{{$formId}} .invoiceTypeArea", function () {
                let form = $("#{{$formId}}");
                if ($(this).find("[name='invoice_type']").val() == "INDIVIDUAL") {
                    form.find(".individual-area").find('input').prop('disabled',false);
                    // form.find(".individual-area").find('input').prop('required',true);
                    form.find(".corporate-area").find('input').prop('required',false);
                    form.find(".corporate-area").find('input').prop('disabled',true);
                    form.find(".individual-area").fadeIn();
                    form.find(".corporate-area").hide();
                } else {
                    form.find(".corporate-area").find('input').prop('disabled',false);
                    // form.find(".corporate-area").find('input').prop('required',true);
                    form.find(".individual-area").find('input').prop('required',false);
                    form.find(".individual-area").find('input').prop('disabled',true);
                    form.find(".corporate-area").fadeIn();
                    form.find(".individual-area").hide();
                }
            })
        })
    </script>
@endpush
