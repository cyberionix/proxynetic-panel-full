@extends("admin.template")
@section("title", __("support_tickets"))
@section("css")
    <style>
        .bulk-toolbar { display: none; }
        .bulk-toolbar.active { display: flex !important; }
    </style>
@endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('support_tickets')"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
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
                       data-key="WAITING_FOR_AN_ANSWER"
                       href="javascript:void(0);">{{__("waiting_for_an_answer")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 statusTab"
                       data-bs-toggle="tab"
                       data-key="ANSWERED"
                       href="javascript:void(0);">{{__("answered")}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 statusTab"
                       data-bs-toggle="tab"
                       data-key="RESOLVED"
                       href="javascript:void(0);">{{__("resolved")}}</a>
                </li>
                <!--end:::Tab item-->
            </ul>
            <!--end:::Tabs-->
        </div>
    </div>
    <!--end::Navbar-->
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
            <div class="card-toolbar gap-2">
                <div class="bulk-toolbar align-items-center gap-2" id="bulkToolbar">
                    <span class="fw-bold text-gray-700 me-2"><span id="bulkCount">0</span> seçili</span>
                    <button type="button" class="btn btn-sm btn-success bulkActionBtn" data-action="RESOLVED">
                        <i class="fa fa-check-circle me-1"></i>Çözümlendi
                    </button>
                    <button type="button" class="btn btn-sm btn-warning bulkActionBtn" data-action="ANSWERED">
                        <i class="fa fa-reply me-1"></i>Yanıtlandı
                    </button>
                    <button type="button" class="btn btn-sm btn-info bulkActionBtn" data-action="WAITING_FOR_AN_ANSWER">
                        <i class="fa fa-clock me-1"></i>Yanıt Bekliyor
                    </button>
                    <button type="button" class="btn btn-sm btn-danger bulkActionBtn" data-action="DELETE">
                        <i class="fa fa-trash me-1"></i>Sil
                    </button>
                    <button type="button" class="btn btn-sm btn-light" id="bulkClearBtn">
                        <i class="fa fa-times me-1"></i>Temizle
                    </button>
                </div>
                <a href="{{route("admin.supports.templates.index")}}" class="btn btn-light-primary btn-sm">
                    <i class="fa fa-envelope-open-text me-1"></i>Hazır Mesajlar
                </a>
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
                    <th class="w-25px"><div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" id="selectAll" /></div></th>
                    <th class="m-w-50">#</th>
                    <th class="min-w-125px">{{__("customer")}}</th>
                    <th class="min-w-125px">{{__("subject")}}</th>
                    <th class="min-w-125px">Ürün / Hizmet</th>
                    <th class="min-w-125px">{{__("updated_date")}}</th>
                    <th class="min-w-125px">{{__("status")}}</th>
                    <th class="m-w-50"></th>
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
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            var lastUpdatedAt = 0;
            var lastTotalTickets = 0;
            var isFirstLoad = true;
            var tabSwitching = false;

            function playNotificationSound() {
                try {
                    var ac = new (window.AudioContext || window.webkitAudioContext)();
                    var notes = [
                        { freq: 1175, start: 0, dur: 0.08 },
                        { freq: 1397, start: 0.1, dur: 0.08 },
                        { freq: 1568, start: 0.2, dur: 0.12 }
                    ];
                    var t = ac.currentTime;
                    notes.forEach(function(n) {
                        var osc = ac.createOscillator();
                        var gain = ac.createGain();
                        osc.type = 'sine';
                        osc.frequency.value = n.freq;
                        gain.gain.setValueAtTime(0, t + n.start);
                        gain.gain.linearRampToValueAtTime(0.3, t + n.start + 0.01);
                        gain.gain.exponentialRampToValueAtTime(0.001, t + n.start + n.dur + 0.05);
                        osc.connect(gain);
                        gain.connect(ac.destination);
                        osc.start(t + n.start);
                        osc.stop(t + n.start + n.dur + 0.05);
                    });
                } catch(e) {}
            }

            var t = $("#dataTable").DataTable({
                order: [],
                columnDefs: [
                    { orderable: !1, targets: 0 },
                    { orderable: !0, targets: 1 },
                    { orderable: !0, targets: 2 },
                    { orderable: !0, targets: 3 },
                    { orderable: !0, targets: 4 },
                    { orderable: !0, targets: 5 },
                    { orderable: !0, targets: 6 },
                    { orderable: !1, targets: 7 }
                ],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route("admin.supports.ajax") }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                        d.showAllList = true
                        d.status = $(".statusTab.active").data("key")
                    },
                    "dataSrc": function (json) {
                        var newUpdatedAt = json.latestUpdatedAt || 0;
                        var newTotalTickets = json.totalTickets || 0;

                        if (!isFirstLoad && !tabSwitching) {
                            if (newTotalTickets > lastTotalTickets) {
                                playNotificationSound();
                                toastr.info("Yeni destek talebi geldi!", "Bildirim");
                            } else if (newUpdatedAt > lastUpdatedAt) {
                                playNotificationSound();
                                toastr.info("Destek talebi güncellendi!", "Bildirim");
                            }
                        }

                        lastUpdatedAt = newUpdatedAt;
                        lastTotalTickets = newTotalTickets;
                        isFirstLoad = false;
                        tabSwitching = false;

                        return json.data;
                    }
                },
            }).on("draw", function () {
                KTMenu.createInstances();
            });

            document.querySelector('[data-table-action="search"]').addEventListener("keyup", (function (e) {
                t.search(e.target.value).draw();
            }));

            $(document).on("click", ".statusTab", function () {
                tabSwitching = true;
                t.draw();
            })

            setInterval(function () {
                t.ajax.reload(null, false);
            }, 10000);

            function updateBulkToolbar() {
                var count = $(".row-checkbox:checked").length;
                $("#bulkCount").text(count);
                if (count > 0) {
                    $("#bulkToolbar").addClass("active");
                } else {
                    $("#bulkToolbar").removeClass("active");
                }
            }

            $(document).on("change", "#selectAll", function () {
                $(".row-checkbox").prop("checked", this.checked);
                updateBulkToolbar();
            });

            $(document).on("change", ".row-checkbox", function () {
                if (!this.checked) {
                    $("#selectAll").prop("checked", false);
                } else if ($(".row-checkbox").length === $(".row-checkbox:checked").length) {
                    $("#selectAll").prop("checked", true);
                }
                updateBulkToolbar();
            });

            $(document).on("click", "#bulkClearBtn", function () {
                $(".row-checkbox, #selectAll").prop("checked", false);
                updateBulkToolbar();
            });

            $(document).on("click", ".bulkActionBtn", function () {
                var action = $(this).data("action");
                var ids = [];
                $(".row-checkbox:checked").each(function () {
                    ids.push($(this).val());
                });
                if (ids.length === 0) return;

                var actionLabels = {
                    'RESOLVED': 'Çözümlendi olarak işaretlenecek',
                    'ANSWERED': 'Yanıtlandı olarak işaretlenecek',
                    'WAITING_FOR_AN_ANSWER': 'Yanıt Bekliyor olarak işaretlenecek',
                    'DELETE': 'Silinecek'
                };

                Swal.fire({
                    title: "Emin misiniz?",
                    html: "<b>" + ids.length + "</b> destek talebi <b>" + actionLabels[action] + "</b>.",
                    icon: action === "DELETE" ? "warning" : "question",
                    showCancelButton: true,
                    confirmButtonText: "Evet, Uygula",
                    cancelButtonText: "Vazgeç",
                    confirmButtonColor: action === "DELETE" ? "#f1416c" : "#009ef7"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('admin.supports.bulkAction') }}",
                            dataType: "json",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: ids,
                                action: action
                            },
                            complete: function (data) {
                                var res = data.responseJSON;
                                if (res && res.success === true) {
                                    toastr.success(res.message);
                                    $(".row-checkbox, #selectAll").prop("checked", false);
                                    updateBulkToolbar();
                                    tabSwitching = true;
                                    t.ajax.reload(null, false);
                                } else {
                                    Swal.fire({ title: "Hata", text: res?.message ?? "Bir hata oluştu.", icon: "error" });
                                }
                            }
                        });
                    }
                });
            });
        })
    </script>
@endsection
