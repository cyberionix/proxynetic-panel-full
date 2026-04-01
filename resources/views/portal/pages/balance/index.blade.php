@extends("portal.template")
@section("title", __('credit_balance'))
@section("css")
    <style>
        .loading {
            filter: blur(3px);
            pointer-events: none;
        }
    </style>
@endsection
@section("breadcrumb")
    <x-portal.bread-crumb :data="__('credit_balance')"/>
@endsection
@section("master")
    <div class="row gap-5">
        <div class="col-12">
            <div class="alert alert-dismissible bg-primary d-flex flex-column flex-sm-row p-5 mb-0">
                <div class="d-flex flex-center me-5">
                    <i class="fa fa-info-circle fs-2hx text-white"></i>
                </div>
                <div class="text-white">
                    <div class="fw-bold">Kredi Bakiyesi ile;</div>
                    - Tüm ürün ve hizmetlerimizde anında aktivasyon sağlayabilir,<br>
                    - Ödenmemiş faturalarınızı kredi bakiyeniz ile ödeyebilirsiniz.
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-label fw-bolder fs-3">{{__("credit_balance")}}</div>
                    <div class="fs-6 fw-bold">
                        <span class="d-block fs-2x"
                              data-np-balance="amount">{{showBalance(auth()->user()->balance, true)}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <label class="required form-label">{{__('balance')}}</label>
                            <div class="input-group">
                                <span class="input-group-text">₺</span>
                                <input type="text" placeholder="0,00" class="form-control price mw-125px priceInput" name="balance">
                            </div>
                            <button class="btn btn-light-primary btn-sm mt-4 addBalanceBtn">
                                <!--begin::Indicator label-->
                                <div class="d-flex flex-center gap-2">
                                    <i class="fa fa-plus"></i><span class="indicator-label">{{__('add_:name', ['name' => __('balance')])}}</span>
                                </div>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">{{__("please_wait")}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Bakiye Hareketlerim</h2>
                        <!--start::Info-->
                        <div class="ms-7 d-flex flex-wrap gap-5">
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div class="border border-1 border-gray-400 w-15px h-15px bg-light-success me-1"></div>
                                Para Girişi
                            </div>
                            <div class="d-flex align-items-center fs-6 me-3">
                                <div class="border border-1 border-gray-400 w-15px h-15px bg-light-danger me-1"></div>
                                Para Çıkışı
                            </div>
                        </div>
                        <!--end::Info-->
                    </div>
                </div>
                <div class="card-body">
                    <!--begin::Table-->
                    <table id="balanceActivityTable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="m-w-50">#</th>
                            <th class="min-w-125px">{{__("amount")}}</th>
                            <th class="min-w-125px">{{__("date")}}</th>
                            <th class="min-w-125px">{{__("action")}}</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">

                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
            </div>
        </div>
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $(document).on("click", ".addBalanceBtn", function () {
                let btn = $(this);
                alerts.confirm.fire({
                    title: "{{__('warning')}}",
                    text: "{{__('an_invoice_will_be_generated_to_credit_your_account_If_you_pay_your_invoice_your_credit_will_be_automatically_credited_to_your_account')}}",
                    cancelButtonText: '{{__('cancel')}}',
                }).then((result) => {
                    if (result.isConfirmed === true) {
                        $.ajax({
                            type: "POST",
                            url: "{{route("portal.balance.addBalancePost")}}",
                            dataType: "json",
                            data: {
                                _token: "{{csrf_token()}}",
                                balance: $("[name='balance']").val()
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                            },
                            complete: function (data, status) {
                                propSubmitButton(btn, 0);
                                res = data.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        html: res?.message ?? "",
                                    }).then(() => window.location.href = res?.redirectUrl)
                                } else {
                                    alerts.error.fire({
                                        text: res?.message ?? "{{__('form_has_errors')}}",
                                    });
                                }
                            }
                        })
                    }
                });
            })

            /*<!-- START::Balance Activity-->*/
            $("#balanceActivityTable").DataTable({
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
                    "url": "{{route("portal.balance.ajax")}}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"
                    },
                },
            }).on("draw", function () {
                KTMenu.createInstances();
                $('[data-bs-toggle="tooltip"]').tooltip();

                $('#balanceActivityTable > tbody tr').each(function (index, item) {
                    let bg = $(item).closest("tr").find('td:first span').data('bg');
                    $(item).addClass('bg-' + bg)
                })
            });
            /*<!-- END::Balance Activity-->*/
        })
    </script>
@endsection
