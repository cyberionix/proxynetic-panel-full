@extends("portal.template")
@section("title", __("buy_product"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('buy_product')"/>
@endsection
@section("master")
    <div data-np-area="prices">
        @if(count($productCategory->children) > 0)
            <!--begin:::Tabs-->
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold gap-10 d-flex flex-center mb-10">
                @foreach($productCategory->children as $child)
                    <!--begin:::Tab item-->
                    <li class="nav-item">
                        <a class="nav-link mx-0 text-active-primary pb-4 {{$loop->first ? "active" : ""}}"
                           data-bs-toggle="tab"
                           href="#{{\Illuminate\Support\Str::slug($child->name, "_")}}_tab">{{$child->name}}</a>
                    </li>
                    <!--end:::Tab item-->
                @endforeach
            </ul>
            <!--end:::Tabs-->
            <!--begin:::Tab content-->
            <div class="tab-content" id="myTabContent">
                @foreach($productCategory->children as $child)
                    <!--begin:::Tab pane-->
                    <div class="tab-pane fade {{$loop->first ? "show active" : ""}}"
                         id="{{\Illuminate\Support\Str::slug($child->name, "_")}}_tab" role="tabpanel">
                        <div class="row g-5">
                            @if(count($child->products) <= 0)
                                <div
                                    class="alert alert-primary text-center py-5 fs-5">{{__("there_are_no_products_in_this_category_it_will_be_renewed_as_soon_as_possible")}}</div>
                            @else
                                @foreach($child->products as $product)
                                    <x-portal.draw-price-card :product="$product"/>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <!--end:::Tab pane-->
                @endforeach

            </div>
            <!--end:::Tab content-->
        @else
            <div class="row g-5">
                @if(count($productCategory->products) <= 0)
                    <div
                        class="alert alert-primary text-center py-5 fs-5">{{__("there_are_no_products_in_this_category_it_will_be_renewed_as_soon_as_possible")}}</div>
                @else
                    @foreach($productCategory->products as $product)
                        <x-portal.draw-price-card :product="$product"/>
                    @endforeach
                @endif
            </div>
        @endif
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
