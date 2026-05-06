<div style="text-align: center">
    <div style="font-size: 1.7rem; margin-bottom: 25px">
        <b>{{count($cancelledInvoiceItems)}} </b> adet hizmet; yenileme faturaları ödenmediği için <b>iptal edildi</b>
    </div>

    @foreach($cancelledInvoiceItems as $invoiceItem)
        <hr>
        <div style="padding-top: 15px; padding-bottom: 15px">
            <div style="padding-bottom: 15px">
                <div>
                    Müşteri: <b>{{$invoiceItem->invoice->user->id}} | {{$invoiceItem->invoice->user->full_name}}</b> | Fatura No: <b>#{{$invoiceItem->invoice->invoice_number}}</b> | Sipariş No: <b>#{{$invoiceItem->order->id}}</b> | Son Ödeme Tarihi: <b>{{$invoiceItem->invoice->due_date->format(defaultDateFormat())}}</b>
                </div>
                <div style="margin-top: 15px">
                    <a href="{{route("admin.invoices.show", ["invoice" => $invoiceItem->invoice->id])}}" rel="noopener"
                       style="text-decoration:none;display:inline-block;text-align:center;padding:0.35rem 0.8rem;font-size:0.75rem;line-height:1.5;border-radius:0.35rem;color:#ffffff;background-color:#006bf7;border:0px;margin-right:0.75rem!important;font-weight:600!important;outline:none!important;vertical-align:middle"
                       target="_blank">Ödenmemiş Faturayı Görüntüle</a>
                    <a href="{{route("admin.orders.show", ["order" => $invoiceItem->order->id])}}" rel="noopener"
                       style="text-decoration:none;display:inline-block;text-align:center;padding:0.35rem 0.8rem;font-size:0.75rem;line-height:1.5;border-radius:0.35rem;color:#ffffff;background-color:#006bf7;border:0px;margin-right:0.75rem!important;font-weight:600!important;outline:none!important;vertical-align:middle"
                       target="_blank">İptal Edilen Hizmeti Görüntüle</a>
                </div>
            </div>
            <hr>
            @endforeach
            <x-emails.admin-button/>
        </div>
</div>
