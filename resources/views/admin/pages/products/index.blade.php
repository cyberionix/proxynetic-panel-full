@extends("admin.template")
@section("title", __("products"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('products')"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
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
                            <!--begin::Toolbar-->
                            <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                                <!--begin::Add customer-->
                                <a href="{{route("admin.products.create")}}" type="button" class="btn btn-primary"><i
                                        class="fa fa-plus fs-5"></i> {{__("add_:name", ["name" => __("product")])}}</a>
                                <!--end::Add customer-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="dataTable" class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                                <th class="min-w-70px">#</th>
                                <th class="min-w-125px">{{__("product")}}</th>
                                <th class="min-w-125px">{{__("Test Ürünü")}}</th>
                                <th class="min-w-70px"></th>
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
    <!--begin::Modals-->

    <!--end::Modals-->
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
                        orderable: !1, targets: 2
                    },
                    {
                        orderable: !1, targets: 3
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.products.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

                        // SortableJS drag-drop reorder for products
            (function loadSortable(cb){
                if (window.Sortable) return cb();
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                s.onload = cb;
                document.head.appendChild(s);
            })(function(){
                function attachSortable(){
                    var tbody = document.querySelector('#dataTable tbody');
                    if (!tbody || tbody.dataset.sortableAttached) return;
                    tbody.dataset.sortableAttached = '1';
                    Sortable.create(tbody, {
                        animation: 150,
                        handle: '.product-row-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        onEnd: function(){
                            var ids = [];
                            tbody.querySelectorAll('tr').forEach(function(tr, i){
                                var span = tr.querySelector('td:first-child span[data-id]');
                                if (span) {
                                    ids.push(span.getAttribute('data-id'));
                                    span.textContent = (i + 1); // refresh visible position
                                }
                            });
                            if (!ids.length) return;
                            $.ajax({
                                type: 'POST',
                                url: '{{ route("admin.products.reorder") }}',
                                data: { _token: '{{csrf_token()}}', items: ids },
                                success: function(res){
                                    if (res && res.success){
                                        if (window.toastr) toastr.success("{{__('success')}}");
                                    } else {
                                        if (window.toastr) toastr.error((res && res.message) || "{{__('form_has_errors')}}");
                                    }
                                },
                                error: function(xhr){
                                    if (window.toastr) toastr.error('Siralama kaydedilemedi: ' + xhr.status);
                                }
                            });
                        }
                    });
                }
                if (typeof t !== 'undefined' && t && t.on){
                    t.on('draw', function(){ delete document.querySelector('#dataTable tbody').dataset.sortableAttached; attachSortable(); });
                }
                setTimeout(attachSortable, 500);
            });

            $(document).on("click", ".cloneBtn", function () {
                let id = $(this).closest("tr").find("td:first span[data-id]").data("id");
                let url = `{{ route('admin.products.clone', ['product' => '__pid__']) }}`.replace('__pid__', id);
                Swal.fire({
                    icon: 'question',
                    title: 'Ürünü Klonla',
                    text: 'Bu ürünün bir kopyası oluşturulacak ve düzenleme sayfasına yönlendirileceksiniz. Devam edilsin mi?',
                    showConfirmButton: 1,
                    showCancelButton: 1,
                    cancelButtonText: "{{__('close')}}",
                    confirmButtonText: 'Evet, klonla',
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: url,
                            dataType: "json",
                            data: { _token: "{{csrf_token()}}" },
                            success: function (res) {
                                if (res && res.success === true) {
                                    Swal.fire({
                                        title: "{{__('success')}}",
                                        text: 'Ürün klonlandı. Düzenleme sayfasına yönlendiriliyorsunuz...',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false,
                                    }).then(() => {
                                        if (res.data && res.data.redirect) {
                                            window.location.href = res.data.redirect;
                                        } else {
                                            t.draw();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res.message || "{{__('form_has_errors')}}",
                                        icon: 'error',
                                    });
                                }
                            },
                            error: function (xhr) {
                                let msg = (xhr.responseJSON && xhr.responseJSON.message) || ('Hata: ' + xhr.status);
                                Swal.fire({ title: "{{__('error')}}", text: msg, icon: 'error' });
                            }
                        });
                    }
                });
            });

            $(document).on("click", ".deleteBtn", function () {
                let id = $(this).closest("tr").find("td:first span[data-id]").data("id");
                let url = `{{ route('admin.products.destroy', ['product' => '__book_placeholder__']) }}`;
                url = url.replace('__book_placeholder__', id);

                Swal.fire({
                    icon: 'warning',
                    title: "{{__('warning')}}",
                    text: '{{__("are_you_sure_you_want_to_delete_it")}}',
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
                                        text: res.message,
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    })
                                    t.draw();
                                } else {
                                    Swal.fire({
                                        title: "{{__('error')}}",
                                        text: res.message ? res.message : "{{__('form_has_errors')}}",
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
        })
    </script>
@endsection
