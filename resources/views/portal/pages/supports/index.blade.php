@extends("portal.template")
@section("title", __("support_tickets"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('support_tickets')"/>
@endsection
@section("master")
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <span class="fw-bold fs-3">{{__("support_tickets")}}</span>
            </div>
            <div class="card-toolbar">
                <button class="btn btn-primary" data-np-btn="create-support">
                    <i class="fa fa-plus"></i> {{__("create_:name", ["name" => __("support_ticket")])}}
                </button>
            </div>
        </div>
        <!--begin::Body-->
        <div class="card-body">
            <!--begin::Table-->
            <table id="dataTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                    <th class="min-w-50px">#</th>
                    <th class="min-w-125px">{{__("subject")}}</th>
                    <th class="min-w-125px">{{__("department")}}</th>
                    <th class="min-w-125px">{{__("updated_date")}}</th>
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
            var t = $("#dataTable").DataTable({
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
                        orderable: !0, targets: 4
                    },
                    {
                        orderable: !1, targets: 5
                    },
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("portal.supports.ajax")}}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            $(document).on("submit", "#primarySupportForm", function (e) {
                e.preventDefault()
                let form = $(this);

                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            }).then((r) => window.location.href = res.redirectUrl);
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
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
