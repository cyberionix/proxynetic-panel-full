@props([
    "basket" => auth()->user()->basket
])
<div class="card">
    <div class="card-header bg-light-primary">
        <div class="card-title">
            <h2>{{__("order_summary")}}</h2>
        </div>
    </div>
    <div class="card-body" data-np-basket-summary="card_body">
        <div class="d-flex flex-stack">
            <!--begin::Content-->
            <div class="fs-6 fw-bold">
                <span class="d-block lh-1 mb-2">{{__("sub_total")}}</span>
                <span class="d-block mb-2">{{__("tax")}}</span>
            </div>
            <!--end::Content-->
            <!--begin::Content-->
            <div class="fs-6 fw-bold text-end">
                            <span
                                class="d-block lh-1 mb-2" data-np-basket-summary="sub_total">{{ showBalance(isset($basket) ? $basket->basketSummary()["sub_total"] : 0, true)}}</span>
                <span
                    class="d-block mb-2" data-np-basket-summary="tax">{{showBalance(isset($basket) ? $basket->basketSummary()["tax"] : 0, true)}}</span>
            </div>
            <!--end::Content-->
        </div>
        <div class="separator separator-dashed my-5"></div>
        <div class="d-flex flex-stack">
            <!--begin::Content-->
            <div class="fs-6 fw-bold">
                <span class="d-block fs-2 lh-1 mt-2">{{__("total")}}</span>
            </div>
            <!--end::Content-->
            <!--begin::Content-->
            <div class="fs-6 fw-bold text-end">
                            <span
                                class="d-block fs-2 lh-1 mt-2" data-np-basket-summary="total">{{showBalance(isset($basket) ? $basket->basketSummary()["total"] : 0, true)}}</span>
            </div>
            <!--end::Content-->
        </div>
        <div class="separator separator-dashed my-5"></div>
 @if(isset($basket) && $basket->basketSummary()["coupon_code_text"] && $basket->basketSummary()["discount_amount"] > 0)
            <div class="mt-5">
                <div class="d-flex flex-stack">
                    <!--begin::Content-->
                    <div class="fs-6 fw-bold">
                        <span class="d-block lh-1 mb-2">{{__("İndirim Kodu")}}</span>
                        <span class="d-block mb-2">{{__("İndirim Miktarı")}}</span>
                    </div>
                    <!--end::Content-->
                    <!--begin::Content-->
                    <div class="fs-6 fw-bold text-end">
                            <span
                                class="d-block badge badge-light lh-1 mb-2" data-np-basket-summary="sub_total">{{ isset($basket) ? $basket->basketSummary()["coupon_code_text"] : ''}}</span>
                        <span
                            class="d-block mb-2 badge badge-success" data-np-basket-summary="tax">-{{showBalance(isset($basket) ? $basket->basketSummary()["discount_amount"] : 0, true)}} </span><i id="removeCoupon" class=" cursor-pointer fa fa-trash text-danger ms-3"></i>
                    </div>
                    <!--end::Content-->
                </div>

            </div>
            <div class="separator separator-dashed my-5"></div>
     @endif
        <div class="d-flex flex-stack mt-5 mb-2">
            <!--begin::Content-->
            <div class="fs-6 fw-bold">
                <span class="d-block fs-1 lh-1 mt-2">{{__("Ödenecek Tutar")}}</span>
            </div>
            <!--end::Content-->
            <!--begin::Content-->
            <div class="fs-6 fw-bold text-end">
                            <span
                                class="d-block fs-1 lh-1 mt-2" data-np-basket-summary="total">{{showBalance(isset($basket) ? $basket->basketSummary()["real_total"] : 0, true)}}</span>
            </div>
            <!--end::Content-->
        </div>

        @if(isset($basket) && !$basket->basketSummary()["coupon_code_text"] && !$basket->basketSummary()["discount_amount"])
            <div class="mt-10">

                <form action="" id="couponCodeForm">
                    <div class="row">
                        <div class="col-lg-7">
                            <input required type="text" id="coupon_code_area" class="form-control" placeholder="Kupon Kodu">
                        </div>
                        <div class="col-lg-5">
                            <button type="submit" id="submit_coupon_code" class="btn bm btn-success w-100">Uygula</button>
                        </div>
                    </div>
                </form>

            </div>
            @endif


    </div>
</div>

@push('js')
    <script>

        $(document).on('submit','#couponCodeForm',function(e){
            propSubmitButton($('#submit_coupon_code'),true);

            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '{{route('portal.basket.applyCoupon')}}',
                data:{
                    _token: '{{csrf_token()}}',
                    code: $('#coupon_code_area').val()
                },
                dataType:'json',
                success: function(res){
                    if(res.success === true){
                        Swal.fire({
                            title: "{{__('success')}}",
                            text: res?.message ? res.message : "{{__('form_has_errors')}}",
                            icon: "success",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "{{__('close')}}",
                        }).then(r => window.location.reload())
                    }else{
                        Swal.fire({
                            title: "{{__('error')}}",
                            text: res?.message ? res.message : "{{__('form_has_errors')}}",
                            icon: "error",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "{{__('close')}}",
                        })
                    }
                },
                complete: function(){
                    propSubmitButton($('#submit_coupon_code'),false);

                }
            })
        })
        $(document).on('click','#removeCoupon',function(e){
            propSubmitButton($('#submit_coupon_code'),true);

            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: '{{route('portal.basket.removeCoupon')}}',
                data:{
                    _token: '{{csrf_token()}}'
                },
                dataType:'json',
                success: function(res){
                    if(res.success === true){
                        window.location.reload()
                    }else{
                        Swal.fire({
                            title: "{{__('error')}}",
                            text: res?.message ? res.message : "{{__('form_has_errors')}}",
                            icon: "error",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "{{__('close')}}",
                        })
                    }
                },
                complete: function(){
                    propSubmitButton($('#submit_coupon_code'),false);

                }
            })
        })

    </script>
@endpush
