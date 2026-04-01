@extends("admin.template")
@section("title", 'Sistem Ayarları')
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
    <x-admin.bread-crumb data="Sistem Ayarları"/>
@endsection
@section("master")

    @if(session()->has('form_success'))
        <div class="alert alert-success">{{session()->get('form_success')}}</div>
    @endif
    @if(session()->has('form_error'))
        <div class="alert alert-danger">{{session()->get('form_error')}}</div>
    @endif
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->

                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <form action="{{ route('admin.updateSettings') }}" method="POST">
                @csrf
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold gap-5 mb-8">
                    <li class="nav-item">
                        <a class="nav-link pb-4 active" data-bs-toggle="tab" href="#system_settings_general_tab">Genel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link pb-4" data-bs-toggle="tab" href="#system_settings_localtonet_tab">Localtonet</a>
                    </li>
                </ul>

                <div class="tab-content" id="systemSettingsTabs">
                    <div class="tab-pane fade show active" id="system_settings_general_tab" role="tabpanel">
                        <div class="w-50 mx-auto row">
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required form-label mb-3">ACL List</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea name="urls" id="" cols="30" rows="5"
                                  class="form-control form-control-solid">{{implode("\n",$urls)}}</textarea>
                        <!--end::Input-->
                    </div>
                    <hr>
                    <div class="w-100 d-none">
                        <h3 class="my-3">Test Ürünü Ayarları</h3>
                        <div class="row">
                            <div class="col-12 mb-7">
                                <!--begin::Label-->
                                <label class="form-label fw-semibold required">Durum</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" {{config('test_product.status') == 1 ? 'checked' : ''}} name="test_product[status]" value="1" id="flexSwitchDefault"/>
                                    <label class="form-check-label" for="flexSwitchDefault">
                                        Aktif
                                    </label>
                                </div>
                                <!--end::Input-->
                            </div>
                            <div class="col-12 mb-7">
                                <!--begin::Label-->
                                <label class="form-label fw-semibold required">{{__("product")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <x-admin.form-elements.product-select name="test_product[product_id]"
                                                                      customClass="productSelection"
                                                                      :withPassives="true"
                                                                      :selectedOption="$test_product ? ['label' => $test_product->name,'value' => $test_product->id] : ''"
                                                                      />
                                <!--end::Input-->
                            </div>
                            <div class="col-12 mb-7">
                                <!--begin::Label-->
                                <label class="form-label fw-semibold required">{{__("price")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <x-admin.form-elements.select name="test_product[price_id]"
                                                              customClass="priceSelection"
                                                              :ajaxSelect2="true"
                                                              :hideSearch="true"
                                                              :selectedOption="$test_product_price ? ['label' => $test_product_price->name,'value' => $test_product_price->id] : ''"
                                                              />
                                <!--end::Input-->
                            </div>
                            <div class="col-12 d-none">
                                <label class="form-label fw-semibold">{{__("additional_services")}}</label>
                                <!--end::Label-->
                                <!--begin::Additional Services-->
                                <table id="additionalTable" class="table table-bordered">
                                    <tbody>
                                    <tr>
                                        <td colspan='2' class='text-center fw-bold text-gray-600'>Ek Hizmet Yok</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <!--end::Additional Services-->
                            </div>

                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
                </div>
                    </div>

                    <div class="tab-pane fade" id="system_settings_localtonet_tab" role="tabpanel">
                        <div class="w-50 mx-auto row">
                            <div class="col-12 mb-7">
                                <label class="form-label fw-semibold">HTTPS SSL doğrulama (cURL verify)</label>
                                <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                    <input type="hidden" name="localtonet_http_verify" value="0" id="localtonetHttpVerifyHidden"/>
                                    <input class="form-check-input" type="checkbox" name="localtonet_http_verify" value="1"
                                           id="localtonetHttpVerifySwitch" {{ $localtonetHttpVerify ? 'checked' : '' }}/>
                                    <label class="form-check-label" for="localtonetHttpVerifySwitch">
                                        Açık (önerilen — üretim)
                                    </label>
                                </div>
                                <div class="form-text text-gray-600 mt-3">
                                    Kapalıyken Localtonet API ve IP değiştirme gibi HTTPS isteklerinde sertifika doğrulanmaz (Windows geliştirme ortamında cURL 60 hatasını önlemek için kullanılır).
                                    Canlı sunucuda güvenlik için açık tutun veya sunucu CA sertifikalarını yapılandırın.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Değişiklikleri Kaydet</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Modals-->
    <div class="modal fade" id="primaryGroupModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>{{__("create")}}</h2>
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
                    <form id="tokenPoolForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row mb-7">
                            <!--begin::Label-->
                            <label class="required form-label mb-3">{{__("title")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name" class="form-control form-control-lg " required>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row">
                            <!--begin::Label-->
                            <label class="required form-label mb-3">Auth Token Seçimi</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <!--begin::Input-->
                            <x-admin.form-elements.auth-token-select
                                name="auth_tokens[]"
                                customAttr="multiple"
                                customClass="mw-100"/>
                            <!--end::Input-->
                            <!--end::Input-->
                        </div>
                        <!--begin::Actions-->
                        <div class="d-flex flex-center flex-row-fluid pt-12">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="user_group_submit_btn">
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
    <!--end::Modals-->
@endsection
@section("js")
    <script>
        $(document).ready(function(){
            var $ltHidden = $("#localtonetHttpVerifyHidden");
            var $ltSwitch = $("#localtonetHttpVerifySwitch");
            function syncLocaltonetVerifyHidden() {
                if (!$ltHidden.length || !$ltSwitch.length) return;
                $ltHidden.prop("disabled", $ltSwitch.is(":checked"));
            }
            $ltSwitch.on("change", syncLocaltonetVerifyHidden);
            syncLocaltonetVerifyHidden();

            $(document).on("select2:select", '.productSelection', function (e) {
                let additionalServiceArea = $("#additionalTable"),
                    extraParams = e.params.data.extraParams,
                    attributes,
                    body = additionalServiceArea.find("tbody");

                $(".priceSelection").val("").trigger("change")

                if (extraParams.attrs.length > 0) {
                    attributes = extraParams.attrs.filter((item) => {
                        return item.service_type === "protocol_select";
                    })

                    body.html("")
                    attributes.map((item) => {
                        body.append("<tr>" +
                            "<td>" + item.label + "</td>" +
                            "<td>" + drawFormElement(item) + "</td>" +
                            "</tr>")
                    })
                } else {
                    body.html("")
                    body.append("<tr>" +
                        "<td colspan='2' class='text-center fw-bold text-gray-600'>Ek Hizmet Yok</td>" +
                        "</tr>")
                }
            })

            $(".priceSelection").select2({
                placeholder: "{{__(":name_selection", ["name" => __("district")])}}",
                allowClear: true,
                minimumResultsForSearch: Infinity,
                tags: false,
                language: {
                    searching: function () {
                        return "{{__("searching")}}...";
                    },
                    "noResults": function () {
                        return "{{__("result_not_found")}}";
                    },
                    "errorLoading": function () {
                        return 'Ürün seçiniz';
                    }
                },
                ajax: {
                    url: "{{route("admin.prices.searchByProduct")}}",
                    type: "GET",
                    dataType: 'json',
                    quietMillis: 50,
                    data: function (term) {
                        return {
                            _token: "{{csrf_token()}}",
                            term: term,
                            product_id: $(".productSelection").val()
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
            });

        })
    </script>
@endsection
