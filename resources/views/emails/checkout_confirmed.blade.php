@extends('emails.master')
@section('content')
    <div style="font-size: 14px; font-weight: 500; margin-bottom: 15px; font-family:Arial,Helvetica,sans-serif;">
        <p style="margin-bottom:5px; color:#181C32; font-size: 19px; font-weight:700">Merhaba {{$user->full_name}},<br>
        </p>
        @if($user->month_of_subs < 2)
            <div style="padding-bottom: 10px">İngilizce Bilen Çocuklar'a hoş geldiniz!<br>Sistemimize katıldığınız için <b>heyecanlıyız</b>.<br><br></div>
        @endif
     <div>   Ödemeniz tarafımıza ulaşmıştır, teşekkür ederiz.<br>Müşteri paneline giriş yaparak görüşmemiz için müsait tarihler arasından randevunuzu planlayabilirsiniz.</div>
        <br><br>

        <x-emails.portal-button/>

    </div>
@endsection
