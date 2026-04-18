<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura #{{ $invoice->invoice_number }} | {{ brand('title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f8fa; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .invoice-card { max-width: 900px; margin: 30px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .invoice-header { background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color: #fff; padding: 30px 40px; }
        .invoice-header .logo img { max-height: 40px; }
        .invoice-body { padding: 30px 40px; }
        .status-badge { font-size: 14px; padding: 6px 18px; border-radius: 20px; font-weight: 600; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #e5e7eb; color: #374151; }
        .info-label { color: #6b7280; font-size: 13px; font-weight: 500; }
        .info-value { color: #1f2937; font-size: 15px; font-weight: 600; }
        .items-table { border-collapse: separate; border-spacing: 0; }
        .items-table th { background: #1e3a5f; color: #fff; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 20px; border: none; }
        .items-table th:first-child { border-radius: 8px 0 0 8px; }
        .items-table th:last-child { border-radius: 0 8px 8px 0; }
        .items-table td { color: #374151; font-size: 14px; padding: 12px 20px; }
        .summary-section { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 20px; }
        .total-row { font-size: 18px; font-weight: 700; color: #1e3a5f; }
        .footer-text { color: #9ca3af; font-size: 13px; padding: 20px 40px; }
        .footer-text a { color: #2563eb; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .footer-text a:hover { color: #1d4ed8; }
        @media (max-width: 576px) {
            .invoice-header, .invoice-body { padding: 20px; }
            .items-table { font-size: 12px; }
        }
        .logo-print { display: none; }
        .payment-section { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 24px; }
        .payment-tabs .btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
        @media print {
            body { background: #fff; }
            .invoice-card { box-shadow: none; margin: 0; border-radius: 0; }
            .invoice-header { background: #fff !important; color: #1e3a5f !important; border-bottom: 2px solid #e5e7eb; }
            .status-badge { border: 1px solid currentColor; }
            .logo-screen { display: none !important; }
            .logo-print { display: inline !important; }
            .footer-text, .action-buttons, .payment-section { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <div class="invoice-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="logo mb-2">
                    <img src="{{ url(brand('logo')) }}" alt="Logo" class="logo-screen" onerror="this.style.display='none'">
                    <img src="{{ url(brand('logo_dark')) }}" alt="Logo" class="logo-print" onerror="this.style.display='none'">
                </div>
                <div class="fs-4 fw-bold">Fatura #{{ $invoice->invoice_number }}</div>
            </div>
            <div class="text-end">
                @if($invoice->status == 'PAID')
                    <span class="status-badge status-paid"><i class="fa fa-check-circle me-1"></i>Ödendi</span>
                @elseif($invoice->status == 'CANCELLED')
                    <span class="status-badge status-cancelled"><i class="fa fa-ban me-1"></i>İptal Edildi</span>
                @else
                    <span class="status-badge status-pending"><i class="fa fa-clock me-1"></i>Ödeme Bekleniyor</span>
                @endif
            </div>
        </div>

        <div class="invoice-body">
            <div class="row mb-4">
                <div class="col-sm-6 mb-3">
                    <div class="info-label mb-1">Müşteri</div>
                    <div class="info-value">{{ $invoice->user?->full_name }}</div>
                    @if($invoice->invoice_address)
                        <div class="text-muted" style="font-size:13px;">
                            {!! nl2br(e(@$invoice->invoice_address['address'] ?? '')) !!}
                            @if(@$invoice->invoice_address['district']['title'] || @$invoice->invoice_address['city']['title'])
                                <br>{{ @$invoice->invoice_address['district']['title'] }} / {{ @$invoice->invoice_address['city']['title'] }}
                            @endif
                            @if(@$invoice->invoice_address['tax_number'])
                                <br>{{ @$invoice->invoice_address['tax_number'] }}
                                @if(@$invoice->invoice_address['invoice_type'] == 'CORPORATE' && @$invoice->invoice_address['tax_office'])
                                    - {{ @$invoice->invoice_address['tax_office'] }}
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-sm-6 mb-3 text-sm-end">
                    <div class="info-label mb-1">Düzenleyen</div>
                    <div class="info-value">SAĞLAM PROXY YAZILIM LİMİTED ŞİRKETİ</div>
                    <div class="text-muted" style="font-size:13px;">
                        YAKUPLU MAH. HÜRRİYET BLV. SKYPORT Skyport Residence NO: 1 İÇ KAPI NO: 62<br>
                        BEYLİKDÜZÜ / İSTANBUL<br>
                        7381261591 - BEYLİKDÜZÜ V.D.
                    </div>
                    <div class="mt-3" style="font-size:13px;">
                        <div class="d-flex justify-content-sm-end gap-3 mb-1">
                            <span class="text-muted">Fatura Tarihi:</span>
                            <span class="info-value" style="font-size:13px;">{{ $invoice->invoice_date?->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-sm-end gap-3 mb-1">
                            <span class="text-muted">Son Ödeme Tarihi:</span>
                            <span class="info-value" style="font-size:13px;">{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-sm-end gap-3">
                            <span class="text-muted">Toplam Tutar:</span>
                            <span class="info-value" style="font-size:15px; color:#1e3a5f;">{{ showBalance($invoice->total_price_with_vat, true) }}</span>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2 justify-content-sm-end flex-wrap action-buttons">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="shareInvoice()">
                            <i class="fa fa-share-nodes me-1"></i>Paylaş
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fa fa-print me-1"></i>Yazdır
                        </button>
                        @if($invoice->invoice_pdf)
                        <a href="{{ url($invoice->invoice_pdf) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-file-pdf me-1"></i>PDF İndir
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table items-table mb-0">
                    <thead>
                        <tr>
                            <th>Ürün / Hizmet</th>
                            <th class="text-end">Fiyat</th>
                            <th class="text-end">KDV</th>
                            <th class="text-end">Tutar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td class="text-end">{{ showBalance($item->total_price ?? 0, true) }}</td>
                                <td class="text-end">%{{ $item->vat_percent ?? 0 }}</td>
                                <td class="text-end fw-bold">{{ showBalance($item->total_price_with_vat ?? 0, true) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <div class="summary-section" style="min-width: 300px;">
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Ara Toplam:</span>
                        <span class="fw-semibold">{{ showBalance($invoice->total_price, true) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">KDV:</span>
                        <span class="fw-semibold">{{ showBalance($invoice->total_vat, true) }}</span>
                    </div>
                    @if($invoice->discount_amount)
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">İndirim:</span>
                            <span class="fw-semibold text-success">-{{ showBalance($invoice->discount_amount, true) }}</span>
                        </div>
                    @endif
                    <hr class="my-1">
                    <div class="d-flex justify-content-between total-row py-2">
                        <span>Toplam:</span>
                        <span>{{ showBalance($invoice->total_price_with_vat, true) }}</span>
                    </div>
                </div>
            </div>

            @if(session('payment_status'))
                <div class="alert {{ session('payment_status') == 'success' ? 'alert-success' : 'alert-danger' }} d-flex align-items-center" role="alert">
                    <i class="fa {{ session('payment_status') == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-2"></i>
                    {{ session('payment_message') }}
                </div>
            @endif

            @if($invoice->status == 'PENDING')
                <div class="payment-section mt-2">
                    <h5 class="mb-3 fw-bold" style="color:#1e3a5f;"><i class="fa fa-lock me-2"></i>Faturayı Öde</h5>

                    <div class="payment-tabs mb-3">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="tabCard" onclick="showPaymentTab('card')">
                                <i class="fa fa-credit-card me-1"></i>Kredi Kartı
                            </button>
                            @if(env('NESTPAY_ENABLED', false))
                            <button type="button" class="btn btn-outline-primary" id="tabNestpay" onclick="showPaymentTab('nestpay')">
                                <i class="fa fa-university me-1"></i>İşbank Kartı
                            </button>
                            @endif
                            <button type="button" class="btn btn-outline-primary" id="tabEft" onclick="showPaymentTab('eft')">
                                <i class="fa fa-building-columns me-1"></i>Havale / EFT
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="tabBalance" onclick="showPaymentTab('balance')">
                                <i class="fa fa-wallet me-1"></i>Bakiye
                            </button>
                        </div>
                    </div>

                    <div id="cardPaymentArea">
                        <form method="POST" action="{{ route('public.invoice.checkout') }}" id="publicCheckoutForm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $invoice->share_token }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kart Üzerindeki İsim</label>
                                <input type="text" class="form-control" name="card_name" value="{{ $invoice->user?->full_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kart Numarası</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="16" required>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3 d-flex gap-1">
                                        <img src="https://cdn.jsdelivr.net/gh/nicepay-dev/nicepay-images@main/visa.svg" alt="Visa" height="20" onerror="this.style.display='none'">
                                        <img src="https://cdn.jsdelivr.net/gh/nicepay-dev/nicepay-images@main/mastercard.svg" alt="MC" height="20" onerror="this.style.display='none'">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Ay</label>
                                    <select name="card_exp_month" class="form-select" required>
                                        <option value="">Ay</option>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Yıl</label>
                                    <select name="card_exp_year" class="form-select" required>
                                        <option value="">Yıl</option>
                                        @for($y = (int)date('y'); $y <= (int)date('y') + 10; $y++)
                                            <option value="{{ $y }}">20{{ str_pad($y, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">CVV</label>
                                    <input type="text" class="form-control" name="card_cvv" placeholder="***" maxlength="3" required>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted" style="font-size:12px;"><i class="fa fa-shield-halved me-1 text-success"></i>Ödemeniz 3D Secure ile güvenli şekilde işlenir.</span>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="payBtn">
                                <i class="fa fa-lock me-1"></i>{{ showBalance($invoice->total_price_with_vat, true) }} Öde
                            </button>
                        </form>
                    </div>

                    @if(env('NESTPAY_ENABLED', false))
                    <div id="nestpayPaymentArea" style="display:none;">
                        <form method="POST" action="{{ route('public.invoice.nestpayCheckout') }}" id="publicNestpayForm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $invoice->share_token }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kart Üzerindeki İsim</label>
                                <input type="text" class="form-control" name="card_name" value="{{ $invoice->user?->full_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kart Numarası</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="16" required>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3 d-flex gap-1">
                                        <img src="https://cdn.jsdelivr.net/gh/nicepay-dev/nicepay-images@main/visa.svg" alt="Visa" height="20" onerror="this.style.display='none'">
                                        <img src="https://cdn.jsdelivr.net/gh/nicepay-dev/nicepay-images@main/mastercard.svg" alt="MC" height="20" onerror="this.style.display='none'">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-3">
                                    <label class="form-label fw-semibold">Ay</label>
                                    <select name="card_exp_month" class="form-select" required>
                                        <option value="">Ay</option>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="form-label fw-semibold">Yıl</label>
                                    <select name="card_exp_year" class="form-select" required>
                                        <option value="">Yıl</option>
                                        @for($y = (int)date('y'); $y <= (int)date('y') + 10; $y++)
                                            <option value="{{ $y }}">20{{ str_pad($y, 2, '0', STR_PAD_LEFT) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label class="form-label fw-semibold">CVV</label>
                                    <input type="text" class="form-control" name="card_cvv" placeholder="***" maxlength="3" required>
                                </div>
                                <div class="col-3">
                                    <label class="form-label fw-semibold">Taksit</label>
                                    <select name="installment" class="form-select">
                                        <option value="0">Tek Çekim</option>
                                        <option value="2">2 Taksit</option>
                                        <option value="3">3 Taksit</option>
                                        <option value="6">6 Taksit</option>
                                        <option value="9">9 Taksit</option>
                                        <option value="12">12 Taksit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted" style="font-size:12px;"><i class="fa fa-shield-halved me-1 text-success"></i>Ödemeniz İşbank 3D Secure ile güvenli şekilde işlenir.</span>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="nestpayPayBtn">
                                <i class="fa fa-university me-1"></i>{{ showBalance($invoice->total_price_with_vat, true) }} Öde
                            </button>
                        </form>
                    </div>
                    @endif

                    <div id="eftPaymentArea" style="display:none;">
                        <div class="text-center py-3" id="eftLoading">
                            <button type="button" class="btn btn-success btn-lg px-5" id="eftStartBtn" onclick="loadEftIframe()">
                                <i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat
                            </button>
                            <p class="text-muted mt-2" style="font-size:12px;">PayTR güvenli altyapısı ile banka havalesi yapabilirsiniz.</p>
                        </div>
                        <div id="eftIframeArea" style="display:none;">
                            <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                            <iframe id="eftIframe" frameborder="0" scrolling="no" style="width:100%; min-height:400px;"></iframe>
                        </div>
                        <div id="eftError" class="alert alert-danger" style="display:none;"></div>
                    </div>

                    <div id="balancePaymentArea" style="display:none;">
                        <div class="text-center py-4">
                            <i class="fa fa-wallet fa-3x text-primary mb-3"></i>
                            <p class="text-muted mb-3">Bakiye ile ödeme yapabilmek için müşteri paneline giriş yapmanız gerekmektedir.</p>
                            <a href="{{ route('portal.invoices.index') }}" class="btn btn-primary px-4">
                                <i class="fa fa-sign-in-alt me-1"></i>Giriş Yap ve Bakiye ile Öde
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <div class="footer-text">
            <a href="{{ route('portal.invoices.index') }}"><i class="fa fa-arrow-left me-1"></i>Faturalarıma geri dön</a>
        </div>
    </div>

    <script>
        function shareInvoice() {
            var url = window.location.href;
            if (navigator.share) {
                navigator.share({ title: 'Fatura #{{ $invoice->invoice_number }}', url: url });
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Fatura linki kopyalandı!');
                }).catch(function() {
                    prompt('Fatura linkini kopyalayın:', url);
                });
            }
        }

        function showPaymentTab(tab) {
            document.getElementById('tabCard').classList.toggle('active', tab === 'card');
            var tabNestpay = document.getElementById('tabNestpay');
            if (tabNestpay) tabNestpay.classList.toggle('active', tab === 'nestpay');
            document.getElementById('tabEft').classList.toggle('active', tab === 'eft');
            document.getElementById('tabBalance').classList.toggle('active', tab === 'balance');
            document.getElementById('cardPaymentArea').style.display = tab === 'card' ? 'block' : 'none';
            var nestpayArea = document.getElementById('nestpayPaymentArea');
            if (nestpayArea) nestpayArea.style.display = tab === 'nestpay' ? 'block' : 'none';
            document.getElementById('eftPaymentArea').style.display = tab === 'eft' ? 'block' : 'none';
            document.getElementById('balancePaymentArea').style.display = tab === 'balance' ? 'block' : 'none';
        }

        var eftLoaded = false;
        function loadEftIframe() {
            if (eftLoaded) return;
            var btn = document.getElementById('eftStartBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Yükleniyor...';
            document.getElementById('eftError').style.display = 'none';

            fetch('{{ route("public.invoice.eftIframe") }}?token={{ $invoice->share_token }}')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success && data.iframe_token) {
                        eftLoaded = true;
                        document.getElementById('eftLoading').style.display = 'none';
                        var iframeArea = document.getElementById('eftIframeArea');
                        var iframe = document.getElementById('eftIframe');
                        iframe.src = 'https://www.paytr.com/odeme/api/' + data.iframe_token;
                        iframeArea.style.display = 'block';
                        if (typeof iFrameResize === 'function') {
                            iFrameResize({}, '#eftIframe');
                        }
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat';
                        var err = document.getElementById('eftError');
                        err.textContent = data.message || 'Bir hata oluştu.';
                        err.style.display = 'block';
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-building-columns me-2"></i>Havale/EFT ile Ödeme Başlat';
                    var err = document.getElementById('eftError');
                    err.textContent = 'Bağlantı hatası. Lütfen tekrar deneyin.';
                    err.style.display = 'block';
                });
        }

        document.getElementById('publicCheckoutForm')?.addEventListener('submit', function() {
            var btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>İşleniyor...';
        });

        document.getElementById('publicNestpayForm')?.addEventListener('submit', function() {
            var btn = document.getElementById('nestpayPayBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>İşleniyor...';
        });

    </script>
</body>
</html>
