@extends("admin.template")
@section("title", __("product_categories"))
@section("css")
    <link href="{{asset('custom/jstree/jstree.bundle.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        .loading{
            filter: blur(3px);
            pointer-events: none;
        }
    </style>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('product_categories')"/>
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

                        </div>
                        <!--begin::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Toolbar-->
                            <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                                <!--begin::Add-->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addCategoryModal"><i
                                        class="fa fa-plus fs-5"></i> {{__("add_:name", ["name" => __("product_category")])}}
                                </button>
                                <!--end::Add-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0 mt-8">
                        <div class="fw-row position-relative min-h-300px d-flex flex-center loader">
                            <div class='position-absolute z-index-3 d-flex flex-center flex-column gap-4 w-100'>
                                <div class='spinner-border'></div>
                                <div class="text-gray-800 fw-bold">{{__("loading")}}..</div>
                            </div>
                        </div>
                        <div class="row mainBody" style="display: none">
                            <div class="col-xl-4 border-1 border-end">
                                <div id="jstree">
                                    <ul class="draggable-zone">
                                        @foreach($categories as $category)
                                            @if($category->children)
                                                <li data-id="{{$category->id}}">{{$category->name}}
                                                    <ul>
                                                        @foreach($category->children as $subCategory)
                                                            <li data-id="{{$subCategory->id}}" {{$loop->first ? 'id="child_node_1'.$subCategory->id.'"' : '' }}>{{$subCategory->name}}</li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @else
                                                <li class="draggable" >{{$category->name}}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-8 ps-10 position-relative" data-np-edit="container">
                                <div class="position-sticky" style="top: 100px;">
                                    <div class="d-flex justify-content-between">
                                        <h3 class="fw-bold my-2">{{__("edit_:name", ["name" => __("product_category")])}}
                                            (<span data-np-edit="name"></span>)</h3>
                                        <button class="btn btn-light-danger btn-sm deleteBtn">
                                        <span class="svg-icon svg-icon-2">
                                            <i class="fa fa-trash"></i>
                                        </span>
                                        </button>
                                    </div>
                                    <hr>
                                    <form id="editCategoryForm"
                                          action="{{route("admin.products.categories.update", ["productCategory" => "__placeholder__"])}}"
                                          data-find-url="{{route("admin.products.categories.find", ["productCategory" => "__placeholder__"])}}">
                                        @csrf
                                        <input type="hidden" name="id">
                                        <div class="fv-row mb-5">
                                            <!--begin::Label-->
                                            <label class="required form-label">{{__("title")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" name="name"
                                                   class="form-control  form-control-lg" required>
                                            <!--end::Input-->
                                        </div>
                                        <div class="fv-row mb-5">
                                            <!--begin::Label-->
                                            <label class="required form-label">Sıra</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" min="1" max="999" name="seq"
                                                   class="form-control  form-control-lg" required>
                                            <!--end::Input-->
                                        </div>
                                        <div class="fv-row mb-5">
                                            <!--begin::Label-->
                                            <label class="form-label">{{__("up_category")}}</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <x-admin.form-elements.select name="parent_id"
                                                                          allowClear="true"
                                                                          :options="$upCategoryOptions"/>
                                            <!--end::Input-->
                                        </div>
                                        <div class="fw-row text-end">
                                            <button type="submit" class="btn btn-primary">
                                                <!--begin::Indicator label-->
                                                <span class="indicator-label">{{__("save_changes")}}</span>
                                                <!--end::Indicator label-->
                                                <!--begin::Indicator progress-->
                                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                <!--end::Indicator progress-->
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>{{__("add_:name", ["name" => __("product_category")])}}</h2>
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
                    <form id="addCategoryForm" action="{{route("admin.products.categories.store")}}">
                        @csrf
                        <div class="fw-row mb-5">
                            <!--begin::Label-->
                            <label class="required form-label">{{__("title")}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="name" class="form-control  form-control-lg"
                                   required>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-5">
                            <!--begin::Label-->
                            <label class="required form-label">Sıra</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" min="1" max="999" name="seq"
                                   class="form-control  form-control-lg" required>
                            <!--end::Input-->
                        </div>
                        <div class="fw-row mb-5">
                            <!--begin::Label-->
                            <label class="form-label">{{__("up_category")}}</label>
                            <!--end::Label-->
                            <!--begin::Select-->
                            <x-admin.form-elements.select name="parent_id"
                                                          allowClear="true"
                                                          dropdownParent="#addCategoryModal"
                                                          :options="$upCategoryOptions"/>
                            <!--end::Select-->
                        </div>
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
    <script src="{{asset("custom/jstree/jstree.bundle.js")}}"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <script>
        $(document).ready(function () {

            function disabledOptionUpCategory(children) {
                children.map((child) => {
                    $("#editCategoryForm [name='parent_id']").find("option[value='" + child.id + "']").prop("disabled", true)
                })
            }

            $('#jstree')
                .bind('ready.jstree', function (e, data) {
                    $('#jstree .jstree-anchor:first').trigger("click")
                    setTimeout(() => {
                        $(".loader").addClass("d-none")
                        $(".mainBody").fadeIn(50)
                    }, 200)
                })
                .jstree({
                    "core": {
                        "themes": {
                            "responsive": true
                        }
                    },
                    "types": {
                        "default": {
                            "icon": "ki-outline ki-message-text-2 text-primary fs-7",
                        }
                    },
                    "plugins": ["types"]
                })

            $(document).on("click", ".jstree-anchor", function () {
                let id = $(this).closest("li").data("id"),
                    form = $("#editCategoryForm"),
                    url = form.data("find-url").replace('__placeholder__', id);

                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    beforeSend: function (){
                      $('[data-np-edit="container"]').addClass("loading");
                    },
                    complete: function (data, status) {
                        res = data.responseJSON;
                        if (res && res.success === true) {
                            $("[data-np-edit='name']").text(res.data.name);
                            form.find("[name='id']").val(res.data.id);
                            form.find("[name='name']").val(res.data.name);
                            form.find("[name='seq']").val(res.data.seq ? res.data.seq : 999);
                            form.find("[name='parent_id']").val(res.data.parent_id).trigger("change");

                            form.find("[name='parent_id']").find("option").prop("disabled", false)
                            form.find("[name='parent_id']").find("option[value='" + res.data.id + "']").prop("disabled", true)
                            if (res.data.children.length > 0) {
                                disabledOptionUpCategory(res.data.children)
                            }
                            $('[data-np-edit="container"]').removeClass("loading");
                        } else {
                            toastr.error("{{__("an_error_occurred")}}");
                        }
                    }
                })
            })

            $('#jstree li:first').trigger("click")
            $(document).on("submit", "#addCategoryForm", function (e) {
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
                        propSubmitButton(form.find("[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton(form.find("[type='submit']"), 0);

                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => window.location.reload())
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
            $(document).on("submit", "#editCategoryForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    id = form.find("[name='id']").val(),
                    url = form.attr("action").replace('__placeholder__', id);

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: new FormData(this),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(form.find("[type='submit']"), 1);
                    },
                    complete: function (data, status) {
                        propSubmitButton(form.find("[type='submit']"), 0);

                        res = data.responseJSON;
                        if (res && res.success === true) {
                            Swal.fire({
                                title: "{{__('success')}}",
                                text: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => window.location.reload())
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
            $(document).on("click", ".deleteBtn", function () {
                let id = $("#editCategoryForm [name='id']").val(),
                    url = `{{ route('admin.products.categories.delete', ['productCategory' => '__placeholder__']) }}`;
                url = url.replace('__placeholder__', id);

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
                                        text: res?.message ?? "",
                                        icon: "success",
                                        showConfirmButton: 0,
                                        showCancelButton: 1,
                                        cancelButtonText: "{{__('close')}}"
                                    }).then((r) => window.location.reload())
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
        })
    </script>
@endsection
