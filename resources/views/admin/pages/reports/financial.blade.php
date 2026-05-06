@extends("admin.template")
@section("title", "Finansal Raporlar")
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title BreadCrumb-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        Finansal Raporlar</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{route("admin.dashboard")}}"
                               class="text-muted text-hover-primary">ProxyNetic</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-500 w-5px h-2px"></span>
                        </li>

                        <li class="breadcrumb-item text-gray-900">Finansal Raporlar</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title BreadCrumb-->
            </div>

            <!--end::Toolbar container-->
        </div>

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">

                <div class="card card-flush mb-10" data-np-sale-report="area">
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-3">
                                    <label class="form-label">Tarih Aralığı</label>
                                    <input class="form-control" name="date_range" placeholder="" id="dtrange"/>
                                </div>
                                <div class="col-3">
                                    <label class="form-label">Ürün Kategorisi</label>
                                    <select name="product_category_id" data-control="select2" id=""
                                            class="form-control">
                                        <option value="">Tümü</option>
                                        @foreach($product_categories as $product_category)
                                            <option
                                                value="{{$product_category->id}}">{{$product_category->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-3">
                                    <label class="form-label">Ürün</label>
                                    <select name="product_id" data-control="select2" id="" class="form-control">
                                        <option value="">Tümü</option>
                                        @foreach($products as $product)
                                            <option value="{{$product->id}}">{{$product->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label class="form-label mb-2 opacity-0">Filtrele</label>
                                    <button type="submit" class="btn btn-primary form-control">Filtrele</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                @if($submit)
                    <div class="mb-10 row g-5 justify-content-center">
                        <div class="col-xl-4 position-relative">
                            <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3 d-none"
                                 data-np-sale-report="loader">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </div>
                            <div class="card card-flush" data-np-sale-report="area">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <!--begin::Title-->
                                    <div class="card-title d-flex flex-column">
                                        <!--begin::Info-->
                                        <!--begin::Subtitle-->
                                        <h3 class="text-dark pt-1 fw- ">Ödenen Faturalar</h3>
                                        <!--end::Subtitle-->
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Header-->

                                <!--begin::Card body-->
                                <div class="card-body pt-2 pb-4">
                                    <!--begin::Labels-->
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                            <div class="text-gray-500">Ürün Sayısı</div>
                                        </div>
                                        <div class="fw-bolder text-gray-700 text-end fs-5">

                                            <span data-np-sale-report="this-month">{{$report['invoice']['count']}} ad</span>
                                        </div>
                                    </div>
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                            <div class="text-gray-500">Ara Toplam</div>
                                        </div>
                                        <div class="fw-bolder text-gray-700 text-end fs-5">

                                            <span data-np-sale-report="last-month">{{$report['invoice']['draw_total_price']}}</span></div>
                                    </div>
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                            <div class="text-gray-500">KDV Dahil Toplam</div>
                                        </div>
                                        <div class="fw-bolder text-gray-700 text-end fs-5">

                                            <span data-np-sale-report="last-month">{{$report['invoice']['draw_total_price_with_vat']}}</span></div>
                                    </div>
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            &nbsp;
                                        </div>
                                    </div>
                                    <!--end::Labels-->
                                </div>
                                <!--end::Card body-->
                            </div>
                        </div>
                        <div class="col-xl-4 position-relative">
                            <div class="d-flex flex-center position-absolute top-50 start-50 z-index-3 d-none"
                                 data-np-sale-report="loader">
                                <span class="spinner-border spinner-border-sm align-middle"></span>
                            </div>
                            <div class="card card-flush" data-np-sale-report="area">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <!--begin::Title-->
                                    <div class="card-title d-flex flex-column">
                                        <!--begin::Info-->
                                        <!--begin::Subtitle-->
                                        <h3 class="text-dark pt-1 fw- ">Ödenen Faturalar</h3>
                                        <!--end::Subtitle-->
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Header-->

                                <!--begin::Card body-->
                                <div class="card-body pt-2 pb-4">
                                    <!--begin::Labels-->
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                            <div class="text-gray-500">Ödeme Sayısı</div>
                                        </div>
                                        <div class="fw-bolder text-gray-700 text-end fs-5">

                                            <span data-np-sale-report="this-month">{{$report['checkout']['count']}} ad</span>
                                        </div>
                                    </div>
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                            <div class="text-gray-500">Toplam Ödenen</div>
                                        </div>
                                        <div class="fw-bolder text-gray-700 text-end fs-5">

                                            <span data-np-sale-report="last-month">{{$report['checkout']['sum_draw']}}</span></div>
                                    </div>
                                    <div class="d-flex fw-semibold align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            &nbsp;
                                        </div>
                                    </div>
                                    <!--end::Labels-->
                                </div>
                                <!--end::Card body-->
                            </div>
                        </div>
                    </div>


                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-6">
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
                            <table class="table g-3 gs-0 mb-0 fw-bold text-gray-700" id=""
                                   data-kt-element="items">
                                <!--begin::Table head-->
                                <thead>
                                <tr class="border-bottom fs-6 fw-bold text-gray-700">
                                    <th class="x">Müşteri</th>
                                    <th class="x">{{__("product")}}/{{__("service")}}</th>
                                    <th class="x">{{__("price")}}</th>
                                    <th class="x">{{__("vat")}}</th>
                                    <th class="x">{{__("amount")}}</th>
                                    <th class=""></th>
                                </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody>
                                @foreach($report['invoice_items'] as $item)
                                    <tr class="border-bottom border-bottom-dashed"
                                        data-kt-element="item">
                                        <td>
                                            <div class="mt-3 fs-6 d-flex align-items-center gap-2">

                                            <span class="badge badge-light">
                                                <a target="_blank" href="{{route('admin.users.show',['user'=> $item->invoice_user->id])}}">{{$item->invoice_user->full_name}}</a>
                                            </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="mt-3 fs-6 d-flex align-items-center gap-2">
                                                {{$item->name}}
                                                @if($item->order_id)
                                                    - <a target="_blank"
                                                         href="{{route("admin.orders.show", ["order" => $item->order_id])}}">#{{$item->order_id}}</a>
                                                @endif
                                                <span
                                                    class="badge badge-primary badge-sm">{{__("invoice_item_types.".mb_strtolower($item->type))}}</span>
                                            </div>
                                            <input type="hidden" name="invoice_item[id][]"
                                                   value="{{$item->id}}">
                                        </td>
                                        <td>
                                            <div class="w-125px">
                                                <input disabled
                                                    class="form-control  text-end priceInput"
                                                    data-kt-element="price"
                                                    value="{{showBalance($item->total_price)}}"
                                                    name="invoice_item[price][]" placeholder="0,00"
                                                    type="text">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="w-75px">
                                                <select disabled name="invoice_item[vat_percent][]"
                                                        class="form-select "
                                                        data-kt-element="vat_percent">
                                                    @foreach(getVats() as $vat)
                                                        <option
                                                            value="{{$vat}}" {{$vat == $item->vat_percent ? "selected" : ""}}>{{$vat}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group mb-5">
                                                <span class="input-group-text">{{defaultCurrencySymbol()}}</span>
                                                <input disabled
                                                    class="form-control priceInput"
                                                    data-kt-element="total"
                                                    value="{{showBalance($item->total_price_with_vat)}}"
                                                    name="invoice_item[amount][]" placeholder="0,00"
                                                    type="text">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="mt-1">
                                                <button type="button"
                                                        class="btn btn-sm btn-icon btn-active-color-primary d-none"
                                                        data-kt-element="remove-item">
                                                    <i class="ki-duotone ki-trash fs-3 ">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                    </i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <!--end::Table body-->
                                <!--begin::Table foot-->
                            </table>

                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                @endif
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
            var start = moment().subtract(29, "days");
            var end = moment();

            function cb(start, end) {
                $("#dtrange").html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));
            }

            $("#dtrange").daterangepicker({
                startDate: start,
                endDate: end,

                locale: {
                    format: "DD/MM/YYYY"
                },
                ranges: {
                    "Bugün": [moment(), moment()],
                    "Dün": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    "Son 7 Gün": [moment().subtract(6, "days"), moment()],
                    "Son 30 Gün": [moment().subtract(29, "days"), moment()],
                    "Bu Ay": [moment().startOf("month"), moment().endOf("month")],
                    "Geçen Ay": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")],
                    "Bu Yıl": [moment().startOf("year"), moment().endOf("year")]
                }
            }, cb);

            cb(start, end);
            {{--var t = $("#logsTable").DataTable({--}}
            {{--    order: [],--}}
            {{--    columnDefs: [--}}
            {{--        {--}}
            {{--            orderable: !0, targets: 0--}}
            {{--        },--}}
            {{--        {--}}
            {{--            orderable: !0, targets: 1--}}
            {{--        },--}}
            {{--        {--}}
            {{--            orderable: !0, targets: 2--}}
            {{--        },--}}
            {{--        {--}}
            {{--            orderable: !0, targets: 3--}}
            {{--        },--}}
            {{--        {--}}
            {{--            orderable: !0, targets: 4--}}
            {{--        },--}}
            {{--        {--}}
            {{--            orderable: !0, targets: 5--}}
            {{--        }--}}
            {{--    ],--}}
            {{--    "processing": true,--}}
            {{--    "serverSide": true,--}}
            {{--    "ajax": {--}}
            {{--        "url": "{{ route("admin.reports.financialAjax") }}",--}}
            {{--        "type": "POST",--}}
            {{--        "data": function (d) {--}}
            {{--            d.date_range = $(".date_range").val()--}}
            {{--        },--}}
            {{--    },--}}
            {{--}).on("draw", function () {--}}
            {{--    KTMenu.createInstances();--}}
            {{--});--}}

            // document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
            // t.search(e.target.value).draw();
            // }));

            $(document).on("click", ".statusTab", function () {
                // t.draw();
            })
        })
    </script>
@endsection
