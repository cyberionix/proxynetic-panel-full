@extends("admin.template")
@section("title", __("Otomatik Üretilen Ürünler"))
@section("breadcrumb")
    <x-admin.bread-crumb :data="__('Otomatik Üretilen Ürünler')"/>
@endsection
@section("master")
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{__("Otomatik Üretilen Ürünler")}} <span class="badge badge-light-info ms-2">{{ $products->total() }}</span></h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>{{__("Ürün")}}</th>
                    <th>{{__("Kategori")}}</th>
                    <th>{{__("Tip")}}</th>
                    <th>{{__("Adet/GB")}}</th>
                    <th>{{__("Süre (gün)")}}</th>
                    <th>{{__("Fiyat")}}</th>
                    <th>{{__("Oluşturulma")}}</th>
                    <th>{{__("İşlemler")}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? "-" }}</td>
                        <td><span class="badge badge-light-info">{{ $product->auto_meta["type_code"] ?? "-" }}</span></td>
                        <td>{{ $product->auto_meta["quantity"] ?? "-" }} {{ $product->auto_meta["unit"] ?? "" }}</td>
                        <td>{{ $product->auto_meta["duration_days"] ?? "-" }}</td>
                        <td>{{ $product->prices->first()?->price ?? "-" }} ₺</td>
                        <td>{{ $product->created_at?->format("Y-m-d H:i") }}</td>
                        <td>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-light-primary">{{__("Düzenle")}}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </div>
</div>
@endsection
