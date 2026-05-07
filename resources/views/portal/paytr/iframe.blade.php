@extends("portal.template")

@section("title", "Ödeme - PayTR")

@section("css")
<style>
    .paytr-wrapper { max-width: 900px; margin: 24px auto; padding: 0 16px; }
    .paytr-test-banner {
        background: linear-gradient(90deg, #f59e0b, #ef4444);
        color: #fff; padding: 14px 20px; border-radius: 10px;
        margin-bottom: 20px; font-weight: 600;
        display: flex; align-items: center; gap: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .paytr-test-banner .ico { font-size: 22px; }
    .paytr-iframe-container {
        background: #fff; border-radius: 12px; overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        min-height: 540px; position: relative;
    }
    .paytr-iframe-container iframe {
        width: 100%; min-height: 540px; border: 0; display: block;
    }
    .paytr-loading {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        background: #fafafa; color: #6b7280; flex-direction: column; gap: 12px;
    }
    .paytr-test-cards {
        background: #fef3c7; border: 1px solid #fcd34d;
        border-radius: 10px; padding: 18px 20px; margin-top: 16px; font-size: 14px;
    }
    .paytr-test-cards h4 { margin: 0 0 10px; color: #92400e; }
    .paytr-test-cards table { width: 100%; border-collapse: collapse; }
    .paytr-test-cards td { padding: 6px 8px; border-bottom: 1px solid #fde68a; font-family: monospace; }
    .paytr-test-cards td:first-child { font-weight: 600; }
</style>
@endsection

@section("master")
<div class="paytr-wrapper">

    @if($testMode)
    <div class="paytr-test-banner">
        <span class="ico">🧪</span>
        <div>
            <strong>TEST MODU AKTİF</strong> &mdash; Bu işlemde gerçek ödeme alınmaz. Test kartlarını aşağıda bulabilirsiniz.
        </div>
    </div>
    @endif

    <div class="paytr-iframe-container">
        <div class="paytr-loading" id="paytr-loading">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div>
            <span>Güvenli ödeme sayfası yükleniyor...</span>
        </div>
        <iframe id="paytriframe" src="{{ $iframeUrl }}" frameborder="0" scrolling="no"
                onload="document.getElementById('paytr-loading').style.display='none';"></iframe>
    </div>

    @if($testMode)
    <div class="paytr-test-cards">
        <h4>🃏 Test Kartları</h4>
        <table>
            @foreach(config('paytr.test_cards', []) as $card)
            <tr>
                <td>{{ $card['note'] ?? '' }}</td>
                <td>{{ $card['number'] }}</td>
                <td>SKT: {{ $card['expiry'] }}</td>
                <td>CVV: {{ $card['cvv'] }}</td>
                <td>{{ $card['name'] }}</td>
            </tr>
            @endforeach
        </table>
        <p style="margin-top: 10px; color: #92400e; font-size: 13px;">
            <strong>Not:</strong> 3D Secure simülasyonunda doğrulama kodu olarak <code>123456</code> kullanın.
        </p>
    </div>
    @endif

</div>
@endsection

@section("js")
<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
<script>
    if (typeof iFrameResize !== 'undefined') {
        iFrameResize({}, '#paytriframe');
    }
</script>
@endsection
