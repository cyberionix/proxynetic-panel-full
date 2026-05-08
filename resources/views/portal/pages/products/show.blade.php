@extends("portal.template")
@section("title", $product->name)
@section("breadcrumb")
    <x-portal.bread-crumb :data="$product->name"/>
@endsection
@section("css")
<style>
    .product-wizard-steps { display: flex; justify-content: center; align-items: center; gap: 0; margin: 30px 0 40px 0; }
    .product-wizard-step { display: flex; flex-direction: column; align-items: center; flex: 1; max-width: 200px; position: relative; }
    .product-wizard-step:not(:last-child)::after { content: ""; position: absolute; top: 28px; right: -50%; width: 100%; height: 2px; background: #e4e6ef; z-index: 0; }
    .product-wizard-step.active::after, .product-wizard-step.done::after { background: #50cd89; }
    .product-wizard-step .num { width: 56px; height: 56px; border-radius: 50%; background: #e4e6ef; color: #b5b5c3; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; position: relative; z-index: 1; transition: all 0.3s; }
    .product-wizard-step.active .num { background: #009ef7; color: #fff; box-shadow: 0 0 0 6px rgba(0, 158, 247, 0.15); }
    .product-wizard-step.done .num { background: #50cd89; color: #fff; }
    .product-wizard-step .lbl { margin-top: 12px; font-weight: 600; font-size: 14px; color: #7e8299; }
    .product-wizard-step.active .lbl { color: #009ef7; }
    .product-wizard-step.done .lbl { color: #50cd89; }
    .price-period-card { cursor: pointer; border: 2px solid #e4e6ef; border-radius: 12px; padding: 24px 16px; text-align: center; transition: all 0.2s; background: #fff; height: 100%; position: relative; min-height: 130px; display: flex; flex-direction: column; justify-content: center; }
    .price-period-card:hover { border-color: #009ef7; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,158,247,0.1); }
    .price-period-card.selected { border-color: #009ef7; background: linear-gradient(135deg, #f5fbff 0%, #e8f4ff 100%); box-shadow: 0 4px 16px rgba(0,158,247,0.18); }
    .price-period-card.selected::after { content: "\2713"; position: absolute; bottom: -12px; left: 50%; transform: translateX(-50%); width: 28px; height: 28px; border-radius: 50%; background: #009ef7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; }
    .price-period-card .period-name { font-size: 18px; font-weight: 700; color: #181c32; margin-bottom: 8px; }
    .price-period-card .period-price { font-size: 28px; font-weight: 800; color: #009ef7; }
    .price-period-card .period-currency { font-size: 18px; color: #009ef7; }
    .product-detail-card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
    .continue-btn-area { text-align: center; margin: 40px 0 20px; }
    .continue-btn-area .btn-continue { background: linear-gradient(135deg, #50cd89 0%, #47be7d 100%); color: #fff; border: none; padding: 14px 56px; font-weight: 700; border-radius: 999px; font-size: 16px; box-shadow: 0 4px 14px rgba(80, 205, 137, 0.3); transition: all 0.2s; }
    .continue-btn-area .btn-continue:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(80, 205, 137, 0.4); }
    .product-properties-box { background: #f9fafc; border-radius: 12px; padding: 24px; line-height: 1.7; color: #3f4254; font-size: 15px; }
    .product-meta { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin: 8px 0 20px; }
    @media (max-width: 768px) {
        .product-wizard-step .lbl { font-size: 12px; }
        .product-wizard-step .num { width: 44px; height: 44px; font-size: 18px; }
        .product-wizard-step:not(:last-child)::after { top: 22px; }
        .price-period-card .period-price { font-size: 22px; }
    }
</style>
@endsection
@section("master")
<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="card product-detail-card mb-5 border-0">
        <div class="card-body p-6 p-md-8">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h1 class="fw-bold text-gray-900 mb-2 fs-1">{{ $product->name }}</h1>
                    <div class="product-meta">
                        @if($product->category && $product->category->parent)
                            <span class="badge badge-light-info fs-7">{{ $product->category->parent->name }}</span>
                        @endif
                        @if($product->category)
                            <span class="badge badge-light-primary fs-7">{{ $product->category->name }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-md-end">
                    <a href="{{ $product->category ? route('portal.products.index', ['productCategory' => $product->category_id]) : '#' }}" class="btn btn-sm btn-light fw-semibold">
                        <i class="fa fa-arrow-left me-2"></i>{{ __("back") }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Wizard --}}
    <div data-np-area="prices">
        <div class="product-wizard-steps">
            <div class="product-wizard-step active">
                <div class="num">1</div>
                <div class="lbl">{{ __("Hizmet Süresi") }}</div>
            </div>
            <div class="product-wizard-step">
                <div class="num">2</div>
                <div class="lbl">{{ __("Gerekli Bilgiler") }}</div>
            </div>
            <div class="product-wizard-step">
                <div class="num">3</div>
                <div class="lbl">{{ __("Sepete Git") }}</div>
            </div>
        </div>

        <div class="card product-detail-card border-0">
            <div class="card-body p-5 p-md-8">
                @if(count($product->prices) > 0)
                    <p class="text-center fs-5 fw-semibold text-gray-700 mb-7">
                        {{ __("Hizmetinizin yenilenme periyodunu seçin. Uzun süreli alımlarda indirim fırsatını kaçırmayın.") }}
                    </p>

                    <div class="row g-4 justify-content-center mb-3">
                        @foreach($product->prices as $price)
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="price-period-card {{ $loop->first ? "selected" : "" }}"
                                     data-price-id="{{ $price->id }}"
                                     data-attributes="{{ json_encode(collect($product->attrs ?? [])->where("service_type", "protocol_select")->values()) }}"
                                     data-url="{{ route("portal.basket.addToBasket", ["price" => $price->id]) }}">
                                    <div class="period-name">{{ $price->duration }} {{ __(strtolower($price->duration_unit)) }}</div>
                                    <div>
                                        <span class="period-price">{{ showBalance($price->price) }}</span>
                                        <span class="period-currency">{{ \App\Models\Currency::DEFAULT_SYMBOL }}</span>
                                    </div>
                                    @if($price->is_test_product)
                                        <div class="mt-2"><span class="badge badge-warning fs-8">{{ __("Test Ürünü") }}</span></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="continue-btn-area">
                        <button type="button" id="periodContinueBtn" class="btn btn-continue">
                            <span class="indicator-label">{{ __("Devam") }} <i class="fa fa-arrow-right ms-2"></i></span>
                            <span class="indicator-progress">{{ __("please_wait") }}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                @else
                    <div class="alert alert-warning text-center">{{ __("Bu ürün için fiyat bulunmuyor.") }}</div>
                @endif
            </div>
        </div>

        @if($product->properties)
            <div class="card product-detail-card mt-5 border-0">
                <div class="card-body p-5 p-md-8">
                    <h3 class="fw-bold mb-4"><i class="fa fa-list-check text-primary me-2"></i>{{ __("Ürün Detayları") }}</h3>
                    <div class="product-properties-box">
                        {!! nl2br($product->properties) !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Step 2: Additional services --}}
    <div data-np-area="additional_services" style="display: none">
        <div class="card product-detail-card mt-5 border-0">
            <div class="card-body p-5 p-md-8">
                <div class="product-wizard-steps">
                    <div class="product-wizard-step done"><div class="num"><i class="fa fa-check"></i></div><div class="lbl">{{ __("Hizmet Süresi") }}</div></div>
                    <div class="product-wizard-step active"><div class="num">2</div><div class="lbl">{{ __("Gerekli Bilgiler") }}</div></div>
                    <div class="product-wizard-step"><div class="num">3</div><div class="lbl">{{ __("Sepete Git") }}</div></div>
                </div>
                <p class="text-center fs-5 fw-semibold text-gray-700 mb-7">{{ __("select_if_there_are_additional_services_you_want_to_be_included_in_your_order") }}</p>
                <form id="additionalServicesForm" class="d-flex justify-content-center">
                    <table class="table table-bordered mw-800px">
                        <thead>
                        <tr class="fw-bold fs-6 text-gray-800 bg-light">
                            <th>{{ __("additional_service") }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2" class="text-end px-0" style="border-left: hidden; border-right: hidden; border-bottom: hidden;">
                                <button class="btn btn-continue addToBasketBtn" type="submit">
                                    <span class="indicator-label">{{ __("add_to_basket") }} <i class="fa fa-shopping-cart ms-2"></i></span>
                                    <span class="indicator-progress">{{ __("please_wait") }}...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section("js")
<script>
$(document).ready(function () {
    let priceArea = $("[data-np-area=\"prices\"]"),
        additionalServiceArea = $("[data-np-area=\"additional_services\"]");

    // Period card selection
    $(document).on("click", ".price-period-card", function () {
        $(".price-period-card").removeClass("selected");
        $(this).addClass("selected");
    });

    // Continue button -> add to basket flow
    $(document).on("click", "#periodContinueBtn", function () {
        let selected = $(".price-period-card.selected");
        if (selected.length === 0) {
            Swal.fire({ title: "{{ __('error') }}", text: "{{ __('Lütfen bir periyod seçin.') }}", icon: "warning" });
            return;
        }
        let attributes = selected.data("attributes"),
            url = selected.data("url");

        if (attributes && attributes.length > 0) {
            priceArea.hide();
            additionalServiceArea.find("table tbody").empty();
            attributes.forEach((item) => {
                additionalServiceArea.find("table tbody").append("<tr>" +
                    "<td>" + item.label + "</td>" +
                    "<td>" + drawFormElement(item) + "</td>" +
                    "</tr>");
            });
            additionalServiceArea.find(".addToBasketBtn").attr("data-np-url", url);
            additionalServiceArea.fadeIn();
        } else {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                data: { _token: "{{ csrf_token() }}" },
                beforeSend: function () { propSubmitButton($("#periodContinueBtn"), 1); },
                complete: function (data) {
                    let res = data.responseJSON;
                    if (res && res.success === true) {
                        window.location.href = res.redirectUrl;
                    } else {
                        propSubmitButton($("#periodContinueBtn"), 0);
                        Swal.fire({
                            title: "{{ __('error') }}",
                            text: res?.message ?? "",
                            icon: "error",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "{{ __('close') }}",
                        });
                    }
                }
            });
        }
    });

    // Submit additional services form -> add to basket
    $(document).on("submit", "#additionalServicesForm", function (e) {
        e.preventDefault();
        let formData = new FormData(this),
            btn = $(this).find("button[type=\"submit\"]");

        $.ajax({
            type: "POST",
            url: btn.data("np-url"),
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function () { propSubmitButton($(btn), 1); },
            complete: function (data) {
                let res = data.responseJSON;
                if (res && res.success === true) {
                    window.location.href = res.redirectUrl;
                } else {
                    propSubmitButton($(btn), 0);
                    Swal.fire({
                        title: "{{ __('error') }}",
                        text: res?.message ?? "",
                        icon: "error",
                        showConfirmButton: 0,
                        showCancelButton: 1,
                        cancelButtonText: "{{ __('close') }}",
                    });
                }
            }
        });
    });
});
</script>
@endsection
