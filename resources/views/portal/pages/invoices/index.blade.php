@extends("portal.template")
@section("title", __("invoices"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('invoices')"/>
@endsection
@section("master")
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span class="fw-bold fs-3">{{__("invoices")}}</span>
            </div>
        </div>
        <!--begin::Body-->
        <div class="card-body">
            <!--begin::Table-->
            <table id="invoiceTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                    <th class="min-w-50px">{{__("invoice_number")}}</th>
                    <th class="min-w-125px">{{__("date")}}</th>
                    <th class="min-w-125px">{{__("amount")}}</th>
                    <th class="min-w-125px">{{__("status")}}</th>
                    <th class="min-w-125px">{{__("action")}}</th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">

                </tbody>
                <!--end::Table body-->
            </table>
            <!--end::Table-->
        </div>
        <!--end::Body-->
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var t = $("#invoiceTable").DataTable({
                order: [],
                columnDefs: [
                    {
                        orderable: !0, targets: 0
                    },
                    {
                        orderable: !0, targets: 1
                    },
                    {
                        orderable: !0, targets: 2
                    },
                    {
                        orderable: !0, targets: 3
                    },
                    {
                        orderable: !1, targets: 4
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("portal.invoices.ajax", ["user" => auth()->user()->id])}}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });
        })
    </script>
@endsection
