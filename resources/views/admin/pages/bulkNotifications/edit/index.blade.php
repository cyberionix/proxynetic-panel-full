@extends("admin.template")
@section("title", __("notifications"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('notifications')"/>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <form action="{{route("admin.bulkNotifications.update", ["bulkNotification" => $bulkNotification->id])}}" id="notificationForm" class="card-body">
                        @csrf
                        <div class="row gap-5">
                            <div class="col-12">
                                <!--begin::Label-->
                                <label class="required form-label">{{__("title")}}</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="title" class="form-control form-control-lg "
                                       value="{{$bulkNotification->title}}" required>
                                <!--end::Input-->
                            </div>
                            <div class="col-12">
                                <!--begin::Label-->
                                <label class="required form-label">İçerik</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea name="message"
                                          class="editorInput form-control mb-2">{!! $bulkNotification->message !!}</textarea>
                                <!--end::Textarea-->
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">{{__("create")}}</span>
                                    <!--end::Indicator label-->
                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    <!--end::Indicator progress-->
                                </button>
                            </div>
                        </div>
                    </form>
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
    <script src="{{assetAdmin("plugins/custom/tinymce/tinymce.bundle.js")}}"></script>
    <script>
        $(document).ready(function () {
            var options = {
                selector: ".editorInput", mode: "textareas",
                height: 550,
                force_br_newlines: false,
                force_p_newlines: false,
                forced_root_block: '',
                plugins: 'code print preview fullpage paste searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount spellchecker  imagetools media  link contextmenu colorpicker textpattern help',
                toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat code',
            };
            tinymce.init(options);

            $(document).on("submit", "#notificationForm", function (e) {
                e.preventDefault()
                let form = $(this);
                $.ajax({
                    type: 'POST',
                    url: form.attr("action"),
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
                                text: res?.message ? res.message : "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                        }
                        propSubmitButton(form.find("button[type='submit']"), 0);
                    }
                })
            })
        })
    </script>
@endsection
