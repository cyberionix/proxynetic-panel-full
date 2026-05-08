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
                        <form id="guestRegisterForm" action="{{route('portal.auth.registerPost')}}" method="POST" novalidate
                              onsubmit="return false;">
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
                            <button type="button" id="guestRegisterSubmit" class="btn btn-success w-100 py-3 fw-bold">
                                <span class="indicator-label">{{__("Kayıt Ol & Ödemeye Geç")}} <i class="fa fa-arrow-right ms-2"></i></span>
                                <span class="indicator-progress" style="display:none;">{{__("İşleniyor")}}... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </form>
                    </div>

                    {{-- Login tab --}}
                    <div class="tab-pane fade" id="guestLoginTab">
                        <form id="guestLoginForm" action="{{route('portal.auth.loginPost')}}" method="POST" novalidate
                              onsubmit="return false;">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold required">{{__("E-posta")}}</label>
                                <input type="email" name="email" class="form-control form-control-solid" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold required">{{__("Şifre")}}</label>
                                <input type="password" name="password" class="form-control form-control-solid" required>
                            </div>
                            <button type="button" id="guestLoginSubmit" class="btn btn-primary w-100 py-3 fw-bold">
                                <span class="indicator-label">{{__("Giriş Yap & Ödemeye Geç")}} <i class="fa fa-arrow-right ms-2"></i></span>
                                <span class="indicator-progress" style="display:none;">{{__("İşleniyor")}}... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
<!--end::Guest Checkout Modal-->

@push('js')
<script>
(function() {
    var FALLBACK_REDIRECT = "{{route('portal.basket.payment.index')}}";

    function gcShowError(msg) {
        if (window.Swal) Swal.fire({title:"{{__('error')}}", text: msg, icon:'error'});
        else alert(msg);
    }

    function gcSubmit(formId, btnId) {
        var form = document.getElementById(formId);
        var btn = document.getElementById(btnId);
        if (!form || !btn) return;
        var fd = new FormData(form);
        // Disable button
        btn.disabled = true;
        var label = btn.querySelector('.indicator-label');
        var progress = btn.querySelector('.indicator-progress');
        if (label) label.style.display = 'none';
        if (progress) progress.style.display = 'inline-block';

        fetch(form.action, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) { return r.json().then(function(d) { return {status: r.status, body: d}; }); })
        .then(function(o) {
            var d = o.body;
            if (d && d.success) {
                window.location.href = d.redirectUrl || FALLBACK_REDIRECT;
                return;
            }
            // Re-enable button on error
            btn.disabled = false;
            if (label) label.style.display = '';
            if (progress) progress.style.display = 'none';
            var msg = (d && d.message) || "{{__('Bir hata oluştu')}}";
            if (d && d.errors) {
                var firstKey = Object.keys(d.errors)[0];
                var firstVal = d.errors[firstKey];
                msg = Array.isArray(firstVal) ? firstVal[0] : firstVal;
            }
            gcShowError(msg);
        })
        .catch(function(err) {
            btn.disabled = false;
            if (label) label.style.display = '';
            if (progress) progress.style.display = 'none';
            gcShowError("{{__('Bağlantı hatası')}}: " + (err && err.message ? err.message : ''));
        });
    }

    function attachWhenReady() {
        var rb = document.getElementById('guestRegisterSubmit');
        var lb = document.getElementById('guestLoginSubmit');
        if (rb) rb.addEventListener('click', function(e) { e.preventDefault(); gcSubmit('guestRegisterForm', 'guestRegisterSubmit'); });
        if (lb) lb.addEventListener('click', function(e) { e.preventDefault(); gcSubmit('guestLoginForm', 'guestLoginSubmit'); });
        // Also support Enter key inside form
        ['guestRegisterForm','guestLoginForm'].forEach(function(fid) {
            var f = document.getElementById(fid);
            if (f) f.addEventListener('submit', function(e) {
                e.preventDefault();
                gcSubmit(fid, fid === 'guestRegisterForm' ? 'guestRegisterSubmit' : 'guestLoginSubmit');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachWhenReady);
    } else {
        attachWhenReady();
    }
})();
</script>
@endpush
