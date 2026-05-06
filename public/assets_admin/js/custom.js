const priceFormat = wNumb({
    thousand: '.',
    decimals: 2,
    mark: ','
})

Inputmask({
    mask: '99:99',
    placeholder: '__:__',
}).mask('.timeMask');

Inputmask({
    mask: '99/99/9999',
    placeholder: '__/__/____',
}).mask('.dateMask');

Inputmask({
    mask: '9999',
    placeholder: '____',
}).mask('.yearMask');

function propSubmitButton(btn,status = 1){
    if(status == 1){
        btn.attr("data-kt-indicator", "on");
        btn.prop("disabled", true);
    }else{
        btn.attr("data-kt-indicator", "off");
        btn.prop("disabled", false);
    }
}
function resetForm(form){
    form.find("input:not([name='_token']):not([type='checkbox'])").val("");
    form.find("select").val("");
    form.find("textarea").val("");
    form[0].reset();
    form.find("select").trigger("change");
}
function generateRandomPassword(length = 8) {
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
        password = '',
        hasLetter = false,
        hasDigit = false;

    for (var i = 0; i < length; i++) {
        var randomIndex = Math.floor(Math.random() * characters.length);
        var randomChar = characters.charAt(randomIndex);

        if (/[A-Za-z]/.test(randomChar)) {
            hasLetter = true;
        }
        else if (/\d/.test(randomChar)) {
            hasDigit = true;
        }

        password += randomChar;
    }

    if (!hasLetter || !hasDigit) {
        return generateRandomPassword(length);
    }

    return password;
}

$(".priceInput").on("blur", function () {
    if($(this).val() && (/\d/.test($(this).val()))){
        $(this).val(priceFormat.to(priceFormat.from($(this).val())))
    }else{
        $(this).val("")
    }
})
$(".dateInput").flatpickr({
    dateFormat: 'd/m/Y',
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
        today: 'Bugün',
        clear: 'Temizle',
        firstDayOfWeek: 1,
        time_24hr: true
    },
});

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
