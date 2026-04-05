<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? '' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f0f2f5;font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f2f5;">
    <tr>
        <td align="center" style="padding:32px 16px;">

            <!-- Main Card -->
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                <!-- Header -->
                <tr>
                    <td style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);padding:28px 32px;text-align:center;">
                        <a href="{{ url('/') }}" target="_blank" style="text-decoration:none;">
                            <img src="{{ url(brand('logo') ?: brand('logo_dark')) }}" alt="{{ brand('name') }}" style="height:44px;max-width:200px;" />
                        </a>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:36px 32px 28px 32px;">
                        <div style="font-size:15px;line-height:1.7;color:#334155;">
                            {!! $content !!}
                        </div>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:0 32px;">
                        <div style="height:1px;background-color:#e2e8f0;"></div>
                    </td>
                </tr>

                <!-- Help Section -->
                <tr>
                    <td style="padding:20px 32px;text-align:center;">
                        <p style="margin:0 0 4px 0;font-size:13px;color:#64748b;">
                            Yardıma mı ihtiyacınız var?
                        </p>
                        <a href="{{ url('/portal/supports') }}" target="_blank" style="display:inline-block;font-size:13px;color:#3b82f6;text-decoration:none;font-weight:600;">
                            Destek Talebi Oluşturun
                        </a>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color:#f8fafc;padding:24px 32px;border-top:1px solid #e2e8f0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <p style="margin:0 0 8px 0;font-size:13px;font-weight:600;color:#475569;">
                                        {{ brand('name') }}
                                    </p>
                                    @if(brand('contact_info.phone_number') || brand('contact_info.email'))
                                    <p style="margin:0 0 6px 0;font-size:12px;color:#94a3b8;">
                                        @if(brand('contact_info.phone_number'))
                                            📞 {{ brand('contact_info.phone_number') }}
                                        @endif
                                        @if(brand('contact_info.phone_number') && brand('contact_info.email'))
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                        @endif
                                        @if(brand('contact_info.email'))
                                            ✉️ {{ brand('contact_info.email') }}
                                        @endif
                                    </p>
                                    @endif
                                    @if(brand('contact_info.address_line_1'))
                                    <p style="margin:0 0 6px 0;font-size:11px;color:#94a3b8;">
                                        {{ brand('contact_info.address_line_1') }}
                                        @if(brand('contact_info.address_line_2'))
                                            , {{ brand('contact_info.address_line_2') }}
                                        @endif
                                    </p>
                                    @endif
                                    <p style="margin:0 0 10px 0;font-size:12px;color:#94a3b8;">
                                        <a href="{{ brand('contact_info.website') ?: brand('base_url') ?: url('/') }}" target="_blank" style="color:#3b82f6;text-decoration:none;">
                                            {{ brand('url') ?: brand('contact_info.website') ?: url('/') }}
                                        </a>
                                    </p>
                                    <p style="margin:0;font-size:11px;color:#cbd5e1;">
                                        &copy; {{ date('Y') }} {{ brand('name') }}. Tüm hakları saklıdır.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
            <!-- End Main Card -->

        </td>
    </tr>
</table>

</body>
</html>
