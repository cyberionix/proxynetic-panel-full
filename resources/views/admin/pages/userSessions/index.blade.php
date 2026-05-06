@extends("admin.template")
@section("title", __("session_recordings"))
@section("css") @endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('session_recordings')"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-table-action="search"
                           class="form-control  w-250px ps-13"
                           placeholder="{{__("search_in_table")}}"/>
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Card title-->
        </div>
        <!--end::Card header-->
        <!--start::Filter-->
        <div class="d-flex flex-wrap gap-3 px-10 my-3 table-filter-area">

        </div>
        <!--end::Filter-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table id="dataTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                    <th class="min-w-50px">#</th>
                    <th class="min-w-125px">{{__("customer")}}</th>
                    <th class="min-w-125px">IP</th>
                    <th class="min-w-125px">Oturum Açılış Tarihi</th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">

                </tbody>
                <!--end::Table body-->
            </table>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
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
                        orderable: !0, targets: 2
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.userSessions.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.showAllList = true
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));
        })
    </script>
@endsection
