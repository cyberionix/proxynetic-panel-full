@extends("admin.template")
@section("title", __("Kupon Kodları"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
<x-admin.bread-crumb :data="__('Kupon Kodları')"/>
@endsection
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
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-primary addBtn"><i class="fa fa-plus fs-5"></i> {{__("add_:name", ["name" => __("Kupon Kodu")])}}</button>
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
            <table id="customerGroupsTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="m-w-50">#</th>
                    <th class="min-w-125px">{{__("Kod")}}</th>
                    <th class="min-w-125px">{{__("İndirim Tutarı")}}</th>
                    <th class="min-w-125px">{{__("Son Kullanım Tarihi")}}</th>
                    <th class="min-w-125px">{{__("Durum")}}</th>
                    <th class="min-w-125px"></th>
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

    <!--begin::Modals-->
    <div class="modal fade" id="primaryGroupModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>{{__("create")}}</h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
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
                    <form id="couponCodeForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="fv-row">
                            <!--begin::Label-->
                            <label class="required form-label mb-3">{{__("Kod")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="coupon_code" class="form-control form-control-lg " required>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row row mt-4">
                           <div class="col">
                               <!--begin::Label-->
                               <label class="required form-label mb-3">{{__("İndirim Tipi")}}</label>
                               <!--end::Label-->
                               <!--begin::Input-->
                               <select class="form-control typeSelectArea" required name="type">
                                   <option value="">Lütfen Seçin</option>
                                   <option value="PERCENT">Yüzde</option>
                                   <option value="FIXED">Sabit (TL)</option>
                               </select>
                               <!--end::Input-->
                           </div>
                            <div class="col">
                                <!--begin::Label-->
                                <label class="required form-label mb-3">{{__("İndirim Miktarı")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" name="amount" class="form-control form-control-lg " required>
                                <!--end::Input-->
                            </div>
                        </div>
                       <div class="row mt-4">
                           <div class="col">
                               <!--begin::Label-->
                               <label class="required form-label mb-3">Sadece İlk Sipariş</label>
                               <!--end::Label-->
                               <!--begin::Input-->
                               <select class="form-control " required name="only_new_users">
                                   <option value="">Lütfen Seçin</option>
                                   <option value="1">Evet</option>
                                   <option value="0">Hayır</option>
                               </select>
                               <!--end::Input-->
                           </div>
                           <div class="col">
                               <!--begin::Label-->
                               <label class="fs-6 fw-bold form-label">
                                   <span class="">Geçerli Ürünler</span>
                               </label>
                               <!--end::Label-->
                               <!--begin::Input-->
                               <select data-control="select2" data-placeholder="Tümü" class="form-control " multiple  name="product_ids[]">
                                   @foreach($products as $product)
                                       <option value="{{$product->id}}">{{$product->name}}</option>
                                   @endforeach
                               </select>
                               <!--end::Input-->
                           </div>
                       </div>
                       <div class="row mt-4">
                           <div class="col">
                               <!--begin::Label-->
                               <label class="required form-label mb-3">Kullanım Limiti</label>
                               <!--end::Label-->
                               <!--begin::Input-->
                               <input type="number" name="use_limit" class="form-control form-control-lg " required>
                               <!--end::Input-->
                           </div>
                           <div class="col">
                               <!--begin::Label-->
                               <label class="required form-label mb-3">Son Kullanım Tarihi</label>
                               <!--end::Label-->
                               <!--begin::Input-->
                               <input type="text" name="end_date" class="form-control form-control-lg dateInput" required>
                               <!--end::Input-->
                           </div>
                       </div>
                        <div class="fv-row mt-4">
                            <div class="col">
                                <!--begin::Label-->
                                <label class="required form-label mb-3">Aktif/Pasif</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select class="form-control " required name="is_active">
                                    <option value="1">Aktif</option>
                                    <option value="0">Pasif</option>
                                </select>
                                <!--end::Input-->
                            </div>
                        </div>
                        <!--begin::Actions-->
                        <div class="d-flex flex-center flex-row-fluid pt-12">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{__("cancel")}}</button>
                            <button type="submit" class="btn btn-primary" id="user_group_submit_btn">
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
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modals-->
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var t = $("#customerGroupsTable").DataTable({
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
                        orderable: !1, targets: 3
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.couponCodes.ajax") }}",
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

            $(document).on("click", ".addBtn", function () {
                $("#couponCodeForm [name='url']").val("{{route('admin.couponCodes.store')}}")
                $("#couponCodeForm [name='groupName']").val("")
                $("#primaryGroupModal .modal-header h2").html("{{__("create")}}")
                $("#primaryGroupModal").modal("show")
            })
            $(document).on("click", ".editBtn", function () {

                let id = $(this).closest("tr").find("td:first span").data("id");

                $("#couponCodeForm [name='url']").val("{{route('admin.couponCodes.update')}}")
                $("#couponCodeForm [name='id']").val($(this).closest("tr").find("td:first span").data("id"))

                $.ajax({
                    type: "POST",
                    url: "{{route('admin.couponCodes.find')}}",
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}",
                        id: id
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            $("#couponCodeForm [name='groupName']").val($(this).closest("tr").find("td:nth-child(2)").html())
                            $("#primaryGroupModal .modal-header h2").html("{{__("edit")}}")
                            $("#primaryGroupModal").modal("show")

                            $.each(res.data,function(ind,item){
                                if($('#couponCodeForm').find('[name="'+ind+'"]').length > 0){
                                    $('#couponCodeForm').find('[name="'+ind+'"]').val(item).trigger('change')
                                }else if($('#couponCodeForm').find('[name="'+ind+'[]"]').length > 0){
                                    $('#couponCodeForm').find('[name="'+ind+'[]"]').val(item).trigger('change')
                                }
                            })




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




            })
            $(document).on("click", ".deleteBtn", function () {
                let id = $(this).closest("tr").find("td:first span").data("id");
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
                            url: "{{route('admin.couponCodes.delete')}}",
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                id: id
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

            $(document).on("submit", "#couponCodeForm", function (e) {
                e.preventDefault()
                let formData = new FormData(this);
                formData.delete("url");

                $.ajax({
                    type: 'POST',
                    url:  $("#couponCodeForm [name='url']").val(),
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function (){
                        propSubmitButton($("#user_group_submit_btn"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton($("#user_group_submit_btn"), 0);
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
                            $("#primaryGroupModal").modal("hide");
                            $("#couponCodeForm [name='groupName']").val("");
                            $("#couponCodeForm [name='url']").val("");
                            $("#couponCodeForm [name='id']").val("");
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
            })
        })
    </script>
@endsection
