@extends("admin.template")
@section("title", __("support_ticket"))
@section("css")
    <style>
        .typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .typing-dots span {
            width: 7px;
            height: 7px;
            background: #ffc700;
            border-radius: 50%;
            animation: typingBounce 1.4s infinite ease-in-out both;
        }
        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        .typing-dots span:nth-child(3) { animation-delay: 0s; }
        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1); opacity: 1; }
        }
    </style>
@endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="[__('support_tickets') => route('admin.supports.index'), $support->subject]"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-5">
                <!--begin::Body-->
                <div class="card-body">
                    <div class="row mb-6">
                        <div class="col-xl-6">
                            <!--begin::Label-->
                            <span class="text-gray-800 fw-bold fs-6">{{__("customer")}}:</span>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <a target="_blank" href="{{route("admin.users.show", ["user" => $support->user_id])}}"
                               class="fs-4 text-primary fw-bold ms-1">{{$support->user->full_name}}</a>
                            <!--end::Input-->
                        </div>
                        <div class="col-xl-6 text-end">
                            @if($support->status !== 'RESOLVED')
                                <button class="btn btn-success btn-sm resolveBtn" data-url="{{route("admin.supports.resolve", ["support" => $support->id])}}" data-swal-text="Destek talebini çözümlendi olarak işaretlemek istediğinize emin misiniz?"><i class="fa fa-check-circle me-1"></i>Çözümlendi</button>
                            @endif
                            @if($support->is_locked == 1)
                                <button class="btn btn-danger btn-sm lockBtn" data-url="{{route("admin.supports.unlock", ["support" => $support->id])}}" data-swal-text="Destek Talebinin kilidini kaldırmak istediğinize emin misiniz?"><i class="fa fa-lock-open me-1"></i>Kilidi Kaldır</button>
                            @else
                                <button class="btn btn-danger btn-sm lockBtn" data-url="{{route("admin.supports.lock", ["support" => $support->id])}}" data-swal-text="Destek Talebini kilitlemek istediğinize emin misiniz?"><i class="fa fa-lock me-1"></i>Kilitle</button>
                            @endif
                            <button class="btn btn-danger btn-sm deleteBtn" data-url="{{route("admin.supports.delete", ["support" => $support->id])}}" data-swal-text="Destek Talebini silmek istediğinize emin misiniz?"><i class="fa fa-trash me-1"></i>{{__("delete")}}</button>
                        </div>
                    </div>
                    <div class="separator mb-4"></div>
                    <div class="mb-5">
                        <label class="form-label text-gray-800 fw-bold fs-6">Hazır Mesaj Şablonu</label>
                        <select class="form-select" id="templateSelect">
                            <option value="">-- Şablon Seçin --</option>
                        </select>
                    </div>
                    <form id="sendMessageForm"
                          action="{{route("admin.supports.saveMessage", ["support" => $support->id])}}" class="mb-5">
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("message")}}</label>
                        <!--end::Label-->
                        <!--begin::Textarea-->
                        <textarea name="message"
                                  class="editorInput form-control mb-2"></textarea>
                        <!--end::Textarea-->
                        <div class="text-end">
                            <button type="submit" class="btn btn-light-primary mt-3">{{__("Yanıtla")}}</button>
                        </div>
                    </form>
                </div>
                <!--end::Body-->
            </div>
            <div class="card">
                <div class="card-body">
                    <div id="typingIndicator" class="d-none mb-4">
                        <div class="d-flex align-items-center bg-light-warning rounded p-3">
                            <div class="typing-dots me-3">
                                <span></span><span></span><span></span>
                            </div>
                            <span class="text-warning fw-semibold fs-7" id="typingText">Müşteri yazıyor...</span>
                        </div>
                    </div>
                    <!--begin::Messages-->
                    <div>
                        <div data-np-message="items"></div>
                        <div class="d-none" data-np-message="item-template">
                            <div class="card card-bordered w-100 mb-5" data-np-message="item">
                                <!--begin::Body-->
                                <div class="card-body">
                                    <!--begin::Wrapper-->
                                    <div class="w-100 d-flex flex-stack">
                                        <!--begin::Container-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Info-->
                                            <div
                                                class="d-flex flex-column fw-semibold fs-5 text-gray-600 text-gray-900">
                                                <!--begin::Text-->
                                                <div class="d-flex align-items-center">
                                                    <!--begin::Username-->
                                                    <a class="text-gray-800 text-hover-primary fw-bold fs-5 me-3"
                                                       data-np-message="name" href="#" target="_blank"></a>
                                                    <!--end::Username-->
                                                    <span class="badge badge-success" data-np-message="badge"></span>
                                                </div>
                                                <!--end::Text-->
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Container-->

                                        <!--begin::Actions-->
                                        <div>
                                            <span class="badge badge-primary me-2" data-np-message="user-ip" style="cursor:pointer"></span>
                                            <span class="badge badge-success" data-np-message="date"></span>
                                        </div>
                                        <!--end::Actions-->
                                    </div>
                                    <!--end::Wrapper-->

                                    <div class="separator separator-dashed my-5"></div>

                                    <!--begin::Desc-->
                                    <p class="fw-normal fs-5 text-gray-700 m-0" data-np-message="message">
                                        I run a team of 20 product managers, developers, QA and UX Previously
                                        we designed everything ourselves.
                                    </p>
                                    <!--end::Desc-->
                                    <div class="d-none text-end mt-2" data-np-message="seen-status">
                                        <span class="text-success fs-8 fw-semibold">
                                            <i class="fa fa-check-double text-success fs-8 me-1"></i>Görüldü
                                            <span class="text-muted ms-1" data-np-message="seen-time"></span>
                                        </span>
                                    </div>
                                </div>
                                <!--end::Body-->
                            </div>
                        </div>
                    </div>
                    <!--end::Messages-->
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label text-gray-800 fw-bold fs-6 mb-0">{{__("related_service")}}</label>
                            <button class="btn btn-light-primary btn-sm py-1 px-3" id="changeOrderBtn" title="İlişkili hizmeti değiştir">
                                <i class="fa fa-exchange-alt fs-8 me-1"></i>Değiştir
                            </button>
                        </div>
                        <div id="orderDisplayArea">
                            @if($support->order)
                                <div class="border border-dashed border-gray-300 rounded p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-light-primary">
                                                <i class="fa fa-box text-primary fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="text-gray-800 fw-bold fs-6 d-block">{{ $support->order->product_data['name'] ?? '-' }}</span>
                                            @if(!empty($support->order->product_data['category']['name']))
                                                <span class="text-muted fw-semibold fs-7">{{ $support->order->product_data['category']['name'] }}</span>
                                            @endif
                                        </div>
                                        <a class="btn btn-icon btn-light-primary btn-sm"
                                           target="_blank"
                                           href="{{route("admin.orders.show", ["order" => $support->order->id])}}"
                                           title="Siparişi Görüntüle">
                                            <i class="fa fa-external-link-alt fs-7"></i>
                                        </a>
                                    </div>
                                    <div class="separator separator-dashed mb-3"></div>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="text-gray-500 fw-semibold fs-7 py-1 ps-0" style="width:90px">Sipariş No</td>
                                                <td class="text-gray-800 fw-bold fs-7 py-1"><span class="badge badge-light-primary badge-sm">#{{ $support->order->id }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-500 fw-semibold fs-7 py-1 ps-0">Durum</td>
                                                <td class="py-1">{!! $support->order->drawStatus('badge-sm') !!}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-gray-500 fw-semibold fs-7 py-1 ps-0">Teslimat</td>
                                                <td class="py-1">{!! $support->order->drawDeliveryStatus('badge-sm') !!}</td>
                                            </tr>
                                            @if($support->order->end_date)
                                            <tr>
                                                <td class="text-gray-500 fw-semibold fs-7 py-1 ps-0">Bitiş</td>
                                                <td class="text-gray-800 fs-7 py-1">
                                                    <i class="fa fa-calendar-alt text-muted fs-8 me-1"></i>{{ $support->order->end_date->format('d.m.Y') }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if($support->order->getTotalAmount())
                                            <tr>
                                                <td class="text-gray-500 fw-semibold fs-7 py-1 ps-0">Tutar</td>
                                                <td class="text-gray-800 fw-bold fs-7 py-1">{{ number_format($support->order->getTotalAmount(), 2, ',', '.') }} ₺</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="border border-dashed border-gray-300 rounded p-4 text-center">
                                    <i class="fa fa-inbox text-gray-300 fs-2x d-block mb-2"></i>
                                    <span class="text-gray-400 fw-semibold fs-7">İlişkili sipariş yok</span>
                                </div>
                            @endif
                        </div>
                        <div id="orderSelectArea" class="d-none">
                            <div class="border border-dashed border-primary rounded p-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-primary fw-bold fs-7"><i class="fa fa-exchange-alt me-1"></i>Hizmet Seçin</span>
                                    <button class="btn btn-icon btn-light btn-xs" id="cancelChangeOrder" title="İptal"><i class="fa fa-times fs-8"></i></button>
                                </div>
                                <select class="form-select form-select-sm mb-3" id="orderSelectDropdown">
                                    <option value="">Yükleniyor...</option>
                                </select>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm flex-grow-1" id="saveOrderChange"><i class="fa fa-check me-1"></i>Kaydet</button>
                                    <button class="btn btn-light-danger btn-sm" id="removeOrderLink" title="İlişkiyi kaldır"><i class="fa fa-unlink me-1"></i>Kaldır</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="separator separator-dashed my-3"></div>
                    <div>
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("status")}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        @php
                            $statusSelectUrl = route("admin.supports.updateStatus", ["support" => $support->id]);
                        @endphp
                        <x-admin.form-elements.support-statuses-select name="support_status"
                                                                       required="required"
                                                                       customClass="statusSelect form-select-sm"
                                                                       customAttr="data-url='{{$statusSelectUrl}}' data-swal-text='Talep durumunu düzenlemek istediğinize emin misiniz?' data-current-val='{{$support->status}}'"
                                                                       :selectedOption="$support->status"
                                                                       :hideSearch="true"/>
                        <!--end::Select-->
                    </div>
                    <div class="separator separator-dashed my-3"></div>
                    <div>
                        <!--begin::Label-->
                        <label class="form-label text-gray-800 fw-bold fs-6">{{__("department")}}</label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        @php
                            $departmentSelectUrl = route("admin.supports.updateDepartment", ["support" => $support->id]);
                        @endphp
                        <x-portal.form-elements.department-select name="department"
                                                                  required="required"
                                                                  customClass="departmentSelect form-select-sm"
                                                                  customAttr="data-url='{{$departmentSelectUrl}}' data-swal-text='Departmanı düzenlemek istediğinize emin misiniz?' data-current-val={{$support->department}}"
                                                                  :selectedOption="$support->department"
                                                                  :hideSearch="true"/>
                        <!--end::Select-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("js")
    <script src="{{assetAdmin("plugins/custom/tinymce/tinymce.bundle.js")}}"></script>
    <script>
        $(document).ready(function () {
            @php
                $adminSignature = auth()->guard('admin')->user()->signature ?? '';
                if ($adminSignature) {
                    $adminSignature = str_replace('{admin_name}', auth()->guard('admin')->user()->full_name, $adminSignature);
                    $adminSignature = str_replace('{admin_email}', auth()->guard('admin')->user()->email, $adminSignature);
                    $adminSignature = str_replace('{admin_phone}', auth()->guard('admin')->user()->phone ?? '', $adminSignature);
                } else {
                    $adminSignature = '<p>Saygılarımla<br />Sağlam Proxy Hizmetleri<br />Firma Yetkilisi<br />Whatsapp Destek Hattı : 0530 132 02 95</p>';
                }
                $greeting = '<p>Merhaba ' . e($support->user->full_name) . ',</p>';
                $editorContent = $greeting . '<p><br></p>' . $adminSignature;
            @endphp

            tinymce.init({
                selector: ".editorInput",
                height: "300",
                plugins: [
                    "advlist autolink lists link charmap print preview anchor",
                    "searchreplace visualblocks code fullscreen",
                    "insertdatetime media table paste wordcount textpattern"
                ],
                toolbar: "styleselect fontselect fontsizeselect | bold italic underline forecolor backcolor | link | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat code",
                init_instance_callback: function (editor) {
                    editor.setContent({!! json_encode($editorContent) !!});
                    var body = editor.getBody();
                    var secondP = body.children[1];
                    if (secondP) {
                        editor.selection.setCursorLocation(secondP, 0);
                    }
                    editor.focus();

                    let adminTypingTimer = null;
                    editor.on('keyup', function () {
                        clearTimeout(adminTypingTimer);
                        $.ajax({
                            type: 'POST',
                            url: "{{ route("admin.supports.typing", ["support" => $support->id]) }}",
                            data: { _token: '{{csrf_token()}}' },
                        });
                        adminTypingTimer = setTimeout(() => {}, 3000);
                    });
                }
            });

            let templateData = [];
            $.ajax({
                type: 'GET',
                url: '{{ route("admin.supports.templates.getActive") }}',
                dataType: 'json',
                complete: function (data) {
                    let res = data.responseJSON;
                    if (res && res.success === true && res.templates) {
                        templateData = res.templates;
                        res.templates.forEach(function (t) {
                            $("#templateSelect").append('<option value="' + t.id + '">' + t.title + '</option>');
                        });
                    }
                }
            });

            $(document).on("change", "#templateSelect", function () {
                let selectedId = $(this).val();
                if (!selectedId) return;
                let template = templateData.find(t => t.id == selectedId);
                if (template) {
                    let content = template.content;
                    content = content.replace(/\{user_name\}/g, "{{ $support->user->full_name }}");
                    content = content.replace(/\{ticket_id\}/g, "{{ $support->id }}");
                    content = content.replace(/\{ticket_subject\}/g, "{{ $support->subject }}");

                    if (tinymce.activeEditor) {
                        let signature = {!! json_encode($adminSignature) !!};
                        let greeting = {!! json_encode('<p>Merhaba ' . e($support->user->full_name) . ',</p>') !!};
                        tinymce.activeEditor.setContent(greeting + "<p><br></p>" + content + "<p><br></p>" + signature);
                        var body = tinymce.activeEditor.getBody();
                        var secondP = body.children[1];
                        if (secondP) {
                            tinymce.activeEditor.selection.setCursorLocation(secondP, 0);
                        }
                        tinymce.activeEditor.focus();
                    } else {
                        $(".editorInput[name='message']").val(content);
                    }
                }
            });
        })
    </script>

    <script>
        $(document).ready(function () {
            let userFullName = "{{$support->user->full_name}}",
                itemTemplate = $("[data-np-message='item-template']"),
                lastKnownMessageId = 0;

            function playNotificationSound() {
                try {
                    let ctx = new (window.AudioContext || window.webkitAudioContext)();
                    let t = ctx.currentTime;
                    let notes = [
                        { freq: 1175, start: 0, dur: 0.1 },
                        { freq: 1568, start: 0.15, dur: 0.1 },
                        { freq: 1397, start: 0.3, dur: 0.15 },
                    ];
                    notes.forEach(n => {
                        let osc = ctx.createOscillator();
                        let gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(n.freq, t + n.start);
                        gain.gain.setValueAtTime(0, t + n.start);
                        gain.gain.linearRampToValueAtTime(0.25, t + n.start + 0.01);
                        gain.gain.exponentialRampToValueAtTime(0.001, t + n.start + n.dur);
                        osc.start(t + n.start);
                        osc.stop(t + n.start + n.dur + 0.05);
                    });
                } catch(e) {}
            }

            const renderMessages = (messages) => {
                $("[data-np-message='items']").html("");
                messages.map((item) => {
                    let isAdmin = !!item.admin_id,
                        isAutoReply = !!item.is_auto_reply,
                        createdAt = moment(item.created_at).format(defaultDateTimeFormat());
                    let nameEl = itemTemplate.find("[data-np-message='name']");
                    if (isAutoReply) {
                        nameEl.text("Otomatik Mesaj").attr("href", "#").removeAttr("target");
                        itemTemplate.find("[data-np-message='badge']").removeClass("badge-primary badge-success").addClass("badge-warning").text("Otomatik");
                        itemTemplate.find("[data-np-message='date']").removeClass("badge-primary badge-success").addClass("badge-warning").text(createdAt);
                        itemTemplate.find("[data-np-message='user-ip']").addClass("d-none");
                    } else if (isAdmin) {
                        nameEl.text(item.admin.full_name).attr("href", "#").removeAttr("target");
                        itemTemplate.find("[data-np-message='badge']").removeClass("badge-primary badge-warning").addClass("badge-success").text("{{__("staff")}}");
                        itemTemplate.find("[data-np-message='date']").removeClass("badge-primary badge-warning").addClass("badge-success").text(createdAt);
                        itemTemplate.find("[data-np-message='user-ip']").addClass("d-none");
                    } else {
                        nameEl.text(userFullName).attr("href", "{{ route('admin.users.show', ['user' => $support->user_id]) }}").attr("target", "_blank");
                        itemTemplate.find("[data-np-message='badge']").removeClass("badge-success badge-warning").addClass("badge-primary").text("Müşteri");
                        itemTemplate.find("[data-np-message='date']").removeClass("badge-success badge-warning").addClass("badge-primary").text(createdAt);
                        itemTemplate.find("[data-np-message='user-ip']").text("IP: " + item?.user_ip).attr("data-ip-lookup", item?.user_ip || "");
                        itemTemplate.find("[data-np-message='user-ip']").removeClass("d-none");
                    }
                    let msgHtml = (item.message || '').replace(/\n/g, '<br>');
                    if (item.file) {
                        msgHtml += '<div class="mt-3"><a href="/' + item.file + '" target="_blank"><img src="/' + item.file + '" class="rounded border" style="max-width:300px;max-height:200px;cursor:pointer" /></a></div>';
                    }
                    itemTemplate.find("[data-np-message='message']").html(msgHtml);

                    if (isAdmin && !isAutoReply && item.seen_at) {
                        let seenTime = moment(item.seen_at).format(defaultDateTimeFormat());
                        itemTemplate.find("[data-np-message='seen-time']").text(seenTime);
                        itemTemplate.find("[data-np-message='seen-status']").removeClass("d-none");
                    } else {
                        itemTemplate.find("[data-np-message='seen-status']").addClass("d-none");
                        itemTemplate.find("[data-np-message='seen-time']").text("");
                    }

                    $("[data-np-message='items']").append($("[data-np-message='item-template']").html());
                });
            };

            const getSupport = () => {
                $.ajax({
                    type: 'GET',
                    url: "{{ route("admin.supports.find", ["support" => $support->id]) }}",
                    data: { _token: '{{csrf_token()}}' },
                    complete: function (data) {
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            renderMessages(res.data.messages);
                            if (res.data.messages.length > 0) {
                                lastKnownMessageId = res.data.messages[0].id;
                            }
                        }
                    }
                })
            }
            getSupport();

            const pollSupport = () => {
                $.ajax({
                    type: 'GET',
                    url: "{{ route("admin.supports.poll", ["support" => $support->id]) }}",
                    dataType: 'json',
                    complete: function (data) {
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            if (res.last_message_id > lastKnownMessageId) {
                                lastKnownMessageId = res.last_message_id;
                                renderMessages(res.data.messages);
                                playNotificationSound();
                                toastr.info("Yeni mesaj alındı!", "Bildirim");
                            }
                            if (res.is_user_typing) {
                                $("#typingIndicator").removeClass("d-none");
                                $("#typingText").text(res.typing_user_name + " yazıyor...");
                            } else {
                                $("#typingIndicator").addClass("d-none");
                            }
                        }
                    }
                });
            };
            setInterval(pollSupport, 3000);

            $(document).on("submit", "#sendMessageForm", function (e) {
                e.preventDefault()
                let form = $(this),
                    btn = form.find("btn[type='submit']");

                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        propSubmitButton(btn, 1)
                    },
                    complete: function (data) {
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            getSupport()
                            form.find("textarea").val("")
                            toastr.success(res?.message ?? "", "{{__('success')}}");
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
                        setTimeout(() => propSubmitButton(btn, 0), 3000)
                    }
                })
            })

            $(document).on("change", ".statusSelect, .departmentSelect", function () {
                let element = $(this),
                    ajaxUrl = element.data("url"),
                    swalText = element.data("swal-text"),
                    currentVal = element.data("current-val");
                if (currentVal != element.val()) {
                    alerts.confirm.fire({
                        title: "{{__('warning')}}",
                        text: swalText,
                        confirmButtonText: "{{__('yes')}}",
                    }).then((result) => {
                        if (result.isConfirmed === true) {
                            $.ajax({
                                type: "POST",
                                url: ajaxUrl,
                                dataType: "json",
                                data: {
                                    _token: "{{csrf_token()}}",
                                    value: element.val()
                                },
                                beforeSend: function () {
                                    element.prop("disabled", true)
                                },
                                complete: function (data, status) {
                                    element.prop("disabled", false)
                                    res = data.responseJSON;
                                    if (res && res.success === true) {
                                        alerts.success.fire({
                                            title: "{{__('success')}}",
                                            text: res?.message ?? "",
                                        }).then((r) => window.location.reload())
                                    } else {
                                        alerts.error.fire({
                                            title: "{{__('error')}}",
                                            text: res?.message ?? "",
                                        }).then((r) => window.location.reload())
                                    }
                                }
                            })
                        } else {
                            element.val(currentVal).trigger("change")
                        }
                    });
                }
            })
            $('#changeOrderBtn').on('click', function () {
                $('#orderDisplayArea').addClass('d-none');
                $('#orderSelectArea').removeClass('d-none');
                var dropdown = $('#orderSelectDropdown');
                dropdown.html('<option value="">Yükleniyor...</option>').prop('disabled', true);
                $.ajax({
                    type: 'GET',
                    url: '{{ route("admin.supports.getUserOrders", ["user" => $support->user_id]) }}',
                    dataType: 'json',
                    success: function (res) {
                        dropdown.html('<option value="">-- Sipariş Seçin --</option>');
                        if (res.success && res.orders) {
                            res.orders.forEach(function (o) {
                                var label = '#' + o.id + ' - ' + o.name;
                                if (o.category) label += ' (' + o.category + ')';
                                if (o.end_date) label += ' - ' + o.end_date;
                                var selected = o.id == {{ $support->order_id ?? 0 }} ? ' selected' : '';
                                dropdown.append('<option value="' + o.id + '"' + selected + '>' + label + '</option>');
                            });
                        }
                        dropdown.prop('disabled', false);
                    },
                    error: function () {
                        dropdown.html('<option value="">Hata oluştu</option>');
                    }
                });
            });

            $('#cancelChangeOrder').on('click', function () {
                $('#orderSelectArea').addClass('d-none');
                $('#orderDisplayArea').removeClass('d-none');
            });

            function submitOrderChange(orderId) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.supports.changeOrder", ["support" => $support->id]) }}',
                    data: { _token: '{{ csrf_token() }}', order_id: orderId },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            toastr.success(res.message);
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Bir hata oluştu.';
                        toastr.error(msg);
                    }
                });
            }

            $('#saveOrderChange').on('click', function () {
                var orderId = $('#orderSelectDropdown').val();
                if (!orderId) {
                    toastr.warning('Lütfen bir sipariş seçin.');
                    return;
                }
                submitOrderChange(orderId);
            });

            $('#removeOrderLink').on('click', function () {
                alerts.confirm.fire({
                    title: "{{__('warning')}}",
                    html: 'İlişkili hizmeti kaldırmak istediğinize emin misiniz?',
                    confirmButtonText: "{{__('yes')}}",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        submitOrderChange(null);
                    }
                });
            });

            $(document).on("click", ".lockBtn, .deleteBtn, .resolveBtn", function () {
                let element = $(this),
                    ajaxUrl = element.data("url"),
                    swalText = element.data("swal-text"),
                    currentVal = element.data("current-val");
                alerts.confirm.fire({
                    title: "{{__('warning')}}",
                    html: swalText,
                    confirmButtonText: "{{__('yes')}}",
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: ajaxUrl,
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                value: element.val()
                            },
                            beforeSend: function () {
                                element.prop("disabled", true)
                            },
                            complete: function (data, status) {
                                element.prop("disabled", false)
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res?.message ?? "",
                                    }).then((r) => {
                                        if(res?.redirectUrl){
                                            window.location.href = res.redirectUrl
                                        }else{
                                            window.location.reload()
                                        }
                                    })
                                } else {
                                    alerts.error.fire({
                                        title: "{{__('error')}}",
                                        text: res?.message ?? "",
                                    })
                                }
                            }
                        })
                    } else {
                        element.val(currentVal).trigger("change")
                    }
                });
            })
        })
    </script>
@endsection
