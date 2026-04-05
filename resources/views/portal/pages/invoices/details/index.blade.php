@extends("portal.template")
@section("title", '#' . $invoice->invoice_number)
@section("breadcrumb")
    <x-portal.bread-crumb :data="['#' . $invoice->invoice_number , __('invoices') => route('portal.invoices.index')]"/>
@endsection
@section("master")
    <div class="card">
        <!--begin::Body-->
        <div class="card-body p-7 p-lg-20">
            <!--begin::Layout-->
            <div class="d-flex flex-column flex-xl-row px-0 px-lg-15">
                <!--begin::Content-->
                <div class="flex-lg-row-fluid">
                    @if($invoice->hasPendingCheckout())
                        <div class="alert alert-primary">
                            Ödeme bildiriminiz başarıyla kaydedili. Mesai saatlerinde yoğunluğa bağlı olarak 0-2 saat
                            aralığında ödemeniz onaylanacaktır. Farklı sorularınız var ise bize ulaşmaktan lütfen
                            çekinmeyin. 😊
                        </div>
                    @endif
                    <!--begin::Invoice 2 content-->
                    <div class="mt-n1">
                        <!--begin::Top-->
                        <div class="row pb-10">
                            <!--begin::Logo-->
                            <div class="col d-flex align-items-center">
                                <img class="w-50 theme-light-show" alt="Logo" src="{{url(brand("logo_dark"))}}"/>
                                <img class="w-50 theme-dark-show" alt="Logo" src="{{url(brand("logo"))}}"/>
                            </div>
                            <!--end::Logo-->
                            <!--begin::Action-->
                            <div class="col">
                                @if($invoice->isPaid())
                                    <div class="badge badge-success d-flex flex-column gap-3 px-7 py-4">
                                        <span class="fs-2">{{__(mb_strtolower($invoice->status))}}</span>
                                        <span>{{__(mb_strtolower($invoice->checkout?->type))}} {{$invoice->checkout?->paid_at}}</span>
                                    </div>
                                @else
                                    {!! $invoice->drawStatus("badge-lg px-7 py-4") !!}
                                @endif
                            </div>
                            <!--end::Action-->
                        </div>
                        <!--end::Top-->
                        <!--begin::Wrapper-->
                        <div class="m-0">
                            <!--begin::Label-->
                            <div class="fw-bold fs-3 text-gray-800 mb-8">{{__("invoice")}}
                                #{{$invoice->invoice_number}}</div>
                            <!--end::Label-->
                            <!--begin::Row-->
                            <div class="row g-5 mb-11">
                                <!--end::Col-->
                                <div class="col-sm-6">
                                    <!--end::Label-->
                                    <div class="fw-semibold fs-7 text-gray-600 mb-1">{{__("invoice_date")}}:</div>
                                    <!--end::Label-->
                                    <!--end::Col-->
                                    <div class="fw-bold fs-6 text-gray-800">
                                        {{date("j", strtotime($invoice->invoice_date))}}
                                        {{__(strtolower(date("M", strtotime($invoice->invoice_date))))}}
                                        {{date("Y", strtotime($invoice->invoice_date))}}
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Col-->
                                <!--end::Col-->
                                <div class="col-sm-6">
                                    <!--end::Label-->
                                    <div
                                        class="fw-semibold fs-7 text-gray-600 mb-1">{{__("payment_due_date")}}:
                                    </div>
                                    <!--end::Label-->
                                    <!--end::Info-->
                                    <div
                                        class="fw-bold fs-6 text-gray-800 d-flex align-items-center flex-wrap">
                                        {{date("j", strtotime($invoice->due_date))}}
                                        {{__(strtolower(date("M", strtotime($invoice->due_date))))}}
                                        {{date("Y", strtotime($invoice->due_date))}}
                                    </div>
                                    <!--end::Info-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row g-5 mb-12">
                                <!--end::Col-->
                                <div class="col-sm-6">
                                    <!--end::Text-->
                                    <div class="fw-bold fs-6 text-gray-800">{{auth()->user()->full_name}}</div>
                                    <!--end::Text-->
                                    <!--end::Description-->
                                    <div class="fw-semibold fs-7 text-gray-600">
                                        @if($invoice->isPaid() || $invoice->hasPendingCheckout())
                                            {!! nl2br($invoice->invoice_address["address"]) !!}
                                            <br>
                                            {{@$invoice->invoice_address["district"]["title"]}} /
                                            {{@$invoice->invoice_address["city"]["title"]}} /
                                            {{@$invoice->invoice_address["country"]["title"]}}
                                            <br>
                                            {{@$invoice->invoice_address["tax_number"]}}
                                            {{@$invoice->invoice_address["invoice_type"] == "CORPORATE" ? " - " . @$invoice->invoice_address["tax_office"] : ""}}
                                        @else
                                            <div class="py-2">
                                                <a href="javascript:void(0)"
                                                   data-bs-target="#primaryAddressModal"
                                                   data-bs-toggle="modal"><span
                                                        class="badge badge-success mb-2">Yeni Oluştur</span></a>
                                                <x-portal.form-elements.invoice-address-select name="invoice_address_id"
                                                                                               :placeholder="__(':name_selection', ['name' => __('address')])"
                                                                                               :selectedOption="auth()->user()->address?->id"
                                                                                               customClass="form-select-sm"
                                                                                               :hideSearch="true"/>
                                            </div>
                                            <span data-np-address="address">{{auth()->user()->address?->address}}</span>
                                            <br>
                                            <span data-np-address="district">{{auth()->user()->address?->district?->title}}</span>
                                            <span data-np-address="city">{{auth()->user()->address?->city?->title}}</span>
                                            <span data-np-address="country">{{auth()->user()->address?->country?->title}}</span>
                                            <br>
                                            <span data-np-address="tax_number">{{auth()->user()->address?->tax_number}}</span>
                                            <span data-np-address="tax_office">{{auth()->user()->address?->tax_office}}</span>
                                        @endif
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Col-->
                                <!--end::Col-->
                                <div class="col-sm-6">
                                    <x-invoice-address-area />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Content-->
                            <div class="flex-grow-1">
                                <!--begin::Table-->
                                <div class="table-responsive border-bottom mb-9">
                                    <table class="table mb-3 table-row-dashed">
                                        <thead>
                                        <tr class="border-bottom fs-6 fw-bold text-muted">
                                            <th class="min-w-175px pb-2">{{__("product")}}
                                                /{{__("service")}}</th>
                                            <th class="min-w-80px text-end pb-2">{{__("price")}}</th>
                                            <th class="min-w-100px text-end pb-2">{{__("vat")}}</th>
                                            <th class="min-w-100px text-end pb-2">{{__("amount")}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($invoice->items as $item)
                                            <tr class="fw-bold text-gray-700 fs-5 text-end">
                                                <td class="d-flex align-items-center text-start">
                                                    <div>
                                                        <div class="d-flex align-center">
                                                            {{$item->name}}
                                                            @if($item->order_id)
                                                               - <a target="_blank" href="{{route("portal.orders.show", ["order" => $item->order_id])}}">#{{$item->order_id}}</a>
                                                            @endif
                                                            <span class="badge badge-primary badge-sm ms-3">{{__("invoice_item_types.".mb_strtolower($item->type))}}</span>
                                                        </div>
                                                        @isset($item->orderDetail->additional_services)
                                                            <div class="mt-2 fs-6">
                                                                @foreach($item->orderDetail->additional_services as $additional_service)
                                                                    <div class="text-muted">
                                                                        - {{$additional_service["label"]}} ({{showBalance($additional_service["price"], true)}})
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endisset
                                                    </div>
                                                </td>
                                                <td>{{showBalance($item->total_price ?? 0, true)}}</td>
                                                <td>%{{$item->vat_percent ?? 0}}</td>
                                                <td class="fs-5 text-gray-900 fw-bolder">{{showBalance($item->total_price_with_vat ?? 0, true)}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!--end::Table-->
                                <!--begin::Container-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Section-->
                                    <div class="mw-300px">
                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack mb-3">
                                            <!--begin::Accountname-->
                                            <div
                                                class="fw-semibold pe-10 text-gray-600 fs-7">{{__("subtotal")}}:
                                            </div>
                                            <!--end::Accountname-->
                                            <!--begin::Label-->
                                            <div
                                                class="text-end fw-bold fs-6 text-gray-800">{{showBalance($invoice->total_price, true)}}</div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack mb-3">
                                            <!--begin::Accountname-->
                                            <div
                                                class="fw-semibold pe-10 text-gray-600 fs-7">{{__("total_vat")}}</div>
                                            <!--end::Accountname-->
                                            <!--begin::Label-->
                                            <div
                                                class="text-end fw-bold fs-6 text-gray-800">{{showBalance($invoice->total_vat, true)}}</div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                        @if($invoice->discount_amount)
                                            <hr>

                                            <!--begin::Item-->
                                            <div class="d-flex flex-stack mb-3">
                                                <!--begin::Accountname-->
                                                <div
                                                    class="fw-semibold pe-10 text-gray-600 fs-7">{{__("İndirim Toplamı")}}</div>
                                                <!--end::Accountname-->
                                                <!--begin::Label-->
                                                <div
                                                    class="text-end fw-bold fs-6 text-gray-800"><span class="badge badge-success">-{{showBalance($invoice->discount_amount, true)}}</span></div>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Item-->
                                        @endif
                                        <hr>
                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack mb-3">
                                            <!--begin::Accountnumber-->
                                            <div
                                                class="fw-semibold pe-10 text-gray-600 fs-7">{{__("total")}}</div>
                                            <!--end::Accountnumber-->
                                            <!--begin::Number-->
                                            <div
                                                class="text-end fw-bolder fs-6 text-gray-800">{{showBalance($invoice->total_price_with_vat, true)}}</div>
                                            <!--end::Number-->
                                        </div>
                                        <!--end::Item-->

                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Container-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Wrapper-->
                        @if($invoice->status == "PENDING")
                            <!--begin::Payment-->
                            <div class="card mt-6">
                                <div class="card-header bg-light-primary">
                                    <h3 class="card-title">
                                        <div class="d-flex align-items-start flex-column">
                                            <span class="card-label fw-bold fs-3">Faturayı Öde</span>
                                            <span class="text-muted mt-1 fw-semibold fs-7">Ödemenizi 3D Secure ile güvenli biçimde veya havale/eft yöntemleri ile gerçekleştirebilirsiniz.</span>
                                        </div>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <x-portal.payment-area :invoice="$invoice"/>
                                </div>
                            </div>
                            <!--end::Payment-->
                        @endif
                    </div>
                    <!--end::Invoice 2 content-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Layout-->
        </div>
        <!--end::Body-->
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

            $(document).on("select2:select", `[name='invoice_address_id']`, function (e) {
                let extraParams = JSON.parse(atob($(e.params.data.element).attr('data-extra-params')));

                $('[data-np-address="address"]').text(extraParams?.address);
                $('[data-np-address="district"]').text(`${extraParams?.district?.title} /`);
                $('[data-np-address="city"]').text(`${extraParams?.city?.title} /`);
                $('[data-np-address="country"]').text(extraParams?.country?.title);
                $('[data-np-address="tax_number"]').text(extraParams?.tax_number);
                $('[data-np-address="tax_office"]').text(extraParams?.invoice_type == "CORPORATE" ? ` - ${extraParams?.tax_office}` : "");
            });

            $(document).on("submit", "#primaryAddressForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    url = form.attr("action");

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("button[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res.message,
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then(r => window.location.reload());
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
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })

        })

    </script>
@endsection
