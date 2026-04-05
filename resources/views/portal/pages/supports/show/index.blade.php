@extends("portal.template")
@section("title", __("support_tickets") . ' ' . $support->draw_id)
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('support_tickets') . ' ' . $support->draw_id "/>
@endsection
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
            background: #009ef7;
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
@section("master")
    @if($support->is_locked == 1)
        <div class="alert alert-primary d-flex flex-column flex-sm-row p-5 mb-10">
            <div class="d-flex align-items-center">
                <!--begin::Icon-->
                <i class="ki-duotone ki-notification-bing fs-3x me-4 mb-5 mb-sm-0 text-primary"><span
                        class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <!--end::Icon-->
            </div>
            <!--begin::Wrapper-->
            <div class="d-flex align-items-center">
                <!--begin::Title-->
                <h6 class="mb-0 text-primary">{{__("locked_support_info_message")}}</h6>
                <!--end::Title-->
            </div>
            <!--end::Wrapper-->
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <!--begin::Support Information-->
            <div class="d-flex align-items-center ms-4 mb-9">
                <!--begin::Icon-->
                @if($support->status == "RESOLVED")
                    <i class="ki-duotone ki-file-added fs-3qx text-success ms-n2 me-3"><span
                            class="path1"></span><span class="path2"></span></i>
                @else
                    <i class="ki-duotone ki-add-files fs-3qx text-warning ms-n2 me-3"><span
                            class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                @endif
                <!--end::Icon-->

                <!--begin::Content-->
                <div class="d-flex flex-column">
                    <!--begin::Title-->
                    <h1 class="text-gray-800 fw-semibold">{{$support->subject}}</h1>
                    <!--end::Title-->

                    <!--begin::Info-->
                    <div class="">
                        <!--begin::Label-->
                        <span class="fw-semibold text-muted">{{__("Oluşturulma Tarihi")}}: <span
                                class="fw-bold text-gray-600 me-1">{{$support->created_at->format(defaultDateTimeFormat())}}</span></span>
                        <!--end::Label-->
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Content-->
            </div>
            <div class="row mb-9">
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("department")}}</label>
                            <div class="text-gray-500 fw-semibold fs-6">{{$support->drawDepartment}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("updated_date")}}</label>
                            <div
                                class="text-gray-500 fw-semibold fs-6">{{$support->updated_at->format(defaultDateTimeFormat())}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("status")}}</label>
                            <div class="text-gray-500 fw-semibold fs-6">{{$support->drawStatus}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="card bg-secondary">
                        <div class="card-body d-flex flex-center flex-column">
                            <label class="form-label fw-bolder mb-2">{{__("priority")}}</label>
                            <div class="text-gray-500 fw-semibold fs-6">{{$support->drawPriority}}</div>
                        </div>
                    </div>
                </div>
            </div>
            @if($support->order)
            <div class="mb-9">
                <div class="border border-dashed border-gray-300 rounded p-4">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-40px me-3">
                            <div class="symbol-label bg-light-primary">
                                <i class="fa fa-box text-primary fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-gray-800 fw-bold fs-6 d-block">{{ $support->order->product_data['name'] ?? '-' }}</span>
                            <span class="text-muted fw-semibold fs-7">
                                Sipariş #{{ $support->order->id }}
                                @if(!empty($support->order->product_data['category']['name']))
                                    &middot; {{ $support->order->product_data['category']['name'] }}
                                @endif
                                @if($support->order->end_date)
                                    &middot; Bitiş: {{ $support->order->end_date->format('d.m.Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!--begin::Support Information-->
            <!--begin::Send Message-->
            <div class="mb-9">
                <form id="sendMessageForm" enctype="multipart/form-data"
                      action="{{route("portal.supports.saveMessage", ["support" => $support->id])}}" class="mb-0">
                                <textarea
                                    {{$support->is_locked == 1 ? "disabled" : ""}}
                                    maxlength="1000"
                                    class="form-control form-control-solid placeholder-gray-600 fw-bold fs-4 ps-9 pt-7"
                                    rows="6" name="message" placeholder="Detaylı olarak mesajınızı yazınız"></textarea>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div class="d-flex align-items-center">
                            <label for="supportFile" class="btn btn-sm btn-light-primary me-2 mb-0 {{ $support->is_locked == 1 ? 'disabled' : '' }}" style="cursor:pointer">
                                <i class="fa fa-paperclip me-1"></i>Görsel Ekle
                            </label>
                            <input type="file" id="supportFile" name="file" accept=".jpg,.jpeg,.png" class="d-none"
                                   {{ $support->is_locked == 1 ? 'disabled' : '' }} />
                            <span id="fileNameDisplay" class="text-muted fs-7"></span>
                        </div>
                        <button type="submit"
                                {{$support->is_locked == 1 ? "disabled" : ""}}
                                class="btn btn-primary btn-sm">
                            <i class="fa fa-paper-plane me-1"></i>{{__("send")}}
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1"><i class="fa fa-info-circle me-1"></i>JPG, JPEG, PNG formatları desteklenir. Maksimum 5MB.</small>
                </form>
                <!--end::Textarea-->
            </div>
            <!--end::Send Message-->
            <div id="typingIndicator" class="d-none mb-4">
                <div class="d-flex align-items-center bg-light-info rounded p-3">
                    <div class="typing-dots me-3">
                        <span></span><span></span><span></span>
                    </div>
                    <span class="text-info fw-semibold fs-7" id="typingText">Destek ekibi yazıyor...</span>
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
                                    <div class="d-flex flex-column fw-semibold fs-5 text-gray-600 text-gray-900">
                                        <!--begin::Text-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Username-->
                                            <div class="text-gray-800 fw-bold fs-5 me-3" data-np-message="name"></div>
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
                        </div>
                        <!--end::Body-->
                    </div>
                </div>
            </div>
            <!--end::Messages-->
        </div>
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            let userFullName = "{{auth()->user()->full_name}}",
                itemTemplate = $("[data-np-message='item-template']"),
                lastKnownMessageId = 0,
                typingTimer = null;

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
                        createdAt = moment(item.created_at).format("DD/MM/YYYY HH:mm:ss");
                    if (isAutoReply) {
                        itemTemplate.find("[data-np-message='name']").text("Otomatik Mesaj");
                        itemTemplate.find("[data-np-message='badge']").removeClass("badge-primary badge-success").addClass("badge-warning").text("Otomatik");
                        itemTemplate.find("[data-np-message='date']").removeClass("badge-primary badge-success").addClass("badge-warning").text(createdAt);
                    } else if (isAdmin) {
                        itemTemplate.find("[data-np-message='name']").text(item.admin.full_name);
                        itemTemplate.find("[data-np-message='badge']").removeClass("badge-primary badge-warning").addClass("badge-success").text("{{__("staff")}}");
                        itemTemplate.find("[data-np-message='date']").removeClass("badge-primary badge-warning").addClass("badge-success").text(createdAt);
                    } else {
                        itemTemplate.find("[data-np-message='name']").text(userFullName);
                        itemTemplate.find("[data-np-message='badge']").removeClass("badge-success badge-warning").addClass("badge-primary").text("Müşteri");
                        itemTemplate.find("[data-np-message='date']").removeClass("badge-success badge-warning").addClass("badge-primary").text(createdAt);
                    }
                    let msgHtml = item.message;
                    if (item.file) {
                        msgHtml += '<div class="mt-3"><a href="/' + item.file + '" target="_blank"><img src="/' + item.file + '" class="rounded border" style="max-width:300px;max-height:200px;cursor:pointer" /></a></div>';
                    }
                    itemTemplate.find("[data-np-message='message']").html(msgHtml)
                    $("[data-np-message='items']").append($("[data-np-message='item-template']").html());
                });
            };

            const getSupport = () => {
                $.ajax({
                    type: 'GET',
                    url: "{{ route("portal.supports.find", ["support" => $support->id]) }}",
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
                    url: "{{ route("portal.supports.poll", ["support" => $support->id]) }}",
                    dataType: 'json',
                    complete: function (data) {
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            if (res.last_message_id > lastKnownMessageId) {
                                lastKnownMessageId = res.last_message_id;
                                renderMessages(res.data.messages);
                                playNotificationSound();
                                toastr.info("Yeni yanıt alındı!", "Bildirim");
                            }
                            if (res.is_admin_typing) {
                                $("#typingIndicator").removeClass("d-none");
                                $("#typingText").text(res.typing_admin_name + " yazıyor...");
                            } else {
                                $("#typingIndicator").addClass("d-none");
                            }
                        }
                    }
                });
            };
            setInterval(pollSupport, 3000);

            function sendTyping() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route("portal.supports.typing", ["support" => $support->id]) }}",
                    data: { _token: '{{csrf_token()}}' },
                });
            }

            $(document).on("input", "#sendMessageForm textarea[name='message']", function () {
                clearTimeout(typingTimer);
                sendTyping();
                typingTimer = setTimeout(() => {}, 3000);
            });

            $(document).on("change", "#supportFile", function () {
                let file = this.files[0];
                if (file) {
                    let allowed = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowed.includes(file.type)) {
                        Swal.fire({ title: "Hata", text: "Sadece JPG, JPEG, PNG formatları desteklenir.", icon: "error" });
                        $(this).val('');
                        $("#fileNameDisplay").text('');
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire({ title: "Hata", text: "Dosya boyutu 5MB'ı geçemez.", icon: "error" });
                        $(this).val('');
                        $("#fileNameDisplay").text('');
                        return;
                    }
                    $("#fileNameDisplay").html('<i class="fa fa-image text-primary me-1"></i>' + file.name + ' <span class="text-muted">(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</span>');
                } else {
                    $("#fileNameDisplay").text('');
                }
            });

            $(document).on("submit", "#sendMessageForm", function (e) {
                e.preventDefault()
                let form = $(this), btn = form.find("[type='submit']");

                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        btn.prop("disabled", true)
                    },
                    complete: function (data) {
                        let res = data.responseJSON;
                        if (res && res.success === true) {
                            getSupport()
                            form.find("textarea").val("")
                            $("#supportFile").val('');
                            $("#fileNameDisplay").text('');
                            toastr.success(res?.message ?? "", "{{__('success')}}");
                            setTimeout(() => { btn.prop("disabled", false) }, 10000)
                        } else {
                            Swal.fire({
                                title: "{{__('error')}}",
                                text: res?.message ?? "{{__('form_has_errors')}}",
                                icon: "error",
                                showConfirmButton: 0,
                                showCancelButton: 1,
                                cancelButtonText: "{{__('close')}}",
                            })
                            btn.prop("disabled", false)
                        }
                    }
                })
            })
        });
    </script>
@endsection
