@extends("portal.template")
@section("title", __("my_basket"))
@section("css")
    <style>
        .loading {
            filter: blur(3px);
            pointer-events: none;
        }
    </style>
@endsection
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('my_basket')"/>
@endsection
@section("master")
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header bg-light-primary">
                    <div class="card-title">
                        <h2>{{__("my_basket")}}</h2>
                    </div>
                </div>
                <!--begin::Body-->
                <div class="card-body table-responsive">
                    <table id="dataTable" class="table align-middle table-row-bordered fs-6 gy-5 no-footer">
                        <thead>
                        <tr class="text-start text-gray-900 fw-bold fs-5 gs-0">
                            <th class="min-w-50px"></th>
                            <th class="min-w-50px">{{__("product")}} / {{__("service")}}</th>
                            <th class="min-w-200px">{{__("period")}}</th>
                            <th class="min-w-125px text-end">{{__("amount")}}</th>
                            <th class="min-w-50px"></th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @if(isset($basket))
                            @foreach($basket->items as $item)
                                <tr data-id="{{$item->id}}">
                                    <td>
                                        <button type="button"
                                                data-bs-toggle="tooltip" title="{{__("add_1_more")}}"
                                                class="btn btn-icon btn-sm btn-light btn-icon-gray-500 copyItemAddToBasketBtn">
                                            <i class="ki-duotone ki-plus fs-1"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="fw-bold fs-5 text-gray-900">{!! $item->is_test_product ? '<i class="fa fa-star text-warning fs-2"></i>' : '' !!}{{$item->product->name}}</div>
                                        <div class="badge badge-primary">{{$item->product->category->name}}</div>
                                        @if($item->additional_services)
                                            <div class="mt-7 fs-6 ms-1">
                                                @foreach($item->additional_services as $name => $value)
                                                    <input type="hidden" name="">
                                                    <div class="text-muted">
                                                        - {{$item->getAdditionalServices($name, $value)["label"]}} -
                                                        <span>{{showBalance($item->getAdditionalServices($name, $value)["price_without_vat"], true)}}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="">

                                        @if($item->is_test_product)
                                        <span class="badge badge-secondary badge-lg px-7 py-4 rounded-4 ">
{{--                                            {{$item?->price?->duration}} {{__(strtolower($item?->price?->duration_unit))}}--}}
                                            2 Saat (ücretsiz)
                                        </span>
                                        @else
                                        <x-portal.form-elements.select :hideSearch="true"
                                                                       customClass="selectPrice form-select-sm"
                                                                       :options="$item->getPeriodOptions()"
                                                                       :selectedOption="$item->price_id"/>
                                            @endif
                                    </td>
                                    <td class="text-end">{{showBalance($item?->price?->price_without_vat, true)}}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-active-color-primary"
                                                data-kt-basket="remove-item">
                                            <i class="ki-duotone ki-trash fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                            </i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            @if($basket->items->count() == 0)
                                <tr>
                                    <td colspan="3" class="text-center"><span
                                            class="fw-bold">Sepetinizde hiç ürün yok.</span></td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="3" class="text-center"><span
                                        class="fw-bold">Sepetinizde hiç ürün yok.</span></td>
                            </tr>
                        @endif
                        </tbody>
                        <!--end::Table body-->
                    </table>
                </div>
                <!--end::Body-->
            </div>
            <div class="d-flex flex-center">
                <a href="#" class="btn btn-light-primary w-200px mt-6 d-flex flex-center d-none"><i
                        class="fa fa-plus"></i>{{__("add_product_to_cart")}}</a>
            </div>
        </div>
        <div class="col-xl-4">
            <x-portal.order-summary-card :basket="$basket"/>
            @if(isset($basket))
                <a href="{{route("portal.basket.payment.index")}}"
                   class="btn btn-primary w-100 mt-6">{{__("confirm_basket")}} <i class="fa fa-chevron-right fs-4"></i>
                </a>
            @endif
        </div>
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $(document).on("change", ".selectPrice", function () {
                let row = $(this).closest("tr"),
                    id = row.attr("data-id");
                url = `{{ route('portal.basket.changePeriodToBasket', ['basketItem' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        _token: '{{csrf_token()}}',
                        price_id: $(this).val()
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        row.closest("tbody").addClass("loading");
                        $("[data-np-basket-summary='card_body']").addClass("loading");
                    },
                    success: function (res) {
                        if (res && res.success === true) {
                            window.location.reload()
                        } else {
                            row.closest("tbody").removeClass("loading");
                            $("[data-np-basket-summary='card_body']").removeClass("loading");
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                    }
                })
            })
            $(document).on("click", ".copyItemAddToBasketBtn", function () {
                let row = $(this).closest("tr"),
                    id = row.attr("data-id");
                url = `{{ route('portal.basket.copyItemAddToBasket', ['basketItem' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        _token: '{{csrf_token()}}',
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        row.closest("tbody").addClass("loading");
                        $("[data-np-basket-summary='card_body']").addClass("loading");
                    },
                    success: function (res) {
                        if (res && res.success === true) {
                            window.location.reload()
                        } else {
                            row.closest("tbody").removeClass("loading");
                            $("[data-np-basket-summary='card_body']").removeClass("loading");
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                    }
                })
            })
            $(document).on("click", "[data-kt-basket='remove-item']", function () {
                let row = $(this).closest("tr"),
                    id = row.attr("data-id"),
                    url = `{{ route('portal.basket.removeBasketItem', ['basketItem' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        _token: '{{csrf_token()}}',
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        row.closest("tbody").addClass("loading");
                        $("[data-np-basket-summary='card_body']").addClass("loading");
                    },
                    success: function (res) {
                        if (res && res.success === true) {
                            $("[data-np-basket-summary='sub_total']").text("₺" + priceFormat.to(res?.basket_summary?.sub_total));
                            $("[data-np-basket-summary='tax']").text("₺" + priceFormat.to(res?.basket_summary?.tax));
                            $("[data-np-basket-summary='total']").text("₺" + priceFormat.to(res?.basket_summary?.total));
                            $("[data-np-basket-summary='count']").text(res?.basket_summary?.count ?? "0");

                            row.closest("tbody").removeClass("loading");
                            $("[data-np-basket-summary='card_body']").removeClass("loading");
                            row.closest("tr").remove()
                            window.location.reload()
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

                    }
                })
            })
        })
    </script>
@endsection
