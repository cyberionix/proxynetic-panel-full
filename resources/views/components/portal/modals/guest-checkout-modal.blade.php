<!--begin::Guest Checkout Modal-->
<div class="modal fade" id="guestCheckoutModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-500px">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 py-4">
                <h2 class="fw-bold m-0">{{__("Siparişi Tamamla")}}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted fs-6 mb-5">{{__("Devam etmek için kayıt olun veya giriş yapın. E-posta ve telefon doğrulama ödeme sonrasında istenecektir.")}}</p>

                {{-- Tab navigation --}}
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" id="guestCheckoutTabs">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#guestRegisterTab">
                            <i class="fa fa-user-plus me-2"></i>{{__("Kayıt Ol")}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#guestLoginTab">
                            <i class="fa fa-sign-in-alt me-2"></i>{{__("Giriş Yap")}}
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Register tab --}}
                    <div class="tab-pane fade show active" id="guestRegisterTab">
                        <form id="guestRegisterForm" novalidate>
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold required">{{__("Ad")}}</label>
                                    <input type="text" name="firstName" class="form-control form-control-solid" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold required">{{__("Soyad")}}</label>
                                    <input type="text" name="lastName" class="form-control form-control-solid" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold required">{{__("E-posta")}}</label>
                                <input type="email" name="email" class="form-control form-control-solid" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold required">{{__("Şifre")}}</label>
                                <input type="password" name="password" class="form-control form-control-solid" required minlength="8">
                                <div class="form-text fs-7">{{__("En az 8 karakter, harf ve sayı içermeli")}}</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold required">{{__("Şifre (Tekrar)")}}</label>
                                <input type="password" name="confirm-password" class="form-control form-control-solid" required>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="toc" value="1" id="guestRegisterToc" required>
                                <label class="form-check-label fs-7" for="guestRegisterToc">
                                    {{__("Kullanım koşullarını kabul ediyorum")}}
                                </label>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                                <span class="indicator-label">{{__("Kayıt Ol & Ödemeye Geç")}} <i class="fa fa-arrow-right ms-2"></i></span>
                                <span class="indicator-progress">{{__("İşleniyor")}}... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    {{-- Login tab --}}
                    <div class="tab-pane fade" id="guestLoginTab">
                        <form id="guestLoginForm" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold required">{{__("E-posta")}}</label>
                                <input type="email" name="email" class="form-control form-control-solid" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold required">{{__("Şifre")}}</label>
                                <input type="password" name="password" class="form-control form-control-solid" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                                <span class="indicator-label">{{__("Giriş Yap & Ödemeye Geç")}} <i class="fa fa-arrow-right ms-2"></i></span>
                                <span class="indicator-progress">{{__("İşleniyor")}}... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <div class="text-center mt-3">
                                <a href="{{route('portal.auth.login')}}" class="text-muted fs-7">{{__("Şifremi unuttum")}}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    function gcSubmit(form, url, btn) {
        btn.prop('disabled', true).addClass('disabled');
        btn.find('.indicator-label').addClass('d-none');
        btn.find('.indicator-progress').removeClass('d-none').css('display', 'inline-block');
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            dataType: "json",
            success: function (res) {
                if (res && res.success) {
                    // Always redirect to payment for guest checkout flow
                    window.location.href = res.redirectUrl || "{{route('portal.basket.payment.index')}}";
                } else {
                    btn.prop('disabled', false).removeClass('disabled');
                    btn.find('.indicator-label').removeClass('d-none');
                    btn.find('.indicator-progress').addClass('d-none');
                    let msg = (res && res.message) || "{{__('Bir hata oluştu')}}";
                    if (res && res.errors) {
                        let firstErr = Object.values(res.errors)[0];
                        if (Array.isArray(firstErr)) firstErr = firstErr[0];
                        msg = firstErr || msg;
                    }
                    if (window.Swal) Swal.fire({title: "{{__('error')}}", text: msg, icon: 'error'});
                    else alert(msg);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).removeClass('disabled');
                btn.find('.indicator-label').removeClass('d-none');
                btn.find('.indicator-progress').addClass('d-none');
                let msg = "{{__('Bağlantı hatası')}}";
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        let firstErr = Object.values(xhr.responseJSON.errors)[0];
                        if (Array.isArray(firstErr)) firstErr = firstErr[0];
                        msg = firstErr || msg;
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                if (window.Swal) Swal.fire({title: "{{__('error')}}", text: msg, icon: 'error'});
                else alert(msg);
            }
        });
    }

    $('#guestRegisterForm').on('submit', function (e) {
        e.preventDefault();
        gcSubmit($(this), "{{route('portal.auth.registerPost')}}", $(this).find('button[type=submit]'));
    });
    $('#guestLoginForm').on('submit', function (e) {
        e.preventDefault();
        gcSubmit($(this), "{{route('portal.auth.loginPost')}}", $(this).find('button[type=submit]'));
    });
});
</script>
<!--end::Guest Checkout Modal-->
