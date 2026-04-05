<?php

namespace App\Console\Commands;

use App\Models\NotificationTemplate;
use Illuminate\Console\Command;

class UpdateEmailTemplates extends Command
{
    protected $signature = 'templates:update-design';
    protected $description = 'Update all notification email templates with modern design';

    public function handle()
    {
        $templates = $this->getTemplates();

        foreach ($templates as $key => $content) {
            $t = NotificationTemplate::where('key', $key)->first();
            if ($t) {
                $t->mail_content = $content;
                $t->save();
                $this->info("Updated: {$key}");
            } else {
                $this->warn("Not found: {$key}");
            }
        }

        $this->info('All templates updated!');
        return 0;
    }

    private function getTemplates(): array
    {
        return [
            'welcome' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Hoş Geldiniz! 👋</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;">Proxynetic ailesine katıldığınız için teşekkür ederiz. Hesabınız başarıyla oluşturulmuştur.</p>
<p style="margin:0 0 24px 0;color:#475569;">Hemen giriş yaparak hizmetlerimizden yararlanmaya başlayabilirsiniz.</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Panele Git →</a>
</div>',

            'invoice_created' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Faturanız Oluşturuldu 📋</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanız oluşturulmuştur.</p>
<div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin:0 0 24px 0;">
    <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:8px 0;color:#64748b;font-size:13px;">Fatura No</td><td style="padding:8px 0;text-align:right;font-weight:600;color:#1e293b;">#{{fatura_no}}</td></tr>
        <tr><td style="padding:8px 0;border-top:1px solid #e2e8f0;color:#64748b;font-size:13px;">Tutar</td><td style="padding:8px 0;border-top:1px solid #e2e8f0;text-align:right;font-weight:700;color:#059669;font-size:16px;">{{tutar}} TL</td></tr>
        <tr><td style="padding:8px 0;border-top:1px solid #e2e8f0;color:#64748b;font-size:13px;">Son Ödeme Tarihi</td><td style="padding:8px 0;border-top:1px solid #e2e8f0;text-align:right;font-weight:600;color:#dc2626;">{{son_odeme_tarihi}}</td></tr>
    </table>
</div>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/invoices" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Faturayı Görüntüle →</a>
</div>',

            'invoice_refunded' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Fatura İade Edildi 💰</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanız iade edilmiştir.</p>
<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin:0 0 16px 0;text-align:center;">
    <span style="font-size:13px;color:#15803d;">İade Tutarı</span><br>
    <span style="font-size:24px;font-weight:700;color:#059669;">{{tutar}} TL</span>
</div>',

            'invoice_cancelled' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Fatura İptal Edildi ❌</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanız iptal edilmiştir.</p>
<p style="margin:0;color:#64748b;font-size:13px;">Herhangi bir sorunuz varsa destek ekibimizle iletişime geçebilirsiniz.</p>',

            'invoice_reminder' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Ödeme Hatırlatması ⏰</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanızın son ödeme tarihi yaklaşmaktadır.</p>
<div style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:20px;margin:0 0 24px 0;">
    <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:8px 0;color:#92400e;font-size:13px;">Tutar</td><td style="padding:8px 0;text-align:right;font-weight:700;color:#b45309;font-size:16px;">{{tutar}} TL</td></tr>
        <tr><td style="padding:8px 0;border-top:1px solid #fde68a;color:#92400e;font-size:13px;">Son Ödeme</td><td style="padding:8px 0;border-top:1px solid #fde68a;text-align:right;font-weight:600;color:#dc2626;">{{son_odeme_tarihi}}</td></tr>
    </table>
</div>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/invoices" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#f59e0b;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Ödeme Yap →</a>
</div>',

            'invoice_overdue' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#dc2626;">Ödeme Gecikti! ⚠️</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanızın son ödeme tarihi geçmiştir. Hizmetinizin kesintiye uğramaması için lütfen en kısa sürede ödeme yapınız.</p>
<div style="background-color:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 20px;margin:0 0 24px 0;text-align:center;">
    <span style="font-size:13px;color:#991b1b;">Gecikmiş Tutar</span><br>
    <span style="font-size:24px;font-weight:700;color:#dc2626;">{{tutar}} TL</span>
</div>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/invoices" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#dc2626;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Hemen Öde →</a>
</div>',

            'invoice_paid' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#059669;">Ödeme Onaylandı ✅</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanızın ödemesi başarıyla alınmıştır.</p>
<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin:0 0 16px 0;text-align:center;">
    <span style="font-size:13px;color:#15803d;">Ödenen Tutar</span><br>
    <span style="font-size:24px;font-weight:700;color:#059669;">{{tutar}} TL</span>
</div>
<p style="margin:0;color:#64748b;font-size:13px;text-align:center;">Teşekkür ederiz. 🙏</p>',

            'invoice_formalized' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Resmi Faturanız Hazır 📄</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı resmi faturanız hazırlanmıştır. Faturanızı panelden görüntüleyip indirebilirsiniz.</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/invoices" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Faturayı İndir →</a>
</div>',

            'invoice_pending_approval' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Ödeme Onayı Bekliyor ⏳</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanız için yaptığınız ödeme incelenmektedir. En kısa sürede onaylanacaktır.</p>
<div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin:0 0 16px 0;text-align:center;">
    <span style="font-size:13px;color:#64748b;">Tutar</span><br>
    <span style="font-size:20px;font-weight:700;color:#1e293b;">{{tutar}} TL</span>
</div>',

            'invoice_auto_payment_failed' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#dc2626;">Otomatik Ödeme Başarısız ⚠️</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{fatura_no}}</strong> numaralı faturanızın otomatik ödemesi gerçekleştirilemedi. Hizmetinizin kesintiye uğramaması için lütfen manuel ödeme yapınız.</p>
<div style="background-color:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 20px;margin:0 0 24px 0;text-align:center;">
    <span style="font-size:13px;color:#991b1b;">Tutar</span><br>
    <span style="font-size:24px;font-weight:700;color:#dc2626;">{{tutar}} TL</span>
</div>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/invoices" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#dc2626;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Manuel Ödeme Yap →</a>
</div>',

            'subscription_payment_failed' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#dc2626;">Abonelik Ödemesi Başarısız ⚠️</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;">Abonelik ödemeniz gerçekleştirilemedi. Hizmetinizin kesintiye uğramaması için lütfen ödeme bilgilerinizi güncelleyiniz.</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Panele Git →</a>
</div>',

            'order_received' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Siparişiniz Alındı 🛒</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;"><strong>#{{siparis_no}}</strong> numaralı siparişiniz başarıyla alınmıştır.</p>
<div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin:0 0 24px 0;">
    <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:8px 0;color:#64748b;font-size:13px;">Ürün</td><td style="padding:8px 0;text-align:right;font-weight:600;color:#1e293b;">{{urun_adi}}</td></tr>
        <tr><td style="padding:8px 0;border-top:1px solid #e2e8f0;color:#64748b;font-size:13px;">Sipariş No</td><td style="padding:8px 0;border-top:1px solid #e2e8f0;text-align:right;font-weight:600;color:#1e293b;">#{{siparis_no}}</td></tr>
    </table>
</div>
<p style="margin:0;color:#64748b;font-size:13px;text-align:center;">Siparişiniz en kısa sürede işleme alınacaktır.</p>',

            'order_confirmed' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#059669;">Sipariş Onaylandı ✅</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{siparis_no}}</strong> numaralı siparişiniz onaylanmış ve aktif edilmiştir.</p>
<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin:0 0 24px 0;text-align:center;">
    <span style="font-size:14px;font-weight:600;color:#15803d;">{{urun_adi}}</span>
</div>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/orders" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#059669;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Siparişi Görüntüle →</a>
</div>',

            'order_suspended' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#f59e0b;">Sipariş Askıya Alındı ⏸️</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{siparis_no}}</strong> numaralı siparişiniz askıya alınmıştır. Detaylı bilgi için destek ekibimizle iletişime geçebilirsiniz.</p>',

            'order_activated' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#059669;">Sipariş Aktif Edildi ✅</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 20px 0;color:#475569;"><strong>#{{siparis_no}}</strong> numaralı siparişiniz tekrar aktif edilmiştir. Hizmetiniz kullanıma hazırdır.</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{site_url}}/portal/orders" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#059669;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Siparişi Görüntüle →</a>
</div>',

            'order_cancelled' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#dc2626;">Sipariş İptal Edildi ❌</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>#{{siparis_no}}</strong> numaralı siparişiniz iptal edilmiştir.</p>
<p style="margin:0;color:#64748b;font-size:13px;">Herhangi bir sorunuz varsa destek ekibimizle iletişime geçebilirsiniz.</p>',

            'order_renewed' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#059669;">Hizmet Süreniz Uzatıldı 🔄</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 16px 0;color:#475569;"><strong>{{urun_adi}}</strong> hizmetinizin süresi başarıyla uzatılmıştır.</p>
<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;margin:0 0 16px 0;text-align:center;">
    <span style="font-size:13px;color:#15803d;">Yeni Bitiş Tarihi</span><br>
    <span style="font-size:20px;font-weight:700;color:#059669;">{{yeni_bitis_tarihi}}</span>
</div>
<p style="margin:0;color:#64748b;font-size:13px;text-align:center;">Hizmetiniz kesintisiz devam etmektedir. 🙏</p>',

            'support_auto_resolved' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Destek Talebi Çözümlendi ✅</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;"><strong>#{{talep_no}}</strong> numaralı destek talebiniz otomatik olarak çözümlenmiştir.</p>
<p style="margin:0 0 16px 0;color:#64748b;font-size:13px;">Konu: {{konu}}</p>
<p style="margin:0;color:#64748b;font-size:13px;">Eğer sorununuz devam ediyorsa yeni bir destek talebi oluşturabilirsiniz.</p>',

            'support_replied' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#3b82f6;">Yanıt Geldi 💬</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;"><strong>#{{talep_no}}</strong> numaralı destek talebinize yanıt gelmiştir.</p>
<p style="margin:0 0 24px 0;color:#64748b;font-size:13px;">Konu: {{konu}}</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{talep_url}}" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Yanıtı Görüntüle →</a>
</div>',

            'admin_notification' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Yeni Bildirim 🔔</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;">Yönetici tarafından yeni bir bildirim oluşturulmuştur:</p>
<div style="background-color:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin:12px 0 16px 0;">
    <p style="margin:0 0 8px 0;font-weight:600;color:#1e40af;">{{bildirim_baslik}}</p>
    <p style="margin:0;color:#1e40af;font-size:14px;">{{bildirim_icerik}}</p>
</div>',

            'support_created' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#1e293b;">Destek Talebiniz Oluşturuldu 🎫</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;"><strong>#{{talep_no}}</strong> numaralı destek talebiniz başarıyla oluşturulmuştur.</p>
<p style="margin:0 0 20px 0;color:#64748b;font-size:13px;">Konu: {{konu}}</p>
<p style="margin:0 0 24px 0;color:#475569;">Ekibimiz en kısa sürede talebinizi inceleyecektir.</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{talep_url}}" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Talebi Görüntüle →</a>
</div>',

