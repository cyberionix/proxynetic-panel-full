@extends("admin.template")
@section("title", __("bulk_sms"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('bulk_sms')"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2 class="card-title fw-bolder">{{__("bulk_sms")}}</h2>
                        </div>
                        <!--begin::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <form action="{{route("admin.bulkSms.send")}}" id="sendSmsForm" class="card-body pt-0">
                        @csrf
                        <div class="row">
                            <!--begin::Col-->
                            <div class="col-xl-6">
                                <!--begin::Option-->
                                <input type="radio" class="btn-check" name="type" value="userFilter" checked="checked"
                                       id="userFilterBtn"/>
                                <label
                                    class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center mb-10"
                                    for="userFilterBtn">
                                    <i class="ki-duotone ki-filter fs-3x me-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <!--begin::Info-->
                                    <span class="d-block fw-semibold text-start text-gray-900 fw-bold d-block fs-3">Müşterileri Filtrele</span>
                                    <!--end::Info-->
                                </label>
                                <!--end::Option-->
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-xl-6">
                                <!--begin::Option-->
                                <input type="radio" class="btn-check" name="type" value="selectUser"
                                       id="selectUserBtn"/>
                                <label
                                    class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center mb-10"
                                    for="selectUserBtn">
                                    <i class="ki-duotone ki-profile-user fs-3x me-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    <!--begin::Info-->
                                    <span class="d-block fw-semibold text-start text-gray-900 fw-bold d-block fs-3">Müşteri Seç</span>
                                    <!--end::Info-->
                                </label>
                                <!--end::Option-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <div id="userFilterArea">
                            <div class="fw-bolder fs-4 required">Müşterileri Filtrele</div>
                            <hr>
                            <div class="row">
                                <div class="d-none col-xl-3">
                                    <label class="form-label fs-6 fw-bold">Danışmanlık Durumu:</label>
                                    <x-admin.form-elements.select name="statusFilter"
                                                                  placeholder="Tümü"
                                                                  allowClear="true"
                                                                  :hideSearch="true"
                                                                  :options="[
                                                                    ['label' => 'Aktif Danışan', 'value' => 'ACTIVE'],
                                                                    ['label' => 'Pasif Danışan', 'value' => 'PASSIVE']
                                                                  ]"/>
                                </div>
                                <div class="d-none col-xl-3">
                                    <label class="form-label fs-6 fw-bold">Kaçıncı Aydaki Müşteriler:</label>
                                    <x-admin.form-elements.select name="monthFilter"
                                                                  placeholder="Tümü"
                                                                  allowClear="true"
                                                                  :hideSearch="true"
                                                                  :options="$monthFilterOptions"/>
                                </div>
                                <div class="col-xl-3">
                                    <label class="form-label fs-6 fw-bold">{{__("city")}}:</label>
                                    <x-admin.form-elements.city-select placeholder="Tümü"
                                                                       allowClear="true"
                                    />
                                </div>
                                <div class="col-xl-3">
                                    <label class="form-label fs-6 fw-bold">{{__("district")}}:</label>
                                    <x-admin.form-elements.district-select placeholder="Tümü"
                                                                           allowClear="true"
                                    />
                                </div>
                            </div>
                        </div>
                        <div id="selectUserArea" style="display: none;">
                            <div class="fw-bolder fs-4 required">Müşteri Seç</div>
                            <hr>
                            <div>
                                <label
                                    class="form-label fs-6 fw-bold">{{__(":name_selection", ['name' => __("customer")])}}
                                    :</label>
                                <x-admin.form-elements.user-select name="user_id[]"
                                                                   placeholder="Tümü"
                                                                   allowClear="true"
                                                                   customAttr="multiple"/>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="fw-bolder fs-4 required">{{__("sms_text")}}</div>
                            <hr>
                            <div>
                                <textarea data-kt-autosize="true" rows="1" name="text"
                                          class="form-control  mb-2" placeholder=""
                                          required></textarea>
                            </div>
                        </div>
                        <div class="mt-8">
                            <button type="submit" class="btn btn-primary w-100">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">{{__("save_changes")}}</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                    </form>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $(document).on("change", "#sendSmsForm [name='type']", function () {
                let value = $(this).val();
                if (value === "userFilter") {
                    $("#selectUserArea").hide();
                    $("#userFilterArea").fadeIn();
                } else {
                    $("#userFilterArea").hide();
                    $("#selectUserArea").fadeIn();
                }
            })
            $(document).on("submit", "#sendSmsForm", function (e) {
                e.preventDefault()
                let form = $(this);
                $.ajax({
                    type: 'POST',
                    url: form.attr("action"),
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
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => window.location.reload())
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
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })
        })
    </script>
@endsection
