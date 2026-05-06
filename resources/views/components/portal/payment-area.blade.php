@props([
"invoice" => null,
"showWallet" => isset($invoice) ? $invoice->paymentAreaShowWallet() : true,
"basket" => []
])

@php
    $hasPendingCheckout = false;
    if ($invoice && $invoice?->hasPendingCheckout())
        $hasPendingCheckout = true;

@endphp
@if($hasPendingCheckout)
    <div class="alert alert-primary">
        Ödeme bildiriminiz başarıyla kaydedili. Mesai saatlerinde yoğunluğa bağlı olarak 0-2 saat aralığında ödemeniz
        onaylanacaktır. Farklı sorularınız var ise bize ulaşmaktan lütfen çekinmeyin. 😊
    </div>
@endif

@if(!$invoice && $basket && @$basket->basketSummary()["real_total"] == 0)
<div>
    <div class="wallet-option-form-area text-center" style="">
        <h3 class="mb-7 text-success"><i class="text-warning fa fa-star fs-3"></i> Siparişinizi aşağıdaki butona tıklayarak onaylayabilirsiniz.</h3>
        <button type="submit" class="btn btn-primary submit-wallet-button">
            <span class="indicator-label ">Onayla</span>
            <span class="indicator-progress">{{__("please_wait")}}...
															<span
                                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
