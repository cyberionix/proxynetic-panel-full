<!--begin::Email template-->
<style>html,body { padding:0; margin:0; font-family: Inter, Helvetica, "sans-serif"; } a:hover { color: #009ef7; }</style>
<div id="#kt_app_body_content" style="background-color:#D5D9E2; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:0; width:100%;">
    <div style="background-color:#ffffff; padding: 45px 0 34px 0; border-radius: 24px; margin:40px auto; max-width: 600px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
            <tbody>
            <tr>
                <td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
                    <!--begin:Email content-->
                    <div style="text-align:center; margin:0 15px 34px 15px">
                        <!--begin:Logo-->
                        <div style="margin-bottom: 10px">
                            <a href="{{url('')}}" rel="noopener" target="_blank">
                                <img alt="Logo" src="{{url(brand('logo'))}}" style="height: 100px" />
                            </a>
                        </div>
                        <!--end:Logo-->
                        <!--begin:Text-->
                        <div style="font-size: 14px; font-weight: 500; margin-bottom: 27px; font-family:Arial,Helvetica,sans-serif;">
                            <p style="margin-bottom:9px; color:#181C32; font-size: 20px; font-weight:700">Merhaba {{$user->full_name}},
                                <br>
                                {{brand('name')}} hesabınız başarıyla oluşturuldu.</p><br>
                            <p style="margin-bottom:2px; color:#7E8299">E-posta adresinizi doğrulamanıza sadece bir adım kaldı.</p>
                            <p style="margin-bottom:2px; color:#7E8299">Hesabınızı doğrulamak için aşağıdaki butona tıklayabilirsiniz.</p>
                            <br>
                            <div style="padding-top: 8px; padding-bottom: 8px; text-align:center;">
                                <a href="{{route("auth.verify_email_otp", ["email" => $user->email, "code" => $code])}}" rel="noopener" style="text-decoration:none;display:inline-block;text-align:center;padding:0.75575rem 1.3rem;font-size:0.925rem;line-height:1.5;border-radius:0.35rem;color:#ffffff;background-color:#006bf7;border:0px;margin-right:0.75rem!important;font-weight:600!important;outline:none!important;vertical-align:middle" target="_blank">Hesabını Doğrula</a>
                            </div>
                            <br>
                            <p>Buton bağlantısı çalışmıyor ise aşağıdaki linki tarayıcı adresine kopyalayarak işlem yapabilirsiniz.</p>
                                <br>
                                {{route("auth.verify_email_otp", ["email" => $user->email, "code" => $code])}}
                        </div>
                        <!--end:Text-->
                        <!--begin:Action-->

                        <!--begin:Action-->
                    </div>
                    <!--end:Email content-->
                </td>
            </tr>

            <tr>
                <td align="center" valign="center" style="font-size: 13px; text-align:center; padding: 0 10px 10px 10px; font-weight: 500; color: #A1A5B7; font-family:Arial,Helvetica,sans-serif">
                    <p style="margin-bottom:2px">Sorularınız için iletişime geçmekten lütfen çekinmeyin.</p>
                    <p style="margin-bottom:4px"><a href="{{brand('base_url')}}" rel="noopener" target="_blank" style="font-weight: 600">{{brand('url')}}</a>.</p>

                </td>
            </tr>

            <tr>
                <td align="center" valign="center" style="font-size: 13px; padding:0 15px; text-align:center; font-weight: 500; color: #A1A5B7;font-family:Arial,Helvetica,sans-serif">
                    <p>&copy; Copyright {{now()->year}} {{brand('name')}}.</p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
