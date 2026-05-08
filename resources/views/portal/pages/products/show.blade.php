@extends("portal.template")
@section("title", $product->name)
@section("breadcrumb")
    <x-portal.bread-crumb :data="$product->name"/>
@endsection
@section("master")
    <div data-np-area="prices">
        <div class="row g-5">
            <div class="col-12 col-md-8 mx-auto">
                @if($product->category)
                    <div class="text-center mb-5">
                        <a href="{{route('portal.products.index', ['productCategory' => $product->category_id])}}" class="text-muted fw-semibold">
                            <i class="fa fa-arrow-left me-2"></i>{{ $product->category->name }}
                        </a>
                    </div>
                @endif
                <div class="card shadow-sm mb-5">
                    <div class="card-body p-8">
                        <h1 class="text-center fw-bold mb-3">{{ $product->name }}</h1>
                        @if($product->category && $product->category->parent)
                            <div class="text-center mb-3">
                                <span class="badge badge-light-info me-2">{{ $product->category->parent->name }}</span>
                                <span class="badge badge-light-primary">{{ $product->category->name }}</span>
                            </div>
                        @endif
                        @if($product->properties)
                            <div class="text-gray-700 fs-5 text-center mb-5">
                                {!! $product->properties !!}
                            </div>
                        @endif
                    </div>
                </div>
                <x-portal.draw-price-card :product="$product"/>
            </div>
        </div>
    </div>
    <div data-np-area="additional_services" style="display: none">
        <div class="alert alert-primary text-center py-5 fs-5 mb-10">{{__("select_if_there_are_additional_services_you_want_to_be_included_in_your_order")}}</div>
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
                            <span class="indicator-label">{{__('add_to_basket')}}</span>
                            <span class="indicator-progress">{{__("please_wait")}}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
                        data: { _token: "{{csrf_token()}}" },
                        beforeSend: function () {
                            propSubmitButton($("[data-np-price-card='button']"), 1);
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