</div>

    @push('js')
        <script>
            $(document).ready(function(){

                $(document).on('click', '.submit-wallet-button', function (e) {
                    e.preventDefault();
                    var btn = $(this);
                    alerts.confirm.fire({
                        text: 'Siparişinizi onaylamak istediğinize emin misiniz?',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: '{{route('portal.paymentWithBalance')}}',
                                dataType: "json",
                                data: {
                                    _token: '{{csrf_token()}}',
                                    invoice_address_id: $("[name='invoice_address_id']").val(),
                                    invoice_id: "{{$invoice ? $invoice->id : null}}"
                                },
                                beforeSend: function () {
                                    propSubmitButton(btn, 1);
                                },
                                complete: function (xhr) {
                                    propSubmitButton(btn, 0);
                                    var res = xhr.responseJSON;
                                    if (res && res.success === true) {
                                        alerts.success.fire({
                                            title: "{{__('success')}}",
                                            text: res.message || "",
                                            showConfirmButton: false,
                                            showCancelButton: false,
                                        });
                                        setTimeout(function () { window.location.reload(); }, 1500);
                                    } else {
                                        var msg = (res && res.message) ? res.message : (xhr.statusText || "{{__('error_response')}}");
                                        alerts.error.fire({ html: msg });
                                    }
                                }
                            })
                        }
                    })
                });

            })
        </script>
    @endpush

    @else
    <div>
        <!--begin::Radio group-->
        <div class="btn-group w-100 mb-10" data-kt-buttons="true"
             data-kt-buttons-target="[data-kt-button]">
        @if(Auth::user()->security->is_limit_payment_methods == 0 || (Auth::user()->security->is_limit_payment_methods == 1 && in_array("CREDIT_CARD", Auth::user()->security->payment_methods)))
            <!--begin::Radio-->
                <label
                    class="btn btn-outline btn-color-muted btn-active-primary"
                    data-kt-button="true">
                    <!--begin::Input-->
                    <input class="btn-check" type="radio" name="payment_method"
                           checked="checked"
                           value="CREDIT_CARD"/>
                    <!--end::Input-->
                    <i class="fa fa-university me-1"></i>İş Bankası Kredi Kartı
                </label>
                <!--end::Radio-->
        @endif
        @if(Auth::user()->security->is_limit_payment_methods == 0 || (Auth::user()->security->is_limit_payment_methods == 1 && in_array("TRANSFER", Auth::user()->security->payment_methods)))
            <!--begin::Radio-->
                <label
                    class="btn btn-outline btn-color-muted btn-active-primary"
                    data-kt-button="true">
                    <!--begin::Input-->
                    <input class="btn-check" type="radio" name="payment_method" value="TRANSFER"/>
                    <!--end::Input-->
                    Havale/EFT
                </label>
                <!--end::Radio-->
        @endif
        @if(Auth::user()->security->is_limit_payment_methods == 0 || (Auth::user()->security->is_limit_payment_methods == 1 && in_array("WALLET", Auth::user()->security->payment_methods)))
            @if($showWallet)
                <!--begin::Radio-->
                    <label
                        class="btn btn-outline btn-color-muted btn-active-primary"
                        data-kt-button="true">
                        <!--begin::Input-->
                        <input class="btn-check" type="radio" name="payment_method" value="WALLET"/>
                        <!--end::Input-->
                        {{__("credit_balance")}} ({{showBalance(auth()->user()->balance, true)}})
                    </label>
                    <!--end::Radio-->
                @endif
            @endif
        </div>
        <!--end::Radio group-->
        @if(Auth::user()->security->is_limit_payment_methods == 0 || (Auth::user()->security->is_limit_payment_methods == 1 && in_array("CREDIT_CARD", Auth::user()->security->payment_methods)))
            <div class="credit-card-option-form-area" style="display: none">
                <form method="POST" id="checkoutForm" action="{{route('portal.nestpayCheckout')}}">
                @csrf
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-bold form-label mb-2">
                            <span class="required">{{__("name_on_card")}}</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" placeholder=""
                               name="card_name"
                               value="{{auth()->user()->full_name}}"/>
                    </div>
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="required fs-6 fw-bold form-label mb-2">{{__("card_number")}}</label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-solid"
                                   placeholder="XXXX XXXX XXXX XXXX"
                                   value=""
                                   name="card_number"/>
                            <div class="position-absolute translate-middle-y top-50 end-0 me-5">
                                <img src="{{assetPortal('')}}/media/svg/card-logos/visa.svg" alt=""
                                     class="h-25px"/>
                                <img src="{{assetPortal('')}}/media/svg/card-logos/mastercard.svg"
                                     alt=""
                                     class="h-25px"/>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-7">
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-bold form-label mb-2">SKT Ay</label>
                            <select name="card_exp_month" class="form-select form-select-solid"
                                    data-control="select2"
                                    data-hide-search="true" data-placeholder="Ay">
                                <option></option>
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{$i}}">{{str_pad($i,2,'0',STR_PAD_LEFT)}}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-bold form-label mb-2">SKT Yıl</label>
                            <select name="card_exp_year" class="form-select form-select-solid"
                                    data-control="select2"
                                    data-hide-search="true" data-placeholder="Yıl">
                                <option></option>
                                @php($currentYear = date('Y'))
                                @for($i=0; $i <= 30; $i++)
                                    <option
                                        value="{{mb_substr($currentYear+$i,mb_strlen($currentYear+$i)-2)}}">{{$currentYear+$i}}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-bold form-label mb-2">
                                <span class="required">CVV</span>
                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                   title="Kartın arka yüzünde yer alan 3 haneli güvenlik kodunu girmelisiniz."></i>
                            </label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-solid" minlength="3"
                                       maxlength="3"
                                       placeholder="CVV" name="card_cvv"/>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="fs-6 fw-bold form-label mb-2">Taksit Seçeneği</label>
                        <select name="installment" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                            <option value="0">Tek Çekim</option>
                            <option value="2">2 Taksit</option>
                            <option value="3">3 Taksit</option>
                            <option value="6">6 Taksit</option>
                            <option value="9">9 Taksit</option>
                            <option value="12">12 Taksit</option>
                        </select>
                    </div>
                    <div class="d-flex flex-stack">
                        <span class="text-primary"><b>{{__("pay")}}</b> butonuna tıkladıktan sonra doğrulama işlemi için bankanızın 3D Secure sayfasına yönlendirileceksiniz.</span>
                    </div>
                    <div class="text-center pt-15">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label"><i class="fa fa-university me-1"></i>{{__("make_a_payment")}}</span>
                            <span class="indicator-progress">{{__("please_wait")}}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @if(Auth::user()->security->is_limit_payment_methods == 0 || (Auth::user()->security->is_limit_payment_methods == 1 && in_array("TRANSFER", Auth::user()->security->payment_methods)))
        <div class="transfer-eft-option-form-area" style="display: none">
            <div class="text-center" id="eftStartArea">
                <button type="button" class="btn btn-primary btn-lg px-5" id="portalEftStartBtn" onclick="loadPortalEftIframe()">
                    <i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat
                </button>
                <p class="text-muted mt-3 fs-7">PayTR güvenli altyapısı ile banka havalesi yapabilirsiniz.</p>
            </div>
            <div id="portalEftIframeArea" style="display:none;">
                <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                <iframe id="portalEftIframe" frameborder="0" scrolling="no" style="width:100%; min-height:400px;"></iframe>
            </div>
            <div id="portalEftError" class="alert alert-danger mt-3" style="display:none;"></div>
        </div>
    @endif
    @if(Auth::user()->security->is_limit_payment_methods == 0 || (Auth::user()->security->is_limit_payment_methods == 1 && in_array("WALLET", Auth::user()->security->payment_methods)))
        @if($showWallet)
            <div class="wallet-option-form-area text-center" style="display: none">
                <h3 class="mb-7">{{__("pay_with_credit_balance")}}</h3>
                <button type="submit" class="btn btn-primary submit-wallet-button">
                    <span class="indicator-label ">{{__("pay")}}</span>
                    <span class="indicator-progress">{{__("please_wait")}}...
															<span
                                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        @endif
    @endif
