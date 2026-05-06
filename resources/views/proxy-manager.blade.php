<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Proxy Manager</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
            crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>

    <style>
        .mpy-container {
            font-family: 'Segoe UI', sans-serif;
            max-width: 720px;
            margin: 40px auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
        }

        .mpy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .mpy-header h2 {
            font-size: 20px;
            color: #333;
            margin: 0;
        }

        .mpy-power-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e74c3c;
            position: relative;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .mpy-power-toggle.mpy-on {
            background: #2ecc71;
        }

        .mpy-led {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
        }

        .mpy-tabs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            border-bottom: 1px solid #e0e0e0;
        }

        .mpy-tab {
            flex: 1;
            background: none;
            border: none;
            padding: 12px 0;
            text-align: center;
            cursor: pointer;
            font-size: 14px;
            color: #555;
            transition: all 0.2s ease;
            position: relative;
        }

        .mpy-tab:hover,
        .mpy-tab.active {
            color: #2ecc71;
            font-weight: bold;
        }

        .mpy-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 2px;
            background: #2ecc71;
            border-radius: 10px;
        }

        .mpy-tab-content {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 12px;
        }

        .mpy-panel {
            display: none;
            font-size: 14px;
            color: #333;
        }

        .mpy-panel.active {
            display: block;
        }

        /* Animation + Utility */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .mpy-panel.active {
            animation: fadeIn 0.3s ease;
        }

    </style>
    <style>
        .mpy-auth-box {
            background: #fdfdfd;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .mpy-auth-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-weight: 500;
            color: #333;
        }

        .mpy-switch {
            position: relative;
            width: 50px;
            height: 28px;
        }

        .mpy-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .mpy-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 34px;
            transition: .3s;
        }

        .mpy-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            border-radius: 50%;
            transition: .3s;
        }

        .mpy-switch input:checked + .mpy-slider {
            background-color: #2ecc71;
        }

        .mpy-switch input:checked + .mpy-slider:before {
            transform: translateX(22px);
        }

        .mpy-auth-section {
            margin-top: 10px;
        }

        .mpy-auth-section.hidden {
            display: none;
        }

        .mpy-input,
        .mpy-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            display: block;
            box-sizing: border-box; /* Bu garanti çözümdür */
        }

        .mpy-input:focus, .mpy-textarea:focus {
            border-color: #2ecc71;
        }

        .mpy-textarea {
            min-height: 100px;
            resize: vertical;
        }

    </style>
