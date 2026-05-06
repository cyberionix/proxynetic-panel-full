@extends('emails.master')
@section('content')
    <div style="font-size: 14px; font-weight: 500; margin-bottom: 15px; font-family:Arial,Helvetica,sans-serif;">
        <p style="margin-bottom:5px; color:#181C32; font-size: 19px; font-weight:700">Merhaba {{$user->full_name}},<br></p>
        <div>#{{$support->id}} nolu destek talebiniz yanıtlanmıştır.<br>Müşteri paneline giriş yaparak görüntüleyebilirsiniz.</div>
        <br><br>
        <x-emails.portal-button/>
    </div>
@endsection