            'support_in_progress' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#f59e0b;">İşleme Alındı ⏳</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;"><strong>#{{talep_no}}</strong> numaralı destek talebiniz ekibimiz tarafından işleme alınmıştır.</p>
<p style="margin:0 0 16px 0;color:#64748b;font-size:13px;">Konu: {{konu}}</p>
<p style="margin:0;color:#475569;">En kısa sürede yanıtlanacaktır.</p>',

            'support_resolved' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#059669;">Destek Talebi Çözümlendi ✅</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;"><strong>#{{talep_no}}</strong> numaralı destek talebiniz çözümlenmiştir.</p>
<p style="margin:0 0 20px 0;color:#64748b;font-size:13px;">Konu: {{konu}}</p>
<p style="margin:0 0 24px 0;color:#475569;">Eğer sorununuz devam ediyorsa talebi yeniden açabilirsiniz.</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{talep_url}}" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#059669;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Talebi Görüntüle →</a>
</div>',

            'support_replied_email_piping' => '<h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#3b82f6;">Yanıt Geldi 💬</h2>
<p style="margin:0 0 12px 0;color:#475569;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p>
<p style="margin:0 0 8px 0;color:#475569;"><strong>#{{talep_no}}</strong> numaralı destek talebinize e-posta üzerinden yanıt gelmiştir.</p>
<p style="margin:0 0 24px 0;color:#64748b;font-size:13px;">Konu: {{konu}}</p>
<div style="text-align:center;margin:0 0 16px 0;">
    <a href="{{talep_url}}" target="_blank" style="display:inline-block;padding:12px 32px;background-color:#3b82f6;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;">Yanıtı Görüntüle →</a>
</div>',
        ];
    }
}
