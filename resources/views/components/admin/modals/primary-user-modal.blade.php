@props([
    'modalId' => 'primaryUserModal',
    'modalTitle' => '',
    'formId' => 'userForm',
    'data' => '',
    'url' => ''
    ])
<div class="modal fade" id="{{$modalId}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="{{$modalId}}_header">
                <!--begin::Modal title-->
                <h2>{{$modalTitle}}</h2>
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
                <form id="{{$formId}}" data-url="{{$url}}">
                    @csrf
                    <!--begin::Scroll-->
                    <div class="scroll-y me-n7 pe-7" id="{{$modalId}}_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#{{$modalId}}_header"
                         data-kt-scroll-wrappers="#{{$modalId}}_scroll" data-kt-scroll-offset="300px">
                        <div class="row g-5">
                            <div class="col-xl-6">
                                <h4 class="mb-5">{{__("basic_information")}}</h4>
                                <div class="row g-3">
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="required form-label">{{__("first_name")}}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="first_name" value="{{$data?->first_name ?? ""}}"
                                               class="form-control form-control-lg " required>
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="required form-label">{{__("last_name")}}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="last_name" value="{{$data?->last_name ?? ""}}"
                                               class="form-control form-control-lg " required>
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="form-label required">{{__("tc_identity_number")}}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input class="form-control" name="identity_number" value="{{$data?->identity_number ?? ''}}">
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="form-label required">{{__("birth_date")}} <span class="text-muted fs-7">({{__("day")}}/{{__("month")}}/{{__("year")}})</span></label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input class="form-control dateMask" name="birth_date" value="{{$data?->birth_date ?? ''}}">
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="form-label">{{__("customer_group")}}</label>
                                        <!--end::Label-->
                                        <!--begin::Select-->
                                        <x-admin.form-elements.user-group-select :allowClear="true"
                                                                                 :hideSearch="true"
                                                                                 :selectedOption="$data?->user_group_id ?? ''"
                                                                                 :dropdownParent="'#' . $modalId"/>
                                        <!--end::Select-->
                                    </div>
                                    <div class="col-12">
                                        <h4 class="my-5">{{__("notification_permissions")}}</h4>
                                        <div class="row g-3">
                                            <div class="col-xl-6">
                                                <!--begin::Checkbox-->
                                                <label class="form-check form-check-custom  me-10">
                                                    <input class="form-check-input h-25px w-25px" type="checkbox"
                                                           name="accept_sms" value="1" {{isset($data->accept_sms) && $data?->accept_sms == 1 ? 'checked="checked"' : ''}}>
                                                    <span class="form-check-label fw-semibold">{{__("receive_sms_notifications")}}</span>
                                                </label>
                                                <!--end::Checkbox-->
                                            </div>
                                            <div class="col-xl-6">
                                                <!--begin::Checkbox-->
                                                <label class="form-check form-check-custom  me-10">
                                                    <input class="form-check-input h-25px w-25px" type="checkbox"
                                                           name="accept_email" value="1" {{isset($data->accept_sms) &&  $data?->accept_email == 1 ? 'checked="checked"' : ''}}>
                                                    <span class="form-check-label fw-semibold">{{__("receive_email_notifications")}}</span>
                                                </label>
                                                <!--end::Checkbox-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <h4 class="mb-5">{{__("contact_information")}}</h4>
                                <div class="row g-3">
                                    <div class="col-xl-{{$data ? "12" : "6"}}">
                                        <!--begin::Label-->
                                        <label class="required form-label">{{__("email")}}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="email" name="email" value="{{$data?->email ?? ""}}"
                                               class="form-control form-control-lg " required>
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-6 {{$data ? "d-none" : ""}}">
                                        <!--begin::Label-->
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <label class="required form-label">{{__("password")}}</label>
                                            </div>
                                            <div>
                                                        <span
                                                            class="badge badge-success badge-sm cursor-pointer generateRandomPassword">{{__("create")}}</span>
                                            </div>
                                        </div>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="password"
                                               class="form-control form-control-lg ">
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-12">
                                        <!--begin::Label-->
                                        <label class="form-label">{{__("phone_number")}}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <div class="phoneArea">
                                            <input type="tel"
                                                   class="form-control form-control-lg  phoneInput">
                                        </div>
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="form-label">Telefon Numarasını Doğrula</label>
                                        <!--end::Label-->
                                        <!--begin::Checkbox-->
                                        <label class="form-check form-check-custom me-10">
                                            <input class="form-check-input h-25px w-25px" type="checkbox"
                                                   name="phone_verify" value="1" {{isset($data->phone_verified_at) && !empty($data?->phone_verified_at) ? 'checked="checked"' : ''}}>
                                            <span class="form-check-label fw-semibold">Doğrulandı</span>
                                        </label>
                                        <!--end::Checkbox-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="form-label">E-Posta Aderisini Doğrula</label>
                                        <!--end::Label-->
                                        <!--begin::Checkbox-->
                                        <label class="form-check form-check-custom me-10">
                                            <input class="form-check-input h-25px w-25px" type="checkbox"
                                                   name="email_verify" value="1" {{isset($data->email_verified_at) && !empty($data?->email_verified_at) ? 'checked="checked"' : ''}}>
                                            <span class="form-check-label fw-semibold">Doğrulandı</span>
                                        </label>
                                        <!--end::Checkbox-->
                                    </div>
                                    <div class="col-xl-6">
                                        <!--begin::Label-->
                                        <label class="form-label">TC Kimlik No Doğrula</label>
                                        <!--end::Label-->
                                        <!--begin::Checkbox-->
                                        <label class="form-check form-check-custom me-10">
                                            <input class="form-check-input h-25px w-25px" type="checkbox"
                                                   name="identity_number_verify" value="1" {{isset($data->identity_number_verified_at) && !empty($data?->identity_number_verified_at) ? 'checked="checked"' : ''}}>
                                            <span class="form-check-label fw-semibold">Doğrulandı</span>
                                        </label>
                                        <!--end::Checkbox-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Scroll-->
                    <!--begin::Actions-->
                    <div class="d-flex flex-center flex-row-fluid pt-12">
                        <button type="reset" class="btn btn-light me-3"
                                data-bs-dismiss="modal">{{__("cancel")}}</button>
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
            const form = $("#{{$formId}}");
            const formName = "#{{$formId}}";
            const fillPasswordField = () => {
                $(form).find("[name='password']").val(generateRandomPassword())
            }

            $(document).on("click", `${formName} .generateRandomPassword`, function () {
                fillPasswordField();
            });
            $("#{{$modalId}}").on('show.bs.modal', function () {
                fillPasswordField()
            });
        })
    </script>
@endpush
