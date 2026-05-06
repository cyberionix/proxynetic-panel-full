@extends("admin.template")
@section("title", 'Localtonetv4 teslimat ayarları')
@section("css") @endsection
@section("description", "")
@section("keywords", "")
@section("breadcrumb")
    <x-admin.bread-crumb data="Localtonetv4"/>
@endsection
@section("master")
    @if(session()->has('form_success'))
        <div class="alert alert-success">{{ session()->get('form_success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold m-0">Localtonetv4 — teslimat ayarları</h3>
            </div>
        </div>
        <div class="card-body pt-0">
            <p class="text-muted mb-6">
                Her IPv4 (Localtonetv4) teslimatında tünel oluşturulduktan sonra Localtonet v2
                <code>PATCH …/api/v2/tunnels/{id}/arguments</code> ile havuz IP’si için
                <code>--net … --ip …</code> gönderilir. Ağ arayüzü adı (ör. <code>Ethernet0</code>) aşağıdan ayarlanır.
                Ardından port aralığından <strong>rastgele bir port</strong> seçilir (HTTP / SOCKS sipariş seçiminden bağımsız).
            </p>
            <form action="{{ route('admin.localtonetV4.updateSettings') }}" method="POST" class="w-100 w-lg-50">
                @csrf
                <div class="mb-5">
                    <label class="form-label required" for="tunnel_net_interface">Tünel — ağ arayüzü (<code>--net</code>)</label>
                    <input type="text" class="form-control font-monospace" id="tunnel_net_interface" name="tunnel_net_interface"
                           maxlength="64" required value="{{ old('tunnel_net_interface', $tunnelNetInterface) }}"
                           placeholder="Ethernet0">
                    <div class="form-text">Örnek: <code>Ethernet0</code>. Ürün <code>delivery_items.v4_tunnel_net</code> ile ürün bazında geçersiz kılınabilir.</div>
                </div>
                <div class="mb-5">
                    <label class="form-label required" for="random_port_min">Minimum port</label>
                    <input type="number" class="form-control" id="random_port_min" name="random_port_min"
                           min="1024" max="65535" required value="{{ old('random_port_min', $randomPortMin) }}">
                </div>
                <div class="mb-8">
                    <label class="form-label required" for="random_port_max">Maksimum port</label>
                    <input type="number" class="form-control" id="random_port_max" name="random_port_max"
                           min="1024" max="65535" required value="{{ old('random_port_max', $randomPortMax) }}">
                </div>
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </div>
    </div>
@endsection
