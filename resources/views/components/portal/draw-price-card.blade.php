@props([
    "product"
    ])
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
    <div class="col-xl-4">
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
                                {{$price->duration}} {{__(strtolower($price->duration_unit))}}
                            </div>
                            <!--end::Desc-->
                            <!--begin::Price-->
                            <div class="text-center">
                                <span class="mb-2 fs-1 text-primary">{{\App\Models\Currency::DEFAULT_SYMBOL}}</span>
                                <span class="fs-2hx fw-bold text-primary">{{showBalance($price->price)}}</span>
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
                    <button data-np-price-card="button" class="btn btn-primary" type="button"
                            data-np-attributes="{{json_encode($productAttrs)}}"
                            data-np-url="{{route("portal.basket.addToBasket", ["price" => $price->id])}}">
                        <!--begin::Indicator label-->
                        <span class="indicator-label">{{__('add_to_basket')}}</span>
                        <!--end::Indicator label-->
                        <!--begin::Indicator progress-->
                        <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        <!--end::Indicator progress-->
                    </button>
                    <!--end::Select-->
                </div>
                <!--end::Option-->
            </div>
        </div>
    </div>
@endforeach
