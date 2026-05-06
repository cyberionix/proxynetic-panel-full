@extends("admin.template")
@section("title", "PProxy Ayarları")
@section("breadcrumb")
    <x-admin.bread-crumb :data="['PProxy Ayarları']"/>
@endsection
@section("master")
    <div class="card">
        <div class="card-header">
            <h3 class="card-title fw-bold">Modül Ayarları</h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-sm btn-light-info" id="testConnectionBtn">
                    <i class="fa fa-plug me-1"></i> Bağlantıyı Test Et
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="pproxySettingsForm">
                @csrf
                <div class="row g-5">
                    <div class="col-md-8">
                        <label class="form-label fw-bold required">API Key</label>
                        <input type="text" name="api_key" class="form-control"
                               value="{{ $settings->api_key ?? '' }}"
                               placeholder="PlainProxies_xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold required">Server Domain</label>
                        <input type="text" name="server_domain" class="form-control"
                               value="{{ $settings->server_domain ?? 'tr.saglamproxy.com' }}"
                               placeholder="tr.saglamproxy.com">
                        <div class="form-text">Proxy gateway adresi. Ürün ayarlarından da override edilebilir.</div>
                    </div>
                </div>
                <div class="mt-8 text-end">
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <span class="indicator-label"><i class="fa fa-save me-1"></i> Kaydet</span>
                        <span class="indicator-progress">Bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section("js")
    <script>
        $(document).ready(function () {
            $('#pproxySettingsForm').on('submit', function (e) {
                e.preventDefault();
                let btn = $('#saveBtn');
                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.pproxy.saveSettings") }}',
                    data: $(this).serialize(),
                    dataType: 'json',
                    beforeSend: () => propSubmitButton(btn, 1),
                    complete: function (data) {
                        propSubmitButton(btn, 0);
                        let res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({title: 'Başarılı', text: res.message, icon: 'success', confirmButtonText: 'Tamam'});
                        } else {
                            Swal.fire({title: 'Hata', text: res?.message ?? 'Bir hata oluştu', icon: 'error', confirmButtonText: 'Tamam'});
                        }
                    }
                });
            });

            $('#testConnectionBtn').on('click', function () {
                let btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Test ediliyor...');
                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.pproxy.testConnection") }}',
                    data: {_token: '{{ csrf_token() }}'},
                    dataType: 'json',
                    complete: function (data) {
                        btn.prop('disabled', false).html('<i class="fa fa-plug me-1"></i> Bağlantıyı Test Et');
                        let res = data.responseJSON;
                        if (res && res.success) {
                            Swal.fire({title: 'Başarılı', text: res.message, icon: 'success', confirmButtonText: 'Tamam'});
                        } else {
                            Swal.fire({title: 'Hata', text: res?.message ?? 'Bağlantı başarısız', icon: 'error', confirmButtonText: 'Tamam'});
                        }
                    }
                });
            });
        });
    </script>
@endsection
