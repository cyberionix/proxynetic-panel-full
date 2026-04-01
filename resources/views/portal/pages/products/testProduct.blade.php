@extends("portal.template")
@section("title", __("buy_product"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('buy_product')"/>
@endsection
@section("master")
    <div data-np-area="prices">
        <!--begin:::Tabs-->

            <!--end:::Tabs-->

        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="alert alert-success">
                    <div class="d-flex flex-center">
                        <div class="me-2"><i class="fa fa-check-circle fs-2x text-success"></i></div>
                        <div class="fs-5">Ürünlerimizin kalitesini deneyimlemeniz adına sizin için ücretsiz proxy sunuyoruz.
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <!--begin:::Tab content-->
            <div class="row" id="">
                @foreach($products as $product)
                    @php

                        $productAttrs = null;
                        if (is_array($product->attrs) && count($product->attrs) > 0){
                            $productAttrs = array_filter($product->attrs, function ($attr) {
                                return isset($attr["service_type"]) && $attr["service_type"] === 'protocol_select';
                            });
                        }
                        if (!$productAttrs){
                            $productAttrs = null;
                        }

                    @endphp
                    @foreach($product->prices as $price)
                        <div class="col-xl-4 mx-auto">

                        <div class="card">
                            <div class="card-body d-flex h-100 align-items-center py-15 px-10">
                                <!--begin::Option-->
                                <div class="w-100 d-flex flex-column flex-center">
                                    <!--begin::Heading-->
                                    <div class="mb-7 text-center">
                                        <!--begin::Title-->
                                        <h1 class="text-gray-900 mb-5 fw-bolder">{{$product->name}}</h1>
                                        <!--end::Title-->
                                        <div class="separator separator-dashed"></div>
                                        <div class="py-3">
                                            <!--begin::Desc-->
                                            <div class="text-gray-600 fs-3 fw-semibold mb-2">
                                                2 Saat
{{--                                                {{$price->duration}} {{__(strtolower($price->duration_unit))}}--}}
                                            </div>
                                            <!--end::Desc-->
                                            <!--begin::Price-->
                                            <div class="text-center">
                                                <span class="mb-2 fs-1 text-primary"></span>
                                                <span class="fs-2hx fw-bold text-success">Ücretsiz!</span>
                                            </div>
                                            <!--end::Price-->
                                        </div>
                                        <div class="separator separator-dashed"></div>
                                    </div>
                                    <!--end::Heading-->
                                @if($product->properties)
                                    <!--begin::Properties-->
                                        <div class="w-100 mb-10">
                                            <div class="text-center">
                                            <span class="fw-semibold fs-6 text-gray-800 flex-grow-1 pe-3 lh-xl">
                                                {!!  nl2br($product->properties) !!}
                                            </span>
                                            </div>
                                        </div>
                                        <!--end::Properties-->
                                @endif
                                <!--begin::Select-->
                                    @if($product->usable)
                                        <button data-np-price-card="button" class="btn btn-primary" type="button"
                                                data-np-attributes="{{json_encode($productAttrs)}}"
                                                data-np-url="{{route("portal.basket.addToBasket", ["price" => $price->id,"test_product" => 1])}}">
                                            <!--begin::Indicator label-->
                                            <span class="indicator-label">{{__('add_to_basket')}}</span>
                                            <!--end::Indicator label-->
                                            <!--begin::Indicator progress-->
                                            <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            <!--end::Indicator progress-->
                                        </button>
                                        @else
                                        <button class="btn btn-primary" type="button" disabled>
                                            <span class="indicator-label">Uygun Değil</span>

                                        </button>
                                        @endif
                                    <!--end::Select-->
                                </div>
                                <!--end::Option-->
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endforeach


            </div>
            <!--end:::Tab content-->

    </div>
    <div data-np-area="additional_services" style="display: none">
        <div
            class="alert alert-primary text-center py-5 fs-5 mb-10">{{__("select_if_there_are_additional_services_you_want_to_be_included_in_your_order")}}</div>
        <form id="additionalServicesForm" class="d-flex justify-content-center">
            <table class="table table-bordered mw-800px">
                <thead>
                <tr class="fw-bold fs-6 text-gray-800 bg-light">
                    <th>{{__("additional_service")}}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2" class="text-end px-0"
                        style="border-left: hidden; border-right: hidden; border-bottom: hidden;">
                        <button class="btn btn-primary addToBasketBtn" type="submit">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">{{__('add_to_basket')}}</span>
                            <!--end::Indicator label-->
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            let priceArea = $("[data-np-area='prices']"),
                additionalServiceArea = $("[data-np-area='additional_services']");

            $(document).on("click", "[data-np-price-card='button']", function () {
                let attributes = $(this).data("np-attributes"),
                    url = $(this).data("np-url");

                if (attributes) {
                    priceArea.hide();

                    attributes.map((item) => {
                        additionalServiceArea.find("table tbody").append("<tr>" +
                            "<td>" + item.label + "</td>" +
                            "<td>" + drawFormElement(item) + "</td>" +
                            "</tr>")
                    })
                    additionalServiceArea.find(".addToBasketBtn").attr("data-np-url", url);
                    additionalServiceArea.fadeIn()
                } else {
                    $.ajax({
                        type: "POST",
                        url: url,
                        dataType: "json",
                        data: {
                            _token: "{{csrf_token()}}",
                        },
                        beforeSend: function () {
                            propSubmitButton($("[data-np-price-card='button']"), 1);
                        },
                        complete: function (data, status) {
                            // propSubmitButton($("[data-np-price-card='button']"), 0);
                            res = data.responseJSON;
                            if (res && res.success === true) {
                                window.location.href = res.redirectUrl;
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
                        }
                    })
                }
            })
            $(document).on("submit", "#additionalServicesForm", function (e) {
                e.preventDefault()
                let formData = new FormData(this),
                    btn = $(this).find("button[type='submit']");

                $.ajax({
                    type: "POST",
                    url: btn.data("np-url"),
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton($(btn), 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            window.location.href = res.redirectUrl;
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
                    }
                })
            })
        })
    </script>
@endsection
