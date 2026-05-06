<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .proxy-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 16px;
            color: #333;
        }

        .proxy-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #444;
        }
        .proxy-info span {
            font-size: 17px;
        }
        .proxy-info span:first-child {
            color: #777;
        }
    </style>
</head>
<body>
<div id="managerArea"></div>
</body>
<script>


    // const endpointUrl = "https://my.proxynetic.com/api/get-tunnel-manager";
    const endpointUrl = "http://127.0.0.1:8000/api/get-tunnel-manager";
    const ppId = "<?=$ppId?>";
    const ppToken = "<?=$ppToken?>";

    fetch(endpointUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ id: ppId,token: ppToken })
    })
        .then(response => {
            if (!response.ok) throw new Error("Ağ hatası: " + response.status);
            return response.text(); // HTML dönecekse text, JSON dönecekse response.json()
        })
        .then(data => {
            const container = document.getElementById("managerArea");
            container.innerHTML = data;

            // Gelen içeriğin içindeki script'leri manuel çalıştır
            const scripts = container.querySelectorAll("script");
            scripts.forEach(oldScript => {
                const newScript = document.createElement("script");

                if (oldScript.src) {
                    // Harici script varsa onu da yükle
                    newScript.src = oldScript.src;
                    newScript.async = false; // sıralı yükleme istersen
                    document.head.appendChild(newScript);
                } else {
                    // Inline script
                    newScript.text = oldScript.textContent;
                    document.body.appendChild(newScript);
                }

                oldScript.remove(); // eski script'i sil (temizlik)
            });
        })

        .catch(error => {
            document.getElementById("managerArea").innerHTML = "<div class='error'>Bir hata oluştu: " + error.message + "</div>";
        });
</script>
</html>
