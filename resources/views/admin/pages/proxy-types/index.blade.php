@extends("admin.template")
@section("title", __("Proxy Tip Ayarları"))
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('Proxy Tip Ayarları')"/>
@endsection
@section("master")
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{route('admin.proxyTypes.autoProducts')}}" class="btn btn-light-primary">
                <i class="fa fa-list me-2"></i>{{__("Otomatik Üretilen Ürünler")}}
            </a>
        </div>
    </div>

    @foreach($types as $type)
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ $type->display_name }} <span class="badge badge-light-info ms-2">{{$type->type_code}}</span></h3>
        </div>
        <form action="{{ route('admin.proxyTypes.update', $type->id) }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{__("Maksimum Adet/Miktar")}}</label>
                        <input type="number" name="max_quantity" value="{{ $type->max_quantity }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{__("Birim")}}</label>
                        <select name="quantity_unit" class="form-control">
                            <option value="PROXY" {{ $type->quantity_unit=='PROXY'?'selected':''}}>Adet</option>
                            <option value="GB" {{ $type->quantity_unit=='GB'?'selected':''}}>GB</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{__("Teslim Tipi")}}</label>
                        <select name="delivery_type" class="form-control" required>
                            @foreach($deliveryTypes as $dt)
                                <option value="{{$dt}}" {{$type->delivery_type==$dt?'selected':''}}>{{$dt}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">{{__("Kategori")}}</label>
                        <select name="category_id" class="form-control">
                            <option value="">-</option>
                            @foreach($categories as $c)
                                <option value="{{$c->id}}" {{$type->category_id==$c->id?'selected':''}}>{{$c->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{__("Delivery Items Template (JSON)")}}</label>
                        <textarea name="delivery_items_template_json" class="form-control font-monospace" rows="3">{{ json_encode($type->delivery_items_template, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{__("Varsayılan Özellikler")}}</label>
                        <textarea name="default_properties" class="form-control" rows="3">{{ $type->default_properties }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $type->is_active ? "checked" : "" }}>
                            <label class="form-check-label">{{__("Aktif")}}</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h4 class="fw-bold mb-3">{{__("Fiyat Aralıkları")}}</h4>
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Süre (Gün)</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Birim Fiyat</th>
                            <th>Aktif</th>
                            <th>Sil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($type->tiers as $tier)
                        <tr>
                            <td><input type="number" name="tiers[{{$tier->id}}][duration_days]" value="{{$tier->duration_days}}" class="form-control"></td>
                            <td><input type="number" name="tiers[{{$tier->id}}][min_quantity]" value="{{$tier->min_quantity}}" class="form-control"></td>
                            <td><input type="number" name="tiers[{{$tier->id}}][max_quantity]" value="{{$tier->max_quantity}}" class="form-control"></td>
                            <td><input type="text" name="tiers[{{$tier->id}}][price_per_unit]" value="{{$tier->price_per_unit}}" class="form-control"></td>
                            <td><input type="checkbox" name="tiers[{{$tier->id}}][is_active]" value="1" {{$tier->is_active?"checked":""}}></td>
                            <td><input type="checkbox" name="tiers[{{$tier->id}}][delete]" value="1"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-muted fs-7">{{__("Yeni satır eklemek için inceleme menüsünden, mevcut satırı silmek için 'Sil' kutusunu işaretleyin.")}}</div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">{{__("Kaydet")}}</button>
            </div>
        </form>
    </div>
    @endforeach
</div>
@endsection
