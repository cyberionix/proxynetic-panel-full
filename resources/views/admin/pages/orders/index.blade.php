@extends("admin.template")
@section("title", 'Siparişler')
@section("css")
<style>
@media (max-width: 768px) {
    #kt_app_content_container.container-xxl { max-width: 100% !important; padding-left: 10px !important; padding-right: 10px !important; }
    .card { margin-left: 0; margin-right: 0; }
    .card-body { padding-left: 10px !important; padding-right: 10px !important; }
    .card-header { padding-left: 10px !important; padding-right: 10px !important; flex-direction: column; align-items: flex-start !important; gap: 10px; }
    #header-nav { gap: 2px !important; font-size: 14px; width: 100%; }
    #header-nav .nav-item { flex: 1; text-align: center; }
    #header-nav .nav-link { padding-left: 4px !important; padding-right: 4px !important; white-space: nowrap; }
    .card-toolbar { width: 100%; }
    .card-toolbar .d-flex { flex-wrap: wrap; gap: 5px; }
    #orderBulkBar { flex-wrap: wrap; }
    #ordersTable th.col-hide-mobile,
    #ordersTable td.col-hide-mobile { display: none !important; }
    #ordersTable { font-size: 13px; width: 100% !important; }
    #ordersTable .badge { font-size: 12px; padding: 5px 8px; }
    #ordersTable .btn-sm { font-size: 12px; padding: 5px 10px; }
    .table-responsive { overflow-x: hidden; }
    .card.mb-5.mb-xl-10 { margin-bottom: 8px !important; }
}
</style>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb data="Siparişler"/>
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
                                   data-key="ACTIVE"
                                   href="javascript:void(0);">Aktif</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="CANCELLED"
                                   href="javascript:void(0);">İptal Edildi</a>
                            </li>
                            <!--end:::Tab item-->
                            <!--begin:::Tab item-->
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 statusTab"
                                   data-bs-toggle="tab"
                                   data-key="PENDING"
                                   href="javascript:void(0);">Onay Bekliyor</a>
                            </li>
                            <!--end:::Tab item-->
                        </ul>
                        <!--end:::Tabs-->
                    </div>
                </div>
                <!--end::Navbar-->
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="text" data-table-action="search"
                                       class="form-control  w-250px ps-13"
                                       placeholder="{{__("search_in_table")}}"/>
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div id="orderBulkBar" class="d-none d-flex align-items-center gap-2 me-4">
                                <span class="fw-semibold text-gray-700 me-1"><span id="orderSelectedCount">0</span> seçili</span>
                                <button class="btn btn-sm btn-light-success order-bulk-btn" data-action="mark_active">
                                    <i class="fa fa-check me-1"></i>Aktif Yap
                                </button>
                                <button class="btn btn-sm btn-light-secondary order-bulk-btn" data-action="mark_cancelled">
                                    <i class="fa fa-ban me-1"></i>İptal Et
                                </button>
                                <button class="btn btn-sm btn-light-danger order-bulk-btn" data-action="delete">
                                    <i class="fa fa-trash me-1"></i>Sil
                                </button>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary createOrderBtn"><i
                                        class="fa fa-plus fs-5"></i> {{__("create_:name", ["name" => __("order")])}}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table id="ordersTable" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="w-10px pe-2 col-hide-mobile">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" id="orderCheckAll" />
                                    </div>
                                </th>
                                <th class="m-w-50">#</th>
                                <th>{{__("customer")}}</th>
                                <th>{{__("product")}}</th>
                                <th class="col-hide-mobile">{{__("amount")}}</th>
                                <th class="col-hide-mobile">{{__("date")}}</th>
                                <th>Teslimat</th>
                                <th class="col-hide-mobile">{{__("action")}}</th>
                            </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->

    <div class="modal fade" id="createOrderModal" data-bs-backdrop="static"
         data-bs-keyboard="false" tabindex="-1"
         aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header" id="primaryAlertModal_header">
                    <!--begin::Modal title-->
                    <h2>{{__("create_:name", ["name" => __("order")])}}</h2>
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary"
                         data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body py-lg-10 px-lg-15">
                    <form id="createOrderForm" action="{{route("admin.orders.store")}}">
                    @csrf
                    <!--begin::Scroll-->
                        <div class="scroll-y me-n7 pe-7" id="primaryAlertModal_scroll"
                             data-kt-scroll="true"
                             data-kt-scroll-activate="{default: false, lg: true}"
                             data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#primaryAlertModal_header"
                             data-kt-scroll-wrappers="#primaryAlertModal_scroll"
                             data-kt-scroll-offset="300px">
                            <div class="row g-5">

                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("customer")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.user-select name="user_id"
                                                                       :selectedOption="$selected_user"
                                                                       dropdownParent="#createOrderModal"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("product")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.product-select name="product_id"
                                                                          customClass="productSelection"
                                                                          :withPassives="true"
                                                                          dropdownParent="#createOrderModal"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("start_date")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.date-input name="start_date"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("end_date")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.date-input name="end_date"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("price")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <x-admin.form-elements.select name="price_id"
                                                                  customClass="priceSelection"
                                                                  :hideSearch="true"
                                                                  dropdownParent="#createOrderModal"/>
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">{{__("payment")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="form-check d-flex align-items-center gap-2 mt-2">
                                        <input class="form-check-input h-30px w-30px" type="checkbox" value="1" name="isPaymentWithTransfer" id="flexCheckDefaultTransfer" />
                                        <label class="form-check-label text-gray-700" for="flexCheckDefaultTransfer">
                                            Havele ile ödeme alındı. <i class="fa fa-info-circle" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Seçilen fiyat tutarında ödeme kaydı oluşturulacaktır."></i>
                                        </label>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{__("additional_services")}}</label>
                                    <!--end::Label-->
                                    <!--begin::Additional Services-->
                                    <table id="additionalTable" class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td colspan='2' class='text-center fw-bold text-gray-600'>Ek Hizmet Yok</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <!--end::Additional Services-->
                                </div>
                                <div class="col-12">
                                    <div class="separator"></div>
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">Sipariş Adet</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input class="form-control" name="quantity" value="1" minlength="1">
                                    <!--end::Input-->
                                </div>
                                <div class="col-xl-6">
                                    <!--begin::Label-->
                                    <label class="form-label fw-semibold required">Proxy Teslimat Durumu</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <label class="form-check form-switch form-check-custom form-check-solid mt-2">
                                        <input class="form-check-input" type="checkbox" name="auto_delivery" value="1" checked="checked">
                                        <span class="form-check-label fw-semibold text-muted">Otomatik Teslim Edilsin</span>
                                    </label>
                                    <!--end::Input-->
                                </div>
                            </div>
                            <!--end::Scroll-->
                            <!--begin::Actions-->
                            <div class="d-flex flex-center flex-row-fluid pt-12">
                                <button type="reset" class="btn btn-light me-3"
                                        data-bs-dismiss="modal">{{__("cancel")}}</button>
                                <button type="submit" class="btn btn-primary">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">{{__("save")}}</span>
                                    <!--end::Indicator label-->
                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    <!--end::Indicator progress-->
                                </button>
                            </div>
                            <!--end::Actions-->
                        </div>
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $(document).on("click", ".deleteBtn", function () {
                let id = $(this).closest("tr").find("span[data-id]").attr("data-id"),
                    url = `{{ route('admin.orders.delete', ['order' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: "Siparişi kalıcı olarak silmek istediğinize emin misiniz?",
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}"
                            },
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
                                    });
                                    t.draw();
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
                    }
                });
            })


            @if($selected_user)
            setTimeout(function(){
                $('.createOrderBtn').click();
            },200)
            @endif
            var isMobile = window.innerWidth <= 768;
            var t = $("#ordersTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: false, targets: [0, 7] },
                    { orderable: true, targets: [1, 2, 3, 4, 5, 6] },
                    { className: 'col-hide-mobile', targets: [0, 4, 5, 7] }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.orders.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.showAllList = true
                        d.status = $(".statusTab.active").data("key")
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('#orderCheckAll').prop('checked', false);
                orderUpdateBulk();
                if (window.innerWidth <= 768) {
                    $('#ordersTable tbody tr').css('cursor', 'pointer');
                }
            });

            if (window.innerWidth <= 768) {
                $(document).on('click', '#ordersTable tbody td', function(e) {
                    if ($(e.target).is('a, button, input') || $(e.target).closest('a, button, .form-check').length) return;
                    var link = $(this).closest('tr').find('a.badge').attr('href');
                    if (link) window.location.href = link;
                });
            }

            function orderGetIds() {
                var ids = [];
                $('#ordersTable .bulk-check-order:checked').each(function() { ids.push($(this).val()); });
                return ids;
            }
            function orderUpdateBulk() {
                var ids = orderGetIds();
                $('#orderSelectedCount').text(ids.length);
                ids.length > 0 ? $('#orderBulkBar').removeClass('d-none') : $('#orderBulkBar').addClass('d-none');
            }
            $(document).on('change', '#orderCheckAll', function() {
                $('#ordersTable .bulk-check-order').prop('checked', $(this).is(':checked'));
                orderUpdateBulk();
            });
            $(document).on('change', '#ordersTable .bulk-check-order', function() {
                if (!$(this).is(':checked')) $('#orderCheckAll').prop('checked', false);
                orderUpdateBulk();
            });
            $(document).on('click', '.order-bulk-btn', function() {
                var action = $(this).data('action');
                var ids = orderGetIds();
                if (ids.length === 0) return;
                var msgs = {
                    'mark_active': ids.length + ' siparişi aktif olarak işaretlemek istediğinize emin misiniz?',
                    'mark_cancelled': ids.length + ' siparişi iptal etmek istediğinize emin misiniz?',
                    'delete': ids.length + ' siparişi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.'
                };
                Swal.fire({
                    title: 'Toplu İşlem',
                    text: msgs[action] || 'Emin misiniz?',
                    icon: action === 'delete' ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, uygula',
                    cancelButtonText: 'Vazgeç',
                    confirmButtonColor: action === 'delete' ? '#dc3545' : '#3085d6',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.orders.bulkAction') }}",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}", ids: ids, action: action },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({ title: 'Başarılı', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
                                    t.draw();
                                } else {
                                    Swal.fire({ title: 'Hata', text: res.message, icon: 'error' });
                                }
                            }
                        });
                    }
                });
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));
            $(document).on("click", ".statusTab", function () {
                t.draw();
            })

            $(document).on("click", ".createOrderBtn", function () {
                $("#createOrderModal").modal("show");
            })

            $(document).on("select2:select", '.productSelection', function (e) {
                let additionalServiceArea = $("#additionalTable"),
                    extraParams = e.params.data.extraParams,
                    attributes,
                    body = additionalServiceArea.find("tbody");

                $(".priceSelection").val("").trigger("change")

                if (extraParams.attrs.length > 0) {
                    attributes = extraParams.attrs.filter((item) => {
                        return item.service_type === "protocol_select";
                    })

                    body.html("")
                    attributes.map((item) => {
                        body.append("<tr>" +
                            "<td>" + item.label + "</td>" +
                            "<td>" + drawFormElement(item) + "</td>" +
                            "</tr>")
                    })
                } else {
                    body.html("")
                    body.append("<tr>" +
                        "<td colspan='2' class='text-center fw-bold text-gray-600'>Ek Hizmet Yok</td>" +
                        "</tr>")
                }
            })

            $(".priceSelection").select2({
                placeholder: "{{__(":name_selection", ["name" => __("district")])}}",
                allowClear: true,
                minimumResultsForSearch: Infinity,
                tags: false,
                language: {
                    searching: function () {
                        return "{{__("searching")}}...";
                    },
                    "noResults": function () {
                        return "{{__("result_not_found")}}";
                    },
                    "errorLoading": function () {
                        return 'Ürün seçiniz';
                    }
                },
                ajax: {
                    url: "{{route("admin.prices.searchByProduct")}}",
                    type: "GET",
                    dataType: 'json',
                    quietMillis: 50,
                    data: function (term) {
                        return {
                            _token: "{{csrf_token()}}",
                            term: term,
                            product_id: $(".productSelection").val()
                        };
                    },
                    processResults: function (data) {
                        var res = data.items.map(function (item) {
                            return {
                                id: item.id,
                                text: item.name
                            };
                        });
                        return {
                            results: res
                        };
                    }
                }
            });

            $(document).on("submit", "#createOrderForm", function (e) {
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
                            alerts.success.fire({
                                text: res?.message ?? ""
                            }).then((r) => window.location.href = '{{route('admin.orders.index')}}')
                        } else {
                            alerts.error.fire({
                                text: res?.message ?? "",
                            })
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })

        })
    </script>
@endsection