</div>

<div id="pp"></div>
<div class="modal fade" id="paymentAreaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2>{{__("payment")}}</h2>
                <!--end::Modal title-->
                <div class="d-flex align-items-center gap-1">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body py-lg-10 px-lg-15">
                <iframe id="paymentIframe" style="width: 100%; height: 600px"></iframe>
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
@push("js")
    <script>
        $(document).ready(function () {
            $(document).on('click', 'input[name="payment_method"]', function () {
                var val = $('input[name="payment_method"]:checked').attr('value');
                $('.credit-card-option-form-area').hide(300);
                $('.transfer-eft-option-form-area').hide(300);
                $('.wallet-option-form-area').hide(300);
                if (val === 'CREDIT_CARD') {
                    $('.credit-card-option-form-area').fadeIn();
                } else if (val === 'TRANSFER') {
                    $('.transfer-eft-option-form-area').fadeIn();
                } else if (val === 'WALLET') {
                    $('.wallet-option-form-area').fadeIn();
                }
            })
            $('.copy-text').click(function () {
                // data-text özelliğine sahip metni al
                var textToCopy = $(this).data('text');

                // Kopyalama işlemi için geçici bir textarea oluştur
                var $tempTextarea = $('<textarea>');
                $('body').append($tempTextarea);
                $tempTextarea.val(textToCopy).select();
                document.execCommand('copy');
                $tempTextarea.remove();

                // Kopyalama başarılıysa kullanıcıyı bilgilendir
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toastr-top-center",
                    "preventDuplicates": true,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };

                toastr.success("Metin başarıyla kopyalandı.");
            });

            $('#checkoutForm').on('submit', function(event) {
                event.preventDefault();
                const newInput = $('<input>').attr('type', 'hidden').attr('name', 'invoice_address_id').val($("[name='invoice_address_id']").val());
                $(this).append(newInput);
                const newInput2 = $('<input>').attr('type', 'hidden').attr('name', 'invoice_id').val({{$invoice ? $invoice->id : ''}});
                $(this).append(newInput2);
                this.submit();
            });

            {{--$(document).on("submit", "#checkoutForm", function (e) {--}}
            {{--    e.preventDefault()--}}
            {{--    let form = $(this),--}}
            {{--        formData = new FormData(this);--}}

            {{--    formData.append("invoice_address_id", $("[name='invoice_address_id']").val());--}}
            {{--    formData.append("invoice_id", "{{$invoice ? $invoice->id : null}}");--}}
            {{--    $.ajax({--}}
            {{--        type: 'POST',--}}
            {{--        url: form.attr("action"),--}}
            {{--        data: formData,--}}
            {{--        dataType: 'json',--}}
            {{--        contentType: false,--}}
            {{--        processData: false,--}}
            {{--        cache: false,--}}
            {{--        beforeSend: function () {--}}
            {{--            propSubmitButton(form.find("button[type='submit']"), 1);--}}
            {{--        },--}}
            {{--        complete: function (data, status) {--}}
            {{--            res = data.responseJSON;--}}
            {{--            if (res && res.success === true) {--}}

            {{--                // $('#paymentAreaModal').modal("show");--}}
            {{--                // let paymentIframe = document.getElementById('paymentIframe'),--}}
            {{--                //     iframeDoc = paymentIframe.contentDocument || paymentIframe.contentWindow.document;--}}
            {{--                // iframeDoc.open();--}}
            {{--                iframeDoc.write(res.paymentAreaHtml);--}}
            {{--                // iframeDoc.close();--}}
            {{--            } else {--}}
            {{--                Swal.fire({--}}
            {{--                    title: "{{__('error')}}",--}}
            {{--                    text: res?.message ?? "{{__('form_has_errors')}}",--}}
            {{--                    icon: "error",--}}
            {{--                    showConfirmButton: 0,--}}
            {{--                    showCancelButton: 1,--}}
            {{--                    cancelButtonText: "{{__('close')}}",--}}
            {{--                })--}}
            {{--            }--}}
            {{--            propSubmitButton(form.find("button[type='submit']"), 0);--}}
            {{--        }--}}
            {{--    })--}}
            {{--})--}}
            window.portalEftLoaded = false;
            window.loadPortalEftIframe = function() {
                if (window.portalEftLoaded) return;
                var btn = $('#portalEftStartBtn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Yükleniyor...');
                $('#portalEftError').hide();

                var invoiceAddressId = $("[name='invoice_address_id']").val();
                if (!invoiceAddressId) {
                    btn.prop('disabled', false).html('<i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat');
                    $('#portalEftError').text('Lütfen önce fatura adresinizi seçin.').show();
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{route("portal.eftIframeToken")}}',
                    dataType: "json",
                    data: {
                        _token: '{{csrf_token()}}',
                        invoice_address_id: invoiceAddressId,
                        invoice_id: "{{$invoice ? $invoice->id : null}}"
                    },
                    success: function(res) {
                        if (res.success && res.data && res.data.iframe_token) {
                            window.portalEftLoaded = true;
                            $('#eftStartArea').hide();
                            var iframe = document.getElementById('portalEftIframe');
                            iframe.src = 'https://www.paytr.com/odeme/api/' + res.data.iframe_token;
                            $('#portalEftIframeArea').show();
                            if (typeof iFrameResize === 'function') {
                                iFrameResize({}, '#portalEftIframe');
                            }
                        } else {
                            btn.prop('disabled', false).html('<i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat');
                            $('#portalEftError').text(res.message || 'Bir hata oluştu.').show();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat');
                        $('#portalEftError').text('Bağlantı hatası. Lütfen tekrar deneyin.').show();
                    }
                });
            };

            $(document).on('click', '.submit-wallet-button', function (e) {
                e.preventDefault();
                var btn = $(this);
                alerts.confirm.fire({
                    text: 'Kredi Bakiyeniz ile ödemeyi tamamlamak istediğinize emin misiniz?',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: '{{route('portal.paymentWithBalance')}}',
                            dataType: "json",
                            data: {
                                _token: '{{csrf_token()}}',
                                invoice_address_id: $("[name='invoice_address_id']").val(),
                                invoice_id: "{{$invoice ? $invoice->id : null}}"
                            },
                            beforeSend: function () {
                                propSubmitButton(btn, 1);
                            },
                            complete: function (xhr) {
                                propSubmitButton(btn, 0);
                                var res = xhr.responseJSON;
                                if (res && res.success === true) {
                                    alerts.success.fire({
                                        title: "{{__('success')}}",
                                        text: res.message || "",
                                        showConfirmButton: false,
                                        showCancelButton: false,
                                    });
                                    setTimeout(function () { window.location.reload(); }, 1500);
                                } else {
                                    var msg = (res && res.message) ? res.message : (xhr.statusText || "{{__('error_response')}}");
                                    alerts.error.fire({ html: msg });
                                }
                            }
                        })
                    }
                })
            });
            $("input[name='payment_method']:first").trigger("click");
            $("input[name='payment_method']:first").closest("label").addClass("active")
        })
    </script>
@endpush
@endif
