@props([
    "order"
])
<div class="table-responsive">
    <table class="table table-row-bordered gy-3 text-gray-700 fw-semibold fs-6">
        <thead>
        <tr class="fw-bold fs-6 text-gray-800">
            <th>{{__("service_name")}}</th>
            <th>{{__("amount")}}</th>
        </tr>
        </thead>
        <tbody>
        @php($__additionalServices = $order->allAdditionalServices())
        @if(count($__additionalServices) > 0)
            @foreach($__additionalServices as $service)
                <tr>
                    <td>{{$service["label"]}}</td>
                    <td class="d-flex align-center">
                        {{showBalance($service["price"], true)}}
                        @if(isset($service["invoice_id"]))
                            <a target="_blank"
                               href="{{route("portal.invoices.show", ["invoice" => $service["invoice_id"]])}}">
                                <i class="fa fa-file-invoice ms-2 fs-4 text-dark text-hover-primary"
                                   data-bs-toggle="tooltip" title="{{__("view_invoice")}}"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="4"
                    class="text-center py-7 text-gray-600 fw-semibold fs-6">{{__("no_additional_services")}}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
