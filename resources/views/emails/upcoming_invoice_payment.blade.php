@extends('emails.master')
@section('content')
    <div style="font-size: 14px; font-weight: 500; margin-bottom: 15px; font-family:Arial,Helvetica,sans-serif;">
        <p style="margin-bottom:5px; color:#181C32; font-size: 19px; font-weight:700">
            Merhaba {{$invoice->user->full_name}},<br></p>
        <div>#{{$invoice->invoice_number}} numaralı, {{showBalance($invoice->total_price_with_vat, true)}} tutarındaki
            faturanızın son ödeme tarihi yaklaşıyor.
        </div>
        <div style="padding-bottom: 15px">
            <div>Fatura Numarası: <b>{{$invoice->invoice_number}}</b></div>
            <div>Toplam Tutar: <b>{{showBalance($invoice->total_price_with_vat, true)}}</b></div>
            <div>Son Ödeme Tarihi: <b>{{$invoice->due_date->format(defaultDateFormat())}}</b></div>
        </div>
        <div style="margin-top:15px; color: #99A1B7;">Fatura son ödeme tarihine kadar ödenmezse hizmet otomatik olarak
            iptal edilecektir.
        </div>
        <div>
            <a href="{{route("portal.invoices.show", ["invoice" => $invoice->id])}}" rel="noopener"
               style="text-decoration:none;display:inline-block;text-align:center;padding:0.75575rem 1.3rem;font-size:0.925rem;line-height:1.5;border-radius:0.35rem;color:#ffffff;background-color:#006bf7;border:0px;margin-right:0.75rem!important;font-weight:600!important;outline:none!important;vertical-align:middle"
               target="_blank">Fatura Görüntüle</a>
        </div>
        <br><br>
        <x-emails.portal-button/>
    </div>
@endsection
