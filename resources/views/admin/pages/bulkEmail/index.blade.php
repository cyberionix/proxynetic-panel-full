@extends("admin.template")
@section("title", __("bulk_email"))
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <x-admin.bread-crumb :data="__('bulk_email')"/>
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
                            <h2 class="card-title fw-bolder">{{__("bulk_email")}}</h2>
                        </div>
                        <!--begin::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <form action="{{route("admin.bulkEmail.send")}}" id="sendEmailForm" class="card-body pt-0">
                        @csrf
                        <div class="row">
                            <!--begin::Col-->
                            <div class="col-xl-6">
                                <!--begin::Option-->
                                <input type="radio" class="btn-check" name="type" value="userFilter" checked="checked"
                                       id="userFilterBtn"/>
                                <label
                                    class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center mb-10"
                                    for="userFilterBtn">
                                    <i class="ki-duotone ki-filter fs-3x me-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <!--begin::Info-->
                                    <span class="d-block fw-semibold text-start text-gray-900 fw-bold d-block fs-3">Müşterileri Filtrele</span>
                                    <!--end::Info-->
                                </label>
                                <!--end::Option-->
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-xl-6">
                                <!--begin::Option-->
                                <input type="radio" class="btn-check" name="type" value="selectUser"
                                       id="selectUserBtn"/>
                                <label
                                    class="btn btn-outline btn-outline-dashed btn-active-light-primary p-7 d-flex align-items-center mb-10"
                                    for="selectUserBtn">
                                    <i class="ki-duotone ki-profile-user fs-3x me-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    <!--begin::Info-->
                                    <span class="d-block fw-semibold text-start text-gray-900 fw-bold d-block fs-3">Müşteri Seç</span>
                                    <!--end::Info-->
                                </label>
                                <!--end::Option-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <div id="userFilterArea">
                            <div class="fw-bolder fs-4 required">Müşterileri Filtrele</div>
                            <hr>
                            <div class="row">
                                <div class="col-xl-3">
                                    <label class="form-label fs-6 fw-bold">Danışmanlık Durumu:</label>
                                    <x-admin.form-elements.select name="statusFilter"
                                                                  placeholder="Tümü"
                                                                  allowClear="true"
                                                                  :hideSearch="true"
                                                                  :options="[
                                                                    ['label' => 'Aktif Danışan', 'value' => 'ACTIVE'],
                                                                    ['label' => 'Pasif Danışan', 'value' => 'PASSIVE']
                                                                  ]"/>
                                </div>
                                <div class="col-xl-3">
                                    <label class="form-label fs-6 fw-bold">Kaçıncı Aydaki Müşteriler:</label>
                                    <x-admin.form-elements.select name="monthFilter"
                                                                  placeholder="Tümü"
                                                                  allowClear="true"
                                                                  :hideSearch="true"
                                                                  :options="$monthFilterOptions"/>
                                </div>
                                <div class="col-xl-3">
                                    <label class="form-label fs-6 fw-bold">{{__("city")}}:</label>
                                    <x-admin.form-elements.city-select placeholder="Tümü"
                                                                       allowClear="true"
                                    />
                                </div>
                                <div class="col-xl-3">
                                    <label class="form-label fs-6 fw-bold">{{__("district")}}:</label>
                                    <x-admin.form-elements.district-select placeholder="Tümü"
                                                                           allowClear="true"
                                    />
                                </div>
                            </div>
                        </div>
                        <div id="selectUserArea" style="display: none;">
                            <div class="fw-bolder fs-4 required">Müşteri Seç</div>
                            <hr>
                            <div>
                                <label
                                    class="form-label fs-6 fw-bold">{{__(":name_selection", ['name' => __("customer")])}}
                                    :</label>
                                <x-admin.form-elements.user-select name="user_id[]"
                                                                   placeholder="Tümü"
                                                                   allowClear="true"
                                                                   customAttr="multiple"/>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="fw-bolder fs-4 required">{{__("mail_subject")}}</div>
                            <hr>
                            <div>
                                <textarea data-kt-autosize="true" rows="1" name="mailSubject"
                                          class="form-control  mb-2" placeholder=""
                                          required></textarea>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="fw-bolder fs-4 required">E-posta İçeriği</div>
                            <hr>
                            <div>
                                <textarea name="mail_content"
                                          class="editorInput form-control mb-2"><style>html,body { padding: 0; margin:0; }</style>
<div style="font-family:Arial,Helvetica,sans-serif; line-height: 1.5; font-weight: normal; font-size: 15px; color: #2F3044; min-height: 100%; margin:0; padding:0; width:100%; background-color:#edf2f7">
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:0 auto; padding:0; max-width:800px">
		<tbody>
			<tr>
				<td align="center" valign="center" style="text-align:center; padding: 30px">
					<a href="{{ url('/') }}" rel="noopener" target="_blank">
						<img style="width: 125px;border-radius:50%" alt="Logo" src="{{ url(brand('logo')) }}" />
					</a>
				</td>
			</tr>
			<tr>
				<td align="left" valign="center">
					<div style="text-align:left; margin: 0 20px; padding: 40px; background-color:#ffffff; border-radius: 6px">
						<!--begin:Email content-->
						<div style="padding-bottom: 30px; font-size: 17px;">
							<strong>{{ brand('name') }}</strong>
						</div>
						<div style="padding-bottom: 30px">Sevgili <b> ........ </b></div>
						<div style="padding-bottom: 30px">

                        <div style="padding-bottom: 40px; text-align:center;">
							<a href="{{ url('/') }}" rel="noopener" style="text-decoration:none;display:inline-block;text-align:center;padding:0.75575rem 1.3rem;font-size:0.925rem;line-height:1.5;border-radius:0.35rem;color:#ffffff;background-color:#009EF7;border:0px;margin-right:0.75rem!important;font-weight:600!important;outline:none!important;vertical-align:middle" target="_blank">
							    Örnek Buton
							</a>
						</div>

                            Güzel bir gün geçirmeniz dileğiyle!
						<div style="padding-bottom: 10px">Sevgiler,
						<br>Melody & Akın BÜYÜKKARACA
						<br></div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div></textarea>
                            </div>
                        </div>
                        <div class="mt-8">
                            <button type="submit" class="btn btn-success w-100 mb-3 showUsersBtn"
                                    data-url="{{route("admin.bulkEmail.showUsers")}}">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">Alıcıları Gör</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                            <button type="submit" class="btn btn-primary w-100">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">E-Posta Gönder</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
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

            $(document).on("change", "#sendEmailForm [name='type']", function () {
                let value = $(this).val();
                if (value === "userFilter") {
                    $("#selectUserArea").hide();
                    $("#userFilterArea").fadeIn();
                } else {
                    $("#userFilterArea").hide();
                    $("#selectUserArea").fadeIn();
                }
            })

            $(document).on("click", ".showUsersBtn", function (e) {
                $(this).addClass("selected")
            })
            $(document).on("submit", "#sendEmailForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    showUsersBtn = $(".showUsersBtn"),
                    url = showUsersBtn.hasClass("selected") ? showUsersBtn.data("url") : form.attr("action");
                showUsersBtn.removeClass("selected");

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
                            Swal.fire({
                                title: res.reload ? "{{__('success')}}" : "",
                                width: res.reload ? "" : "600px",
                                html: res?.message ?? "",
                                icon: "success",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}"
                            }).then((r) => {
                                if(res.reload) window.location.reload()
                            })
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
