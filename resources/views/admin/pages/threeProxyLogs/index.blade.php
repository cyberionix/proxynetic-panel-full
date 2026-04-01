@extends("admin.template")
@section("title", '3Proxy Logları')
@section("css") @endsection
@section("breadcrumb")
    <x-admin.bread-crumb :data="['3Proxy Logları']"/>
@endsection
@section("description", "")
@section("keywords", "")
@section("master")
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <h2>3Proxy Logları</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <form method="GET" action="{{ route('admin.threeProxyLogs.index') }}" class="row g-3 mb-6 align-items-end">
                <div class="col-auto">
                    <input type="text" name="order_id" class="form-control form-control-sm" placeholder="Sipariş ID"
                           value="{{ request('order_id') }}">
                </div>
                <div class="col-auto">
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Tüm İşlemler</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                {{ $act }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" name="ip" class="form-control form-control-sm" placeholder="IP Adresi"
                           value="{{ request('ip') }}">
                </div>
                <div class="col-auto">
                    <input type="text" name="username" class="form-control form-control-sm" placeholder="Kullanıcı Adı"
                           value="{{ request('username') }}">
                </div>
                <div class="col-auto">
                    <input type="date" name="date_from" class="form-control form-control-sm" placeholder="Başlangıç"
                           value="{{ request('date_from') }}" title="Başlangıç Tarihi">
                </div>
                <div class="col-auto">
                    <input type="date" name="date_to" class="form-control form-control-sm" placeholder="Bitiş"
                           value="{{ request('date_to') }}" title="Bitiş Tarihi">
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('admin.threeProxyLogs.index') }}" class="btn btn-light btn-sm">Temizle</a>
                    <a href="{{ route('admin.threeProxyLogs.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel me-1"></i> Export
                    </a>
                </div>
            </form>

            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start min-w-80px">Tarih</th>
                                <th class="min-w-80px">Sipariş</th>
                                <th class="min-w-70px">Müşteri</th>
                                <th class="min-w-100px">İşlem</th>
                                <th class="min-w-120px">IP Listesi</th>
                                <th class="min-w-50px">Adet</th>
                                <th class="min-w-100px">Kullanıcı / Şifre</th>
                                <th class="min-w-80px">Süre</th>
                                <th class="rounded-end min-w-80px">Detay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-dark fw-semibold d-block fs-7">{{ $log->created_at->format('d.m.Y') }}</span>
                                        <span class="text-muted d-block fs-8">{{ $log->created_at->format('H:i:s') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', ['order' => $log->order_id]) }}"
                                           class="badge badge-light-primary">#{{ $log->order_id }}</a>
                                    </td>
                                    <td>
                                        @if($log->order && $log->order->user)
                                            <a href="{{ route('admin.users.show', ['user' => $log->order->user_id]) }}"
                                               class="text-dark fw-semibold text-hover-primary fs-7">
                                                {{ $log->order->user->full_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{!! $log->action_badge !!}</td>
                                    <td>
                                        @if($log->ip_list && count($log->ip_list) > 0)
                                            @foreach(array_slice($log->ip_list, 0, 2) as $ip)
                                                <code class="d-block fs-8">{{ $ip }}</code>
                                            @endforeach
                                            @if(count($log->ip_list) > 2)
                                                <span class="text-muted fs-8">+{{ count($log->ip_list) - 2 }} daha</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light-dark">{{ $log->proxy_count }}</span>
                                    </td>
                                    <td>
                                        @if($log->username)
                                            <code class="fs-8">{{ $log->username }}</code>
                                            @if($log->password)
                                                <br><code class="fs-8 text-muted">{{ $log->password }}</code>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->duration_human)
                                            <span class="badge badge-light-info">{{ $log->duration_human }}</span>
                                        @elseif($log->started_at && !$log->ended_at)
                                            <span class="badge badge-light-success">Aktif</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->proxy_data && count($log->proxy_data) > 0)
                                            <button type="button" class="btn btn-sm btn-light-info btn-icon" data-bs-toggle="modal"
                                                    data-bs-target="#logDetailModal{{ $log->id }}">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        @elseif($log->metadata)
                                            <button type="button" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="modal"
                                                    data-bs-target="#logDetailModal{{ $log->id }}">
                                                <i class="fa fa-info-circle"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $logs->links() }}
                </div>

                @foreach($logs as $log)
                    @if($log->proxy_data || $log->metadata)
                        <div class="modal fade" id="logDetailModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Log #{{ $log->id }} - {!! $log->action_badge !!}
                                            <small class="text-muted ms-2">{{ $log->created_at->format('d.m.Y H:i:s') }}</small>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if($log->proxy_data && count($log->proxy_data) > 0)
                                            <h6 class="fw-bold mb-3">Proxy Listesi</h6>
                                            <div class="table-responsive mb-5">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>IP</th>
                                                            <th>HTTP Port</th>
                                                            <th>SOCKS Port</th>
                                                            <th>Kullanıcı</th>
                                                            <th>Şifre</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($log->proxy_data as $pdIdx => $pd)
                                                            <tr>
                                                                <td>{{ $pdIdx + 1 }}</td>
                                                                <td><code>{{ $pd['ip'] ?? '-' }}</code></td>
                                                                <td>{{ $pd['http_port'] ?? '-' }}</td>
                                                                <td>{{ $pd['socks_port'] ?? '-' }}</td>
                                                                <td><code>{{ $pd['username'] ?? '-' }}</code></td>
                                                                <td><code>{{ $pd['password'] ?? '-' }}</code></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                        @if($log->metadata)
                                            <h6 class="fw-bold mb-3">Ek Bilgiler</h6>
                                            <pre class="bg-light p-4 rounded fs-7">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @endif
                                        @if($log->started_at || $log->ended_at)
                                            <div class="row mt-4">
                                                @if($log->started_at)
                                                    <div class="col-md-4">
                                                        <span class="fw-bold d-block">Başlangıç:</span>
                                                        <span class="text-muted">{{ $log->started_at->format('d.m.Y H:i:s') }}</span>
                                                    </div>
                                                @endif
                                                @if($log->ended_at)
                                                    <div class="col-md-4">
                                                        <span class="fw-bold d-block">Bitiş:</span>
                                                        <span class="text-muted">{{ $log->ended_at->format('d.m.Y H:i:s') }}</span>
                                                    </div>
                                                @endif
                                                @if($log->duration_human)
                                                    <div class="col-md-4">
                                                        <span class="fw-bold d-block">Toplam Süre:</span>
                                                        <span class="badge badge-light-info">{{ $log->duration_human }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="alert alert-info">Henüz log kaydı bulunmuyor.</div>
            @endif
        </div>
    </div>
@endsection
@section("js") @endsection
