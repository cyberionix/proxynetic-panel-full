<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            [
                'name' => 'Hoş Geldiniz - Tüm Müşteriler',
                'channel' => 'both',
                'target_type' => 'all',
                'target_filters' => [],
                'sms_content' => 'Merhaba {{ad}}, Proxynetic ailesine hoş geldiniz! Sizlere özel fırsatlarımızı kaçırmayın. {{site_url}}',
                'mail_subject' => 'Proxynetic\'e Hoş Geldiniz, {{ad}}!',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#2563eb,#7c3aed);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;font-size:28px;">Proxynetic\'e Hoş Geldiniz!</h1><p style="color:#dbeafe;margin:10px 0 0;font-size:16px;">Türkiye\'nin en güvenilir proxy hizmeti</p></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Proxynetic ailesine katıldığınız için teşekkür ederiz. Mobil proxy, datacenter proxy ve daha birçok hizmetimizle dijital dünyada güvenle gezinin.</p><div style="text-align:center;margin:25px 0;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px;">Hizmetleri Keşfet</a></div><p style="color:#9ca3af;font-size:12px;text-align:center;">Bu e-posta {{site_adi}} tarafından gönderilmiştir.</p></div></div>',
            ],
            [
                'name' => 'Bahar Kampanyası - %30 İndirim',
                'channel' => 'both',
                'target_type' => 'all',
                'target_filters' => [],
                'sms_content' => '{{ad}}, bahar kampanyamız başladı! Tüm proxy hizmetlerinde %30 indirim. Kod: BAHAR30. {{site_url}}',
                'mail_subject' => 'Bahar Kampanyası: Tüm Hizmetlerde %30 İndirim!',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#059669,#10b981);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;font-size:32px;">🌸 Bahar Kampanyası</h1><p style="color:#d1fae5;font-size:20px;margin:10px 0 0;">Tüm hizmetlerde <strong>%30 İndirim!</strong></p></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Baharla birlikte indirimler de geldi! Tüm proxy hizmetlerimizde geçerli %30 indirim fırsatını kaçırmayın.</p><div style="background:#f0fdf4;border:2px dashed #10b981;padding:20px;border-radius:8px;text-align:center;margin:20px 0;"><p style="color:#059669;font-weight:bold;font-size:14px;margin:0;">İndirim Kodu</p><p style="color:#059669;font-weight:bold;font-size:32px;margin:5px 0;letter-spacing:4px;">BAHAR30</p><p style="color:#6b7280;font-size:12px;margin:0;">30 Nisan\'a kadar geçerlidir</p></div><div style="text-align:center;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#059669;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Hemen Alışveriş Yap</a></div></div></div>',
            ],
            [
                'name' => 'Hizmeti Bitenlere Özel Teklif',
                'channel' => 'both',
                'target_type' => 'no_service',
                'target_filters' => [],
                'sms_content' => '{{ad}}, sizi özledik! Proxy hizmetiniz sona ermiş. Yeniden başlamak için %20 indirimli fiyatlarımızdan yararlanın. {{site_url}}',
                'mail_subject' => '{{ad}}, Sizi Özledik! Özel Teklif İçeride',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#dc2626,#f59e0b);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;font-size:28px;">Sizi Özledik! 💔</h1><p style="color:#fef3c7;font-size:16px;margin:10px 0 0;">Size özel bir teklifimiz var</p></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Proxy hizmetinizin sona erdiğini fark ettik. Sizi tekrar aramızda görmek isteriz! Size özel <strong>%20 indirimli</strong> fiyatlarımızdan yararlanarak hizmetinizi yeniden başlatabilirsiniz.</p><div style="text-align:center;margin:25px 0;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#dc2626;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Tekrar Başla</a></div></div></div>',
            ],
            [
                'name' => 'Aktif Müşterilere Teşekkür',
                'channel' => 'mail',
                'target_type' => 'active_orders',
                'target_filters' => [],
                'sms_content' => '',
                'mail_subject' => 'Teşekkürler {{ad}}! Sizin İçin Buradayız',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#7c3aed,#a855f7);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;">Teşekkür Ederiz! 🎉</h1></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Proxynetic\'i tercih ettiğiniz için teşekkür ederiz. Aktif hizmetiniz sorunsuz devam ediyor. Herhangi bir sorunuz olursa 7/24 destek ekibimiz yanınızda.</p><p style="color:#6b7280;">Hizmet kalitemizi artırmak için görüşleriniz bizim için çok değerli!</p><div style="text-align:center;margin:20px 0;"><a href="{{site_url}}" style="display:inline-block;padding:12px 28px;background:#7c3aed;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Panele Git</a></div></div></div>',
            ],
            [
                'name' => 'Yeni Ürün Tanıtımı - Rotating Proxy',
                'channel' => 'both',
                'target_type' => 'all',
                'target_filters' => [],
                'sms_content' => '{{ad}}, yeni hizmetimiz "Rotating Proxy" ile IP adresinizi otomatik değiştirin! Detaylar: {{site_url}}',
                'mail_subject' => 'Yeni Hizmet: Rotating Proxy Artık Proxynetic\'te!',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#0891b2,#06b6d4);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;">🔄 Yeni Hizmet!</h1><p style="color:#cffafe;font-size:18px;margin:10px 0 0;">Rotating Proxy</p></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Yeni hizmetimiz <strong>Rotating Proxy</strong> ile IP adresiniz belirlediğiniz aralıklarla otomatik olarak değişir. Scraping, SEO analizi ve sosyal medya yönetimi için ideal!</p><ul style="color:#6b7280;"><li>Otomatik IP rotasyonu</li><li>Dünya genelinde lokasyonlar</li><li>Yüksek hız ve uptime</li><li>API desteği</li></ul><div style="text-align:center;margin:25px 0;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#0891b2;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Detayları İncele</a></div></div></div>',
            ],
            [
                'name' => 'Hafta Sonu Fırsatı - %15 İndirim',
                'channel' => 'sms',
                'target_type' => 'all',
                'target_filters' => [],
                'sms_content' => '{{ad}}, hafta sonu fırsatı! Tüm proxy paketlerinde %15 indirim. Sadece bu hafta sonu geçerli. {{site_url}}',
                'mail_subject' => '',
                'mail_content' => '',
            ],
            [
                'name' => 'Destek Memnuniyet Anketi',
                'channel' => 'mail',
                'target_type' => 'active_orders',
                'target_filters' => [],
                'sms_content' => '',
                'mail_subject' => '{{ad}}, Hizmetimizi Değerlendirin!',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#ea580c,#f97316);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;">📊 Görüşünüz Bizim İçin Değerli</h1></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Proxynetic hizmetlerimizden memnun musunuz? Deneyiminizi iyileştirmek için geri bildirimlerinize ihtiyacımız var.</p><p style="color:#6b7280;">Kısa anketimize katılarak bize yardımcı olabilirsiniz:</p><div style="text-align:center;margin:25px 0;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#ea580c;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Ankete Katıl</a></div><p style="color:#9ca3af;font-size:12px;text-align:center;">Anket yaklaşık 2 dakika sürmektedir.</p></div></div>',
            ],
            [
                'name' => 'Hizmeti Olmayanlara Ücretsiz Deneme',
                'channel' => 'both',
                'target_type' => 'no_service',
                'target_filters' => [],
                'sms_content' => '{{ad}}, size özel 24 saat ücretsiz proxy deneme hakkı! Hemen deneyin: {{site_url}}',
                'mail_subject' => '{{ad}}, 24 Saat Ücretsiz Proxy Deneyin!',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#4f46e5,#818cf8);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;">🎁 Ücretsiz Deneme Fırsatı!</h1><p style="color:#e0e7ff;font-size:18px;margin:10px 0 0;">24 Saat Proxy Hizmeti Hediye</p></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Proxynetic hizmetlerini denemek ister misiniz? Size özel <strong>24 saat ücretsiz</strong> proxy deneme hakkı tanıdık!</p><div style="background:#eef2ff;border:2px solid #818cf8;padding:20px;border-radius:8px;text-align:center;margin:20px 0;"><p style="color:#4f46e5;font-weight:bold;font-size:18px;margin:0;">24 Saat Ücretsiz</p><p style="color:#6b7280;font-size:13px;margin:5px 0 0;">Kredi kartı gerekmez</p></div><div style="text-align:center;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#4f46e5;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Ücretsiz Deneyin</a></div></div></div>',
            ],
            [
                'name' => 'Ramazan Bayramı Kutlaması',
                'channel' => 'both',
                'target_type' => 'all',
                'target_filters' => [],
                'sms_content' => '{{ad}}, Ramazan Bayramınız kutlu olsun! Bayram süresince tüm hizmetlerde %25 indirim. Kod: BAYRAM25. {{site_url}}',
                'mail_subject' => 'Ramazan Bayramınız Kutlu Olsun! 🌙',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><p style="color:#fbbf24;font-size:48px;margin:0;">🌙</p><h1 style="color:#fff;margin:10px 0 0;">Ramazan Bayramınız Kutlu Olsun!</h1></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Proxynetic ailesi olarak Ramazan Bayramınızı en içten dileklerimizle kutlarız. Bayram süresince tüm hizmetlerimizde geçerli <strong>%25 indirim</strong> fırsatını sunuyoruz.</p><div style="background:#eff6ff;border:2px dashed #2563eb;padding:20px;border-radius:8px;text-align:center;margin:20px 0;"><p style="color:#1e40af;font-weight:bold;font-size:14px;margin:0;">Bayram İndirim Kodu</p><p style="color:#1e40af;font-weight:bold;font-size:28px;margin:5px 0;letter-spacing:4px;">BAYRAM25</p></div><div style="text-align:center;"><a href="{{site_url}}" style="display:inline-block;padding:14px 32px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Fırsattan Yararlan</a></div></div></div>',
            ],
            [
                'name' => 'Sistem Bakım Bildirimi',
                'channel' => 'both',
                'target_type' => 'active_orders',
                'target_filters' => [],
                'sms_content' => '{{ad}}, planlı bakım bildirimi: 15 Nisan 03:00-05:00 arası kısa süreli kesinti olabilir. Anlayışınız için teşekkürler.',
                'mail_subject' => 'Planlı Bakım Bildirimi - 15 Nisan',
                'mail_content' => '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;"><div style="background:linear-gradient(135deg,#374151,#6b7280);padding:40px 30px;border-radius:12px 12px 0 0;text-align:center;"><h1 style="color:#fff;margin:0;">🔧 Planlı Bakım Bildirimi</h1></div><div style="background:#fff;padding:30px;border:1px solid #e5e7eb;"><p style="font-size:16px;color:#374151;">Sayın <strong>{{ad}} {{soyad}}</strong>,</p><p style="color:#6b7280;">Hizmet kalitemizi artırmak amacıyla planlı bir bakım çalışması gerçekleştireceğiz.</p><table style="width:100%;border-collapse:collapse;margin:15px 0;"><tr><td style="padding:10px;border:1px solid #e5e7eb;font-weight:bold;background:#f9fafb;">Tarih</td><td style="padding:10px;border:1px solid #e5e7eb;">15 Nisan 2026</td></tr><tr><td style="padding:10px;border:1px solid #e5e7eb;font-weight:bold;background:#f9fafb;">Saat</td><td style="padding:10px;border:1px solid #e5e7eb;">03:00 - 05:00 (TSİ)</td></tr><tr><td style="padding:10px;border:1px solid #e5e7eb;font-weight:bold;background:#f9fafb;">Etki</td><td style="padding:10px;border:1px solid #e5e7eb;">Kısa süreli bağlantı kesintisi</td></tr></table><p style="color:#6b7280;">Bakım süresince hizmetlerde kısa süreli kesintiler yaşanabilir. Anlayışınız için teşekkür ederiz.</p></div></div>',
            ],
        ];

        foreach ($campaigns as $data) {
            Campaign::create(array_merge($data, [
                'status' => 'draft',
                'created_by' => 1,
            ]));
        }
    }
}
