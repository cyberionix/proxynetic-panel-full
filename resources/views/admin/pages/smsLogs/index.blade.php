@extends("admin.template")
@section("title", "Sms Kayıtları")
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('Tüm Smsler')"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Navbar-->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body py-0">
                        <!--begin:::Tabs-->
                        <ul id="header-nav"
                            class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mt-3 gap-8">
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab active"
                                   data-bs-toggle="tab"
                                   data-key=""
                                   href="javascript:void(0);">{{__("all")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="SUCCESS"
                                   href="javascript:void(0);">{{__("success")}}</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="PENDING"
                                   href="javascript:void(0);">Bekliyor</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="ERROR"
                                   href="javascript:void(0);">{{__("error")}}</a>
                            </li>
                            <!--end:::Tab item-->
                        </ul>
                        <!--end:::Tabs-->
                    </div>
                </div>
                <!--end::Navbar-->
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
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">

                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="logsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="m-w-50">#</th>
                                <th class="min-w-125px">{{__("customer")}}</th>
                                <th class="min-w-125px">Mesaj</th>
                                <th class="min-w-125px">{{__("phone_number")}}</th>
                                <th class="min-w-125px">{{__("date")}}</th>
                                <th class="min-w-125px">{{__("status")}}</th>
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
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var t = $("#logsTable").DataTable({
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
                        orderable: !0, targets: 5
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.smsLogs.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.showAllList = true
                        d.status = $(".statusTab.active").data("key")
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".statusTab", function () {
                t.draw();
            })
        })
    </script>
@endsection
