@extends("portal.template")
@section("title", __("my_products_and_services"))
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('my_products_and_services')"/>
@endsection
@section("master")
    <div class="card">
        <div class="card-header">
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
            <div class="card-toolbar">

            </div>
        </div>
        <!--begin::Body-->
        <div class="card-body">
            <!--begin::Table-->
            <table id="dataTable" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-6 gs-0">
                    <th class="min-w-50px">#</th>
                    <th class="min-w-125px">{{__("product")}} / {{__("service")}}</th>
                    <th class="min-w-125px">{{__("amount")}}</th>
                    <th class="min-w-125px">{{__("payment_due_date")}}</th>
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

    <div class="modal fade" id="orderNoteModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered">
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
                    <form id="updateNoteForm" action="">
                        @csrf
                        <div class="fv-row">
                            <!--begin::Label-->
                            <label class="required form-label mb-3">{{__("note")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea type="text" name="note" class="form-control form-control-lg "></textarea>
                            <!--end::Input-->
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

@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var t = $("#dataTable").DataTable({
                order: [],
                columnDefs: [
                    {
                        orderable: !1, targets: 0
                    },
                    {
                        orderable: !1, targets: 1
                    },
                    {
                        orderable: !1, targets: 2
                    },
                    {
                        orderable: !1, targets: 3
                    },
                    {
                        orderable: !1, targets: 4
                    },
                    {
                        orderable: !1, targets: 5
                    }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("portal.orders.ajax", ["user" => auth()->user()->id])}}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

            $(document).on('click','.update-note-button',function () {


                    $('#orderNoteModal').modal('show');
                let id, url;
                id = $(this).attr('data-id');

                url = `{{ route('portal.orders.updateNote', ['order' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

                let currentNote = $(this).attr('data-original-note') ? $(this).attr('data-original-note') : '';
                $('#updateNoteForm textarea[name="note"]').val('')
                $('#updateNoteForm textarea[name="note"]').val(currentNote)
                $('#updateNoteForm').attr('action',url);

            })

            $(document).on("submit", "#updateNoteForm", function (e) {
                e.preventDefault()
                let formData = new FormData(this);

                $.ajax({
                    type: 'POST',
                    url:  $(this).attr('action'),
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
                            $("#orderNoteModal").modal("hide");
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
            })



        })
    </script>
@endsection
