@extends('emails.master')
@section('content')
    <div style="font-size: 14px; font-weight: 500; margin-bottom: 15px; font-family:Arial,Helvetica,sans-serif;">
        <p style="margin-bottom:5px; color:#181C32; font-size: 19px; font-weight:700">Merhaba {{$invoiceItem->invoice->user->full_name}},<br></p>
        <div>#{{$invoiceItem->order->id}} nolu hizmetiniz, #{{$invoiceItem->invoice->invoice_number}} nolu yenileme faturası ödenmediği için iptal edildi.</div>
        <br><br>
        <x-emails.portal-button/>
    </div>
@endsection
