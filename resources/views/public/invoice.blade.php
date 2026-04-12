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
        @media print {
            body { background: #fff; }
            .invoice-card { box-shadow: none; margin: 0; border-radius: 0; }
            .footer-text, .action-buttons { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <div class="invoice-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="logo mb-2">
                    <img src="{{ url(brand('logo')) }}" alt="Logo" onerror="this.style.display='none'">
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

            <div class="summary-section mb-4">
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
    </script>
</body>
</html>
