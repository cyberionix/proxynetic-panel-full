@extends("portal.template")
@section("title", __("my_basket"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('my_basket')"/>
@endsection
@section("master")
    <div class="row g-6">
        <div class="col-xl-8">
            <div class="card mb-6">
                <div class="card-header bg-light-primary min-h-50px h-50px">
                    <h3 class="card-title">
                        <span class="card-label fw-bold fs-3"> {{__("invoice_information")}}</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="">
                        <!--begin::Label-->
                        <label class="required form-label">Fatura Adresiniz <a href="javascript:void(0)"
                                                                               data-bs-target="#primaryAddressModal"
                                                                               data-bs-toggle="modal"><span
                                    class="badge badge-success">Yeni Oluştur</span></a></label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <x-portal.form-elements.invoice-address-select name="invoice_address_id"
                                                                       :selectedOption="auth()->user()->address?->id"
                                                                       :hideSearch="true"/>
                        <!--end::Input-->
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-light-primary">
                    <h3 class="card-title">
                        <div class="d-flex align-items-start flex-column">
                            <span class="card-label fw-bold fs-3">{{__("payment_method")}}</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Ödemenizi 3D Secure ile güvenli biçimde veya havale/eft yöntemleri ile gerçekleştirebilirsiniz.</span>
                        </div>
                    </h3>
                </div>
                <div class="card-body">
                    <x-portal.payment-area :basket="$basket"/>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <x-portal.order-summary-card/>
        </div>
    </div>
    @if(auth()->user()->addresses->count() <= 0)
        <x-portal.modals.primary-address-modal hardly="true"/>
    @else
        <x-portal.modals.primary-address-modal/>
    @endif
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            @if(auth()->user()->addresses->count() <= 0)
            $('#primaryAddressModal').modal('show');
            @endif


        })
    </script>
@endsection
