@extends("admin.template")
@section("title", '3Proxy: '.$threeProxy->ip_address)
@section("css")


@endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
    <x-admin.bread-crumb :data="'3Proxy: '.$threeProxy->ip_address"/>
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
                    <button type="button" class="btn btn-primary addBtn"><i class="fa fa-plus fs-5"></i> Toplu Oluştur
                    </button>
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
                    <th class="min-w-125px">IP</th>
                    <th class="min-w-125px">Port</th>
                    <th class="min-w-125px">Username</th>
                    <th class="min-w-125px">Password</th>
                    <th class="min-w-125px">İşlem</th>
                </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                @foreach($data as $key => $proxy)
                    <tr>
                        <td>{{intval($key)+1}}</td>
                        <td>{{$proxy['externals'][0] ?? '-'}}</td>
                        <td>{{$proxy['ports'][0] ?? '-'}}</td>
                        <td>{{@$proxy['users'][0]['username'] ?? '-'}}</td>
                        <td>{{@$proxy['users'][0]['password'] ?? '-'}}</td>
                        <td>
                            <button class="btn btn-sm btn-info viewButton" data-externals='{!! json_encode($proxy['externals']) !!}' data-password="{{@$proxy['users'][0]['password'] ?? '-'}}" data-username="{{@$proxy['users'][0]['username'] ?? '-'}}" data-ports='{!! json_encode($proxy['ports']) !!}'><i class="fa fa-info-circle"></i> Görüntüle</button>
                            <button class="btn btn-sm btn-primary copyButton" data-text="{{$proxy['externals'][0] ?? ''}}:{{$proxy['ports'][0] ?? ''}}:{{@$proxy['users'][0]['username']}}:{{@$proxy['users'][0]['password']}}"><i class="fa fa-copy"></i> Kopyala</button>
                            <button class="btn btn-sm btn-danger d-none"><i class="fa fa-trash"></i> Sil</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <!--end::Table body-->
            </table>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
    <div class="modal fade" id="viewProxyModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2>İncele</h2>
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
                    <h4>Proxy Listesi (<span class="count-proxies">0</span>)</h4>
            <div class="content-area overflow-scroll mh-400px"></div>

                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>

    <!--begin::Modals-->
    <div class="modal fade" id="primaryGroupModal" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-650px">
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
                    <div class="alert alert-danger">Her satıra <b>IP:PORT:USER:PASS</b> formatında giriş yapmalısınız.</div>
                    <form id="userGroupForm">
                        @csrf
                        <input type="hidden" name="url">
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="fv-row">
                                    <!--begin::Label-->
                                    <label class="required form-label mb-3">Protokol Seçimi</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="protocol" class="form-control form-control-lg " required>
                                        <option value="http">HTTP</option>
                                        <option value="socks">SOCKS5</option>
                                    </select>
                                    <!--end::Input-->
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="fv-row">
                                    <!--begin::Label-->
                                    <label class="required form-label mb-3">Proxy Listesi</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea type="text" name="proxies" class="form-control form-control-lg " required></textarea>
                                    <!--end::Input-->
                                </div>
                            </div>                                 <!--end::Input-->
                        </div>
                        <!--begin::Actions-->
                        <div class="d-flex flex-center flex-row-fluid pt-12">
                            <button type="reset" class="btn btn-light me-3"
                                    data-bs-dismiss="modal">{{__("cancel")}}</button>
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
            $(document).on('click','.viewButton',function(){
                let externals = JSON.parse($(this).attr('data-externals'));
                let ports = JSON.parse($(this).attr('data-ports'));
                let username = $(this).attr('data-username');
                let password = $(this).attr('data-password');


                let html = '<ul class="list-group">';
                $.each(externals,function(index,item){
                    html += '<li class="list-group-item">'+item+':'+ports[index]+':'+username+':'+password+'</li>';
                })

                html += '</ul>';
                $('#viewProxyModal').find('.content-area').html(html);
                $('#viewProxyModal').find('.count-proxies').html(externals.length);
                $('#viewProxyModal').modal('show');
            })
            $('.copyButton').on('click', function() {
                var textToCopy = $(this).attr('data-text');

                // Clipboard API
                navigator.clipboard.writeText(textToCopy).then(function() {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarıyla kopyalandı!',
                        text: textToCopy,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }, function(err) {
                    console.error('Error copying text: ', err);
                });
            });

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
                ]
            })

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".addBtn", function () {
                $("#userGroupForm [name='url']").val("{{route('admin.products.3proxy.servers.bulkCreate',['ThreeProxyServer' => $threeProxy->id])}}")

                $("#userGroupForm textarea").val("")
                $("#primaryGroupModal .modal-header h2").html("{{__("create")}}")
                $("#primaryGroupModal").modal("show")
            })
            $(document).on("click", ".editBtn", function () {
                let iElm = $(this).closest("tr").find("td:nth-child(1) span");
                $("#userGroupForm [name='url']").val("{{route('admin.products.3proxy.servers.update')}}")
                $("#userGroupForm [name='id']").val(iElm.attr("data-id"))
                $("#userGroupForm [name='ip_address']").val(iElm.attr('data-ip'))
                $("#userGroupForm [name='port']").val(iElm.attr('data-port'))
                $("#userGroupForm [name='api_key']").val(iElm.attr('data-api-key'))
                if(iElm.attr('data-is-active') == 1){
                    $("#userGroupForm [name='is_active']").prop("checked",true);

                }else{
                    $("#userGroupForm [name='is_active']").prop("checked",false);

                }
                $("#primaryGroupModal .modal-header h2").html("{{__("edit")}}")
                $("#primaryGroupModal").modal("show")
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
                            url: "{{route('admin.products.3proxy.servers.delete')}}",
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

            $(document).on("submit", "#userGroupForm", function (e) {
                e.preventDefault()
                let formData = new FormData(this);
                formData.delete("url");

                $.ajax({
                    type: 'POST',
                    url: $("#userGroupForm [name='url']").val(),
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
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
                            }).then(r => window.location.reload())
                            t.draw();
                            $("#primaryGroupModal").modal("hide");
                            $("#userGroupForm [name='groupName']").val("");
                            $("#userGroupForm [name='url']").val("");
                            $("#userGroupForm [name='id']").val("");
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
