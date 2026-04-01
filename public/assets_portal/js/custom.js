const priceFormat = wNumb({
    thousand: '.',
    decimals: 2,
    mark: ','
})

Inputmask({
    mask: '9999',
    placeholder: '____',
}).mask('.yearMask');

Inputmask({
    mask: '99/99/9999',
    placeholder: '__/__/____',
}).mask('.dateMask');

function propSubmitButton(btn, status = 1) {
    var $btn = $(btn);

    if (status == 1) {
        $btn.attr("data-kt-indicator", "on");
        $btn.attr("data-original-text", $btn.html());
        $btn.prop("disabled", true);
        $btn.html('Lütfen bekleyin...');
    } else {
        $btn.removeAttr("data-kt-indicator");
        $btn.prop("disabled", false);
        $btn.html($btn.attr('data-original-text'));
    }
}

function resetForm(form) {
    form.find("input:not([name='_token']):not([type='checkbox'])").val("");
    form.find("select").val("");
    form.find("textarea").val("");
    form[0].reset();
    form.find("select").trigger("change");
}

const drawFormElement = (element) => {
    switch (element.type) {
        case "radio":
            let options = "";
            $.map(element.options, function (item) {
                options += '<div class="form-check form-check-custom form-check-solid mb-2">\n' +
                    '                        <!--begin::Input-->\n' +
                    '                        <input class="form-check-input me-3" name="' + element.name + '" type="radio" value="' + item.value + '" id="np_' + item.value + '_option">\n' +
                    '                        <!--end::Input-->\n' +
                    '\n' +
                    '                        <!--begin::Label-->\n' +
                    '                        <label class="form-check-label" for="np_' + item.value + '_option">\n' +
                    '                            <div class="fw-bold text-gray-800">' + item.label + ' - ₺' + item.price +'</div>\n' +
                    '                        </label>\n' +
                    '                        <!--end::Label-->\n' +
                    '                    </div>';
            });
            return options;
        default:
            return "";
    }
}

function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}
$(document).ready(function () {
    let notificationsArea = $("#header-notifications-area"),
        listArea = notificationsArea.find(".list"),
        templateArea = notificationsArea.find("[data-notification-element='item-template']"),
        unreadCountArea = notificationsArea.find(".total"),
        emptyArea = notificationsArea.find("[data-notification-element='empty-template']");
    const getNotificationsList = () => {
        $.ajax({
            type: "GET",
            url: notificationsArea.attr("data-url"),
            dataType: "json",
            data: {
                _token: notificationsArea.attr("data-token")
            },
            beforeSend: function () {
                listArea.html("")
            },
            complete: function (data, status) {
                res = data.responseJSON;
                if (res && res.success === true) {
                    if (res.unreadCount > 0) {
                        notificationsArea.find(".icon").addClass("text-danger")
                        notificationsArea.find(".icon").find(".path2, .path4, .path5").addClass("animation-blink")
                    } else {
                        notificationsArea.find(".icon").removeClass("text-danger")
                        notificationsArea.find(".icon").find(".path2, .path4, .path5").removeClass("animation-blink")
                    }

                    unreadCountArea.text(res.unreadCount)
                    if (res.data.length > 0) {
                        res.data.map((item) => {
                            templateArea.find(".notification-item").attr("data-id", item.id)
                            if (item.read_at) {
                                templateArea.find(".notification-item").removeClass("bg-gray-200");
                                templateArea.find(".notification-item").addClass("opacity-50");
                            } else {
                                templateArea.find(".notification-item").removeClass("opacity-50");
                                templateArea.find(".notification-item").addClass("bg-gray-100");
                            }

                            templateArea.find(".title").html(item.message)
                            templateArea.find(".timeAgo").html(item.time_ago)
                            listArea.append(templateArea.html())
                        })
                    } else {
                        listArea.append(emptyArea.html())
                    }
                } else {
                    console.log("Bildirimleri çekereken bir sorun oluştu.");
                }
            }
        })
    }
    getNotificationsList();
    setInterval(() => {
        getNotificationsList();
    }, 20000)
})

$(document).ready(function (){
    $(".dateInput").flatpickr({
        dateFormat: defaultDateFormat(),
        enableTime: false,
        locale: {
            weekdays: {
                longhand: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
                shorthand: ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt']
            },
            months: {
                longhand: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                shorthand: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara']
            },
            today: 'BugÃ¼n',
            clear: 'Temizle',
            firstDayOfWeek: 1,
            time_24hr: true
        },
        time_24hr: true
    });

    $(".priceInput").on("blur", function () {
        if($(this).val() && (/\d/.test($(this).val()))){
            $(this).val(priceFormat.to(priceFormat.from($(this).val())))
        }else{
            $(this).val("")
        }
    })
})