</head>
<body>
<div>
    <div style="width:100%;display: flex;justify-content: center;align-items: center;">


        <div class="mpy-container" style="width:100%">
            <div class="mpy-header">
                <h2>Mobil Proxy Yönetimi</h2>
                <div class="mpy-power-toggle mpy-off" id="powerElement" onclick="changeStatus()">
                    <div class="mpy-led"></div>
                </div>
            </div>

            <div class="mpy-tabs">
                <button class="mpy-tab active" onclick="switchTab(event, 'info')">Proxy Bilgileri</button>
                <button class="mpy-tab" onclick="switchTab(event, 'auth')">Kimlik Doğrulama</button>
                <button class="mpy-tab" onclick="switchTab(event, 'ip')">IP Geçmişi</button>
                <button class="mpy-tab d-none" onclick="switchTab(event, 'reset')">IP Reset</button>
            </div>

            <div class="mpy-tab-content">
                <div id="info" class="mpy-panel active">
                    <div class="proxy-box">
                        <div class="proxy-title">Proxy Bilgileri</div>
                        <div class="proxy-title"><span id="server-info" style="cursor: pointer;"
                                                       data-bs-toggle="tooltip" title="Tıklayarak kopyala"></span></div>
                        <div class="proxy-info"><span>Kullanıcı Adı:</span><span id="username"></span></div>
                        <div class="proxy-info"><span>Şifre:</span><span id="password"></span></div>
                        <div class="proxy-info"><span>IP:</span><span id="serverIp"></span></div>
                        <div class="proxy-info"><span>Port:</span><span id="port"></span></div>
                        <div class="proxy-info"><span>Protokol:</span><span id="protocol"></span></div>
                        <div class="proxy-info"><span>Kota Durumu:</span><span id="bandwidth"></span></div>
                    </div>
                </div>
                <div id="ip" class="mpy-panel">

                    <form id="ipSettingsForm" action="">
                        <input type="hidden" name="id" value="{{$pp_id}}">
                        <input type="hidden" name="token" value="{{$pp_token}}">
                        <input type="hidden" name="action" value="save_ip_settings">
                        <div class="mb-3">
                            <div class="">
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" name="is_active" type="checkbox" role="switch"
                                           id="airplaneActive">
                                    <label class="form-check-label" for="airplaneActive">Aktif Et</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Otomatik IP Yenileme Süresi (saniye)</label>
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <input type="number" min="30" id="airplanModeTime" max="10000"
                                                   name="seconds" class="mpy-input" placeholder="0">
                                        </div>
                                        <div class="col-lg-3">
                                            <button type="submit" class="w-100 btn btn-primary">Kaydet</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table" id="ipHistoryTable">
                            <thead>
                            <tr>
                                <th>IP</th>
                                <th>Tarih</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="reset" class="mpy-panel d-none">IP reset işlemleri</div>
                <div id="auth" class="mpy-panel">

                    <div class="mpy-auth-box">
                        <form action="" id="authenticationForm">
                            <input type="hidden" name="id" value="{{$pp_id}}">
                            <input type="hidden" name="token" value="{{$pp_token}}">
                            <input type="hidden" name="action" value="save_auth_settings">
                            <div class="mpy-auth-header">
                                <label>Kullanıcı Adı & Parola</label>
                                <div class="mpy-switch" onclick="toggleAuth()">
                                    <input name="is_active" type="checkbox" id="authToggle"/>
                                    <span class="mpy-slider"></span>
                                </div>
                            </div>

                            <div id="auth-userpass" class="mpy-auth-section">
                                <label>Kullanıcı Adı</label>
                                <input name="username" type="text" class="mpy-input" id="authUsername"
                                       placeholder="username">

                                <label class="mt-3">Parola</label>
                                <input name="password" type="text" class="mpy-input" id="authPass"
                                       placeholder="••••••••">

                                <button type="button" id="generateBtn" class="btn btn-primary mt-3">Rastgele Üret
                                </button> <!-- Buton ekledik -->
                            </div>

                            <div id="auth-whitelist" class="mpy-auth-section hidden">
                                <label>Whitelist IP'ler</label>
                                <textarea class="mpy-textarea" id="authWhitelist"
                                          placeholder="127.0.0.1&#10;192.168.1.1"></textarea>
                            </div>
                            <div class="w-100 mt-3 d-flex">
                                <button type="submit" class="btn btn-success ms-auto">Değişiklikleri Kaydet</button>
                            </div>
                        </form>

                    </div>


                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var p_status = 0;

    document.getElementById('generateBtn').addEventListener('click', function () {
        // Kullanıcı adı ve parola oluşturma
        const username = generateRandomString(6); // 6 karakterlik rastgele kullanıcı adı
        const password = generateRandomPassword(); // Parolayı oluştur

        // Kullanıcı adı ve parolayı form elemanlarına yazma
        document.getElementById('authUsername').value = username;
        document.getElementById('authPass').value = password;
    });

    // Kullanıcı adı için rastgele string oluşturma fonksiyonu
    function generateRandomString(length) {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    // Parola için rastgele oluşturma fonksiyonu
    function generateRandomPassword() {
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';

        let password = '';
        password += getRandomChar(lowercase, 3);  // Küçük harf
        password += getRandomChar(uppercase, 3);  // Büyük harf
        password += getRandomChar(numbers, 3);    // Sayılar

        return password;
    }

    // Belirli bir karakter setinden rastgele harf almak için yardımcı fonksiyon
    function getRandomChar(charSet, count) {
        let result = '';
        for (let i = 0; i < count; i++) {
            result += charSet.charAt(Math.floor(Math.random() * charSet.length));
        }
        return result;
    }

    function toggleAuth() {
        const checkbox = document.getElementById("authToggle");
        checkbox.checked = !checkbox.checked; // elle tetikle
        const userpass = document.getElementById("auth-userpass");
        const whitelist = document.getElementById("auth-whitelist");

        if (checkbox.checked) {
            userpass.classList.remove("hidden");
            whitelist.classList.add("hidden");
        } else {
            userpass.classList.add("hidden");
            // whitelist.classList.remove("hidden");
        }
    }


</script>

<script>

    function changeStatus() {

        let status = !p_status;

        fetch('{{route('api.proxy-manager-actions')}}',
            {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    action: 'change_status',
                    id: ppId,
                    token: ppToken,
                    status: status
                })
            })  // PHP'den JSON veri dönen endpoint
            .then(response => response.json())  // JSON olarak çözümle
            .then(res => {
                if (res.success !== true) {
                    console.log('erx: '+res.message);
                    return;
                }
                p_status = status;
                togglePower();
                Swal.fire({
                    title: "Başarılı",
                    text: response.message,
                    icon: "success"
                }).then(r => window.location.reload());


            })
            .catch(error => console.error('Veri alınırken hata oluştu:', error));


    }

    function togglePower() {
        let el = document.getElementById('powerElement');
        if (p_status == 0) {
            el.classList.remove('mpy-on');
            el.classList.add('mpy-off');
        } else {
            p_status = 1;
            el.classList.add('mpy-on');
            el.classList.remove('mpy-off');
        }


    }

    function switchTab(evt, id) {
        document.querySelectorAll('.mpy-tab').forEach(btn => btn.classList.remove('active'));
        evt.currentTarget.classList.add('active');

        document.querySelectorAll('.mpy-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(id).classList.add('active');
    }
</script>
<script>

    const id = '<?= $pp_id ?>';
    const token = '<?= $pp_token ?>';

    document.getElementById('server-info').addEventListener('click', function () {
        const text = this.textContent;
        navigator.clipboard.writeText(text).then(() => {
            this.title = "Kopyalandı!";
            setTimeout(() => this.title = "Tıklayarak kopyala", 1500);
        });
    });
    // PHP'den alınan veriyi almak için AJAX (fetch)
    fetch('{{route('api.proxy-manager-actions')}}',
        {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                action: 'general_information',
                id: ppId,
                token: ppToken
            })
        })  // PHP'den JSON veri dönen endpoint
        .then(response => response.json())  // JSON olarak çözümle
        .then(res => {
            if (res.success !== true) {
                console.log(res.message);
                return;
            }
            const data = res.data;
            // Veriyi DOM'a yazdır
            document.getElementById('server-info').textContent = `${data.serverIp}:${data.serverPort}:${data.authenticationUsername}:${data.authenticationPassword}`;
            document.getElementById('username').textContent = data.authenticationUsername;
            document.getElementById('password').textContent = data.authenticationPassword;
            document.getElementById('serverIp').textContent = data.serverIp;
            document.getElementById('port').textContent = data.serverPort;
            document.getElementById('protocol').textContent = data.protocolType === 'ProxySocks' ? 'SOCKS5' : 'HTTP';
            document.getElementById('bandwidth').textContent = `${(data.bandwidthUsage / 1024 / 1024).toFixed(2)} MB / ${(data.bandwidthLimit / 1024 / 1024 / 1024).toFixed(0)} GB`;
            document.getElementById('bandwidth').textContent = `${(data.bandwidthUsage / 1024 / 1024).toFixed(2)} MB / ${(data.bandwidthLimit / 1024 / 1024 / 1024).toFixed(0)} GB`;
            document.getElementById('airplaneActive').checked = data?.airplaneMode?.isAirPlaneModeOn === true;
            document.getElementById('airplanModeTime').value = data?.airplaneMode?.isAirPlaneModeOn === true ? data?.airplaneMode?.time : '';

            if (data.enableBasicAuthentication === true) {
                toggleAuth();
                document.getElementById("authUsername").value = data.authenticationUsername;
                document.getElementById("authPass").value = data.authenticationPassword;
            } else {
                toggleAuth();
                toggleAuth();
                document.getElementById("authWhitelist").value = data.authenticationPassword;
            }

            p_status = data.status;

            togglePower(data.status);


        })
        .catch(error => console.error('Veri alınırken hata oluştu:', error));


    fetch('{{route('api.proxy-manager-actions')}}',
        {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                action: 'get_ip_history',
                id: ppId,
                token: ppToken
            })
        })  // PHP'den JSON veri dönen endpoint
        .then(response => response.json())  // JSON olarak çözümle
        .then(res => {
            if (res.success !== true) {
                console.log(res.message);
                return;
            }
            const data = res.data;
            const ipHistoryTableBody = document.querySelector('#ipHistoryTable tbody');

            ipHistoryTableBody.innerHTML = '';


            if (data) {
                data.forEach(entry => {
                    const row = document.createElement('tr');

                    const ipCell = document.createElement('td');
                    ipCell.textContent = entry.ip;

                    const dateCell = document.createElement('td');
                    dateCell.textContent = entry.date;

                    row.appendChild(ipCell);
                    row.appendChild(dateCell);

                    ipHistoryTableBody.appendChild(row);
                });
            }


        })
        .catch(error => console.error('Veri alınırken hata oluştu (IPHistory):', error));


    /* IP Settings Form START*/
    document.getElementById('ipSettingsForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Sayfanın yeniden yüklenmesini engeller

        $('button').prop('disabled', true);
        const form = e.target;
        const formData = new FormData(form); // Tüm form verilerini alır

        // JSON formatına dönüştürmek istersen:
        const data = Object.fromEntries(formData.entries());

        fetch('{{route('api.proxy-manager-actions')}}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(response => {
                if (response.success === true) {
                    Swal.fire({
                        title: "Başarılı",
                        text: response.message,
                        icon: "success"
                    }).then(r => window.location.reload());
                } else {
                    $('button').prop('disabled', false);
                    Swal.fire({
                        title: "Hata",
                        text: response?.message ? response.message : 'Sunucu iletişim hatası. #388839',
                        icon: "error"
                    });
                }
            })
            .catch(error => {
                $('button').prop('disabled', false);
                console.error('Hata:', error);
            });
    });
    /* IP Settings Form END*/
    /* Authentication Form START*/
    document.getElementById('authenticationForm').addEventListener('submit', function (e) {
        $('button').prop('disabled', true);
        e.preventDefault(); // Sayfanın yeniden yüklenmesini engeller

        const form = e.target;
        const formData = new FormData(form); // Tüm form verilerini alır

        // JSON formatına dönüştürmek istersen:
        const data = Object.fromEntries(formData.entries());

        fetch('{{route('api.proxy-manager-actions')}}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(response => {
                if (response.success === true) {
                    Swal.fire({
                        title: "Başarılı",
                        text: response.message,
                        icon: "success"
                    }).then(r => window.location.reload());
                } else {
                    $('button').prop('disabled', false);
                    Swal.fire({
                        title: "Hata",
                        text: response?.message ? response.message : 'Sunucu iletişim hatası. #388840',
                        icon: "error"
                    });
                }
            })
            .catch(error => {
                $('button').prop('disabled', false);
                console.error('Hata:', error);
            });
    });
    /* Authentication Form END*/

</script>

</body>
</html>
