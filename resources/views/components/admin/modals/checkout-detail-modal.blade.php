@props([
    "id" => "checkoutDetailModal"
])
<div class="modal fade" id="{{$id}}" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2>{{__(":name_detail", ["name" => __("checkout")])}}</h2>
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
                <div class="row">
                    <div class="col-xl-6">
                        <div class="fw-bolder ms-5 d-flex">
                            <div class="fs-4 text-gray-900 me-2">Müşteri:</div>
                            <!--begin::Info-->
                            <div class="fs-4"><a target="_blank" class="user"></a></div>
                            <!--end::Info-->
                        </div>
                    </div>
                    <div class="col-xl-6 d-flex flex-end paymentNotify">
                        <div class="fw-semibold text-center">
                            <div class="fs-5 fw-bold text-gray-900">Ödeme Bildirimini</div>
                            <!--begin::Info-->
                            <div class="fs-6 text-muted">
                                <button class="btn btn-sm btn-light-danger paymentStatusUpdateBtn" data-type="CANCELLED">Reddet</button>
                                <button class="btn btn-sm btn-light-success paymentStatusUpdateBtn" data-type="COMPLETED">Onayla</button>
                            </div>
                            <!--end::Info-->
                        </div>
                    </div>
                </div>
                <div class="separator separator-dashed my-5"></div>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center position-relative mb-7">
                            <!--begin::Label-->
                            <div class="position-absolute top-0 start-0 rounded h-100 bg-secondary w-4px"></div>
                            <!--end::Label-->
                            <!--begin::Details-->
                            <div class="fw-semibold ms-5">
                                <div class="fs-5 fw-bold text-gray-900">{{__("invoice")}}:</div>
                                <!--begin::Info-->
                                <div class="fs-6 text-muted">
                                    <a target="_blank" href="#" class="invoice"></a>
                                </div>
                                <!--end::Info-->
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center position-relative mb-7">
                            <!--begin::Label-->
                            <div class="position-absolute top-0 start-0 rounded h-100 bg-secondary w-4px"></div>
                            <!--end::Label-->
                            <!--begin::Details-->
                            <div class="fw-semibold ms-5">
                                <div class="fs-5 fw-bold text-gray-900">{{__("amount")}}:</div>
                                <!--begin::Info-->
                                <div class="fs-6 text-muted amount"></div>
                                <!--end::Info-->
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center position-relative mb-7">
                            <!--begin::Label-->
                            <div class="position-absolute top-0 start-0 rounded h-100 bg-secondary w-4px"></div>
                            <!--end::Label-->
                            <!--begin::Details-->
                            <div class="fw-semibold ms-5">
                                <div class="fs-5 fw-bold text-gray-900">{{__("payment_date")}}:</div>
                                <!--begin::Info-->
                                <div class="fs-6 text-muted paymentDate"></div>
                                <!--end::Info-->
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center position-relative mb-7">
                            <!--begin::Label-->
                            <div class="position-absolute top-0 start-0 rounded h-100 bg-secondary w-4px"></div>
                            <!--end::Label-->
                            <!--begin::Details-->
                            <div class="fw-semibold ms-5">
                                <div class="fs-5 fw-bold text-gray-900">{{__("payment_type")}}:</div>
                                <!--begin::Info-->
                                <div class="fs-6 text-muted paymentType"></div>
                                <!--end::Info-->
                            </div>
                            <!--end::Details-->
                        </div>
                    </div>
                </div>
                <!--begin::Actions-->
                <div class="d-flex flex-center flex-row-fluid pt-12">
                    <button type="reset" class="btn btn-light me-3"
                            data-bs-dismiss="modal">{{__("cancel")}}</button>
                    <button type="submit" class="btn btn-primary d-none" id="add_book_submit_btn">
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
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
