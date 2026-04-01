<div style="text-align: center">
    <div>
        <span style="color:#2943c4;">{{$support->user->id}} | {{$support->user->full_name}}</span> adlı müşteri tarafından destek talebi oluşturdu.<br>
        Yönetim paneline giriş yaparak görüntüleyebilirsiniz.<br>
    </div>
    <div style="padding-top: 15px; padding-bottom: 15px">
        <div style="padding-bottom: 15px">
            <div>Konu: <b>{{$support->subject}}</b></div>
            <div>Destek Talep Id: <b>#{{$support->id}}</b></div>
            <div>Departman: <b>{{$support->drawDepartment}}</b></div>
            <div>Öncelik: <b>{{$support->drawPriority}}</b></div>
        </div>
        <div>
            <a href="{{route("admin.supports.show", ["support" => $support->id])}}" rel="noopener" style="text-decoration:none;display:inline-block;text-align:center;padding:0.75575rem 1.3rem;font-size:0.925rem;line-height:1.5;border-radius:0.35rem;color:#ffffff;background-color:#006bf7;border:0px;margin-right:0.75rem!important;font-weight:600!important;outline:none!important;vertical-align:middle" target="_blank">Destek Talebini Görüntüle</a>
        </div>
    </div>

    <x-emails.admin-button/>
</div>
