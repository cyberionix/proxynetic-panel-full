"use strict";

// Class definition
var KTCreateAccount = function () {
    // Elements

    var formData = {
        license_type_id: null,
        hosting_type: 'SELF_HOSTED',
        hosting_plan: null,
        domain_type: 'SELF',
        domain_address: null,
        sub_domain_address: null,
        _token: $('[name="_token"]').val()
    }


    var modal;
    var modalEl;

    var stepper;
    var form;
    var formSubmitButton;
    var formContinueButton;

    // Variables
    var stepperObj;
    var validations = [];

    var cartTotal;
    var payableAmount;

    const makeCartItem = (item) => {
        return '<tr class="fs-4"><td class="fw-bold py-0 w-75">' + item.name + '</td><td class="fw-bold py-0">' + item.price + '</td></tr>';
    }

    const calcCart = (step = null) => {
        formContinueButton.setAttribute('data-kt-indicator', 'on');
        formContinueButton.setAttribute('disabled', 'disabled');
        var formD = new FormData(document.getElementById('kt_create_account_form'));
        formD.append('_token', $('[name="_token"]').val());
        formD.append('license_type_id', $('[name="license_type_id"]').val());
        formD.append('step', step);
        $.ajax({
            type: 'POST',
            url: 'purchase/send',
            data: formD,
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            success: function (res) {
                formContinueButton.setAttribute('data-kt-indicator', 'off');
                formContinueButton.removeAttribute('disabled');
                if (res.success === true) {
                    if (step === 3) {
                        let cartItemsHTML = '';
                        console.log(res.data.cart.items);
                        $.each(res.data.cart.items, function (index, item) {
                            cartItemsHTML += makeCartItem(item);
                        })
                        cartTotal = res.data.cart.amount.grand_total;
                        payableAmount = res.data.cart.amount.grand_total;
                        $('.cart-summary-area').find('.cart-items-area').html(cartItemsHTML);
                        $('.cart-summary-area').find('.cart-total-area').html(res.data.cart.sub_total);
                        $('.cart-summary-area').find('.cart-vat-area').html(res.data.cart.vat);

                        $('.cart-summary-area').find('.cart-payable-area').html(res.data.cart.grand_total);
                    }
                    stepperObj.goNext(step);
                } else {
                    Swal.fire({
                        text: res.message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Kapat",
                        customClass: {
                            confirmButton: "btn btn-light"
                        }
                    })
                }


            },
            error: function () {
                formContinueButton.setAttribute('data-kt-indicator', 'off');
            }
        })
    }

    // Private Functions
    var initStepper = function () {
        // Initialize Stepper
        stepperObj = new KTStepper(stepper);

        if (stepNumb == 5) {
            formSubmitButton.style.display = 'none';
            $('[data-kt-stepper-action="previous"]').hide();
        }
        stepperObj.goTo(stepNumb);
        const step2Checker = () => {

        }

        function checkHostingType() {
            console.log($('[name="hosting_type"]').val());
            if ($('[name="hosting_type"]:checked').val() === 'SELF_HOSTED') {
                $('.select-hosting-disk-area').hide(500);
            } else {
                $('.select-hosting-disk-area').show(500);
            }
        }

        checkHostingType();
        $('[name="hosting_type"]').on('change', checkHostingType)

        stepperObj.on('kt.stepper.changed', function (stepper) {
            if (stepperObj.getCurrentStepIndex() > 1) {
                $('.cart-summary-element').removeClass('d-none');
            } else {
                $('.cart-summary-element').addClass('d-none');
            }
            if (stepperObj.getCurrentStepIndex() === 1) {

                $('.spinner-element').hide();
                $('.select-license-button').show();
                // $('.period-item').parent().show();

                formSubmitButton.classList.remove('d-inline-block');
                formSubmitButton.classList.add('d-none');
                formContinueButton.classList.add('d-none');


            } else if (stepperObj.getCurrentStepIndex() === 2) {
                formSubmitButton.classList.remove('d-inline-block');
                formSubmitButton.classList.add('d-none');
                formContinueButton.classList.remove('d-none');
                step2Checker();


            } else if (stepperObj.getCurrentStepIndex() === 3) {
                formSubmitButton.classList.remove('d-inline-block');
                formSubmitButton.classList.add('d-none');
                formContinueButton.classList.remove('d-none');


            } else if (stepperObj.getCurrentStepIndex() === 4) {

                formSubmitButton.classList.remove('d-none');
                formSubmitButton.classList.add('d-inline-block');
                formContinueButton.classList.add('d-none');
            } else if (stepperObj.getCurrentStepIndex() === 5) {
                formSubmitButton.classList.add('d-none');
                formContinueButton.classList.add('d-none');
            } else {
                formSubmitButton.classList.remove('d-inline-block');
                formSubmitButton.classList.remove('d-none');
                formContinueButton.classList.remove('d-none');
            }
        });

        // Validation before going to next page
        stepperObj.on('kt.stepper.next', function (stepper) {


            console.log('stepper.next', stepper.getCurrentStepIndex());

            // Validate form before change stepper step
            var validator = validations[stepper.getCurrentStepIndex() - 2]; // get validator for currnt step

            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        calcCart(stepper.getCurrentStepIndex());

                        KTUtil.scrollTop();
                    } else {
                        Swal.fire({
                            text: "Formda bazı bilgiler hatalı, lütfen kontrol ederek tekrar deneyin.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-light"
                            }
                        }).then(function () {
                            KTUtil.scrollTop();
                        });
                    }
                });
            } else {
                calcCart(stepper.getCurrentStepIndex());

                KTUtil.scrollTop();
            }
        });

        // Prev event
        stepperObj.on('kt.stepper.previous', function (stepper) {
            console.log('stepper.previous');

            stepper.goPrevious();
            KTUtil.scrollTop();
        });
    }

    function utf8_to_b64(str) {
        return window.btoa(unescape(encodeURIComponent(str)));
    }

    function b64_to_utf8(str) {
        return decodeURIComponent(escape(window.atob(str)));
    }

    var handleForm = function () {
        formSubmitButton.addEventListener('click', function (e) {
            // Validate form before change stepper step
            var validator = validations[2]; // get validator for last form

            validator.validate().then(function (status) {
                console.log('validated!');

                if (status == 'Valid') {
                    e.preventDefault();

                    formSubmitButton.disabled = true;

                    formSubmitButton.setAttribute('data-kt-indicator', 'on');

                    formContinueButton.setAttribute('data-kt-indicator', 'on');
                    formContinueButton.setAttribute('disabled', 'disabled');
                    var formD = new FormData(document.getElementById('kt_create_account_form'));
                    formD.append('_token', $('[name="_token"]').val());
                    formD.append('license_type_id', $('[name="license_type_id"]').val());
                    formD.append('step', "4");

                    $.ajax({
                        type: 'POST',
                        url: 'purchase/send',
                        data: formD,
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        success: function (res) {
                            if (res.success === true) {
                                Swal.fire({
                                    title: 'Bankanızın 3D doğrulama sayfasına yönlendiriliyorsunuz, lütfen sayfadan ayrılmayın.',
                                    didOpen: () => {
                                        Swal.showLoading()
                                    }
                                })
                                setTimeout(function () {
                                    $('#cFormArea').html(b64_to_utf8(res.content));
                                }, 2200)
                            } else {
                                Swal.fire({
                                    text: res.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Kapat",
                                    customClass: {
                                        confirmButton: "btn btn-light"
                                    }
                                })
                            }
                            formSubmitButton.disabled = false;

                            formSubmitButton.setAttribute('data-kt-indicator', 'off');

                            formContinueButton.setAttribute('data-kt-indicator', 'off');
                            formContinueButton.disabled = false;

                        },
                        error: function () {
                            formSubmitButton.disabled = false;

                            formSubmitButton.setAttribute('data-kt-indicator', 'off');

                            formContinueButton.setAttribute('data-kt-indicator', 'off');
                            formContinueButton.disabled = false;
                        }
                    })

                } else {
                    Swal.fire({
                        text: "Sorry, looks like there are some errors detected, please try again.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-light"
                        }
                    }).then(function () {
                        KTUtil.scrollTop();
                    });
                }
            });
        });

        // Expiry month. For more info, plase visit the official plugin site: https://select2.org/
        $(form.querySelector('[name="card_expiry_month"]')).on('change', function () {
            // Revalidate the field when an option is chosen
            validations[3].revalidateField('card_expiry_month');
        });

        // Expiry year. For more info, plase visit the official plugin site: https://select2.org/
        $(form.querySelector('[name="card_expiry_year"]')).on('change', function () {
            // Revalidate the field when an option is chosen
            validations[3].revalidateField('card_expiry_year');
        });

        // Expiry year. For more info, plase visit the official plugin site: https://select2.org/
        $(form.querySelector('[name="business_type"]')).on('change', function () {
            // Revalidate the field when an option is chosen
            validations[2].revalidateField('business_type');
        });
    }

    var initValidation = function () {

        // Step 2
        validations.push(FormValidation.formValidation(
            form,
            {
                fields: {
                    'hosting_plan': {
                        validators: {
                            callback: {
                                message: 'Geçerli bir hosting planı seçmelisiniz.',
                                callback: function (value, validator, $field) {
                                    if ($('[name="hosting_type"]:checked').val() === 'NETGLOW_HOSTED' && $('[name="hosting_plan"]:checked').length == 0) {
                                        return false;
                                    }
                                    return true;
                                }
                            }
                        }
                    },
                    'domain_address': {
                        validators: {
                            callback: {
                                message: 'Geçerli bir alan adı seçimi yapmalısınız.',
                                callback: function (value, validator, $field) {

                                    var re = new RegExp(/^((?:(?:(?:\w[\.\-\+]?)*)\w)+)((?:(?:(?:\w[\.\-\+]?){0,62})\w)+)\.(\w{2,6})$/);

                                    if (($('[name="domain_type"]:checked').val() == 'SELF' && ($('[name="domain_address"]').val().length <= 3 || !$('[name="domain_address"]').val().match(re)))) {
                                        return false;
                                    }
                                    return true;
                                }
                            }
                        }
                    },
                    'sub_domain_address': {
                        validators: {
                            callback: {
                                message: 'Geçerli bir alan adı seçimi yapmalısınız.',
                                callback: function (value, validator, $field) {
                                    if ($('[name="domain_type"]:checked').val() === 'NETGLOW' && $('[name="sub_domain_address"]').val().length <= 3) {
                                        return false;
                                    }
                                    return true;
                                }
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    // Bootstrap Framework Integration
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        ));

        // Step 3
        validations.push(FormValidation.formValidation(
            form,
            {
                fields: {
                    'invoice[]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur.'
                            }
                        }
                    },
                    'invoice[city]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur.'
                            }
                        }
                    },
                    'invoice[district]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur.'
                            }
                        }
                    },
                    'invoice[name]': {
                        validators: {
                            callback: {
                                message: 'Bu alan zorunludur.',
                                callback: function (value, validator, $field) {
                                    if ($('[name="invoice[kind]"]:checked').val() == 'INDIVIDUAL' && $('[name="invoice[name]"]').val().length <= 1) {
                                        return false;
                                    }

                                    return true;
                                }
                            }
                        }
                    },
                    'invoice[companyName]': {
                        validators: {
                            callback: {
                                message: 'Bu alan zorunludur.',
                                callback: function (value, validator, $field) {
                                    if ($('[name="invoice[kind]"]:checked').val() == 'CORPORATE' && $('[name="invoice[companyName]"]').val().length <= 1) {
                                        return false;
                                    }

                                    return true;
                                }
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    // Bootstrap Framework Integration
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        ));

        // Step 4
        validations.push(FormValidation.formValidation(
            form,
            {
                fields: {
                    'card[holderName]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur'
                            }
                        }
                    },
                    'card[cardNumber]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur'
                            },
                            stringLength: {
                                min: 19,
                                max: 19,
                                message: 'Geçersiz kart numarası'
                            }
                        }
                    },
                    'card[expiryMonth]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur'
                            }
                        }
                    },
                    'card[expiryYear]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur'
                            }
                        }
                    },
                    'card[cvv]': {
                        validators: {
                            notEmpty: {
                                message: 'Bu alan zorunludur'
                            }
                        }
                    }
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    // Bootstrap Framework Integration
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        ));
    }

    return {
        // Public Functions
        init: function () {


            Inputmask({
                "mask": "9999 9999 9999 9999"
            }).mask(".maskCardNumber");

            // Elements
            modalEl = document.querySelector('#kt_modal_create_account');

            if (modalEl) {
                modal = new bootstrap.Modal(modalEl);
            }

            stepper = document.querySelector('#kt_create_account_stepper');

            if (!stepper) {
                return;
            }

            form = stepper.querySelector('#kt_create_account_form');
            formSubmitButton = stepper.querySelector('[data-kt-stepper-action="submit"]');
            formContinueButton = stepper.querySelector('[data-kt-stepper-action="next"]');


            $(document).on('click', '.select-license-button', function (e) {
                e.preventDefault();
                let item = this;
                let id = $(item).data('id');
                $('[name="license_type_id"]').val(id);

                $('.select-license-button').hide();
                $(item).next('.spinner-element').show();
                formData.license_type_id = id;
                calcCart(1);

            })

            $(document).on('focus', '.domain_address_input', function (e) {
                $('[name="domain_type"][value="SELF"]').prop('checked', true).trigger('change');
            })

            $(document).on('focus', '.sub_domain_address_input', function (e) {
                $('[name="domain_type"][value="NETGLOW"]').prop('checked', true).trigger('change');
            })

            $(document).on('keyup', '.domain_address_input', function (e) {
                $('.sub_domain_address_input').val('').trigger('change');
                validations[1].validate();
            })

            $(document).on('keyup', '.sub_domain_address_input', function (e) {
                $('.domain_address_input').val('').trigger('change');
                validations[1].validate();
            })

            $(document).on('change', '[name="hosting_type"]', function (e) {
                formData.hosting_type = $('[name="hosting_type"]:checked').val();
                if ($('[name="hosting_type"]:checked').val() === 'NETGLOW_HOSTED') {
                    $('.domain_address_input').closest('.fv-row').hide(500);
                    $('#net2').prop('checked', true).trigger('change');
                } else {
                    $('.domain_address_input').closest('.fv-row').show(500);
                }

                // calcCart(2);
            })

            $(document).on('change', '[name="hosting_plan"]', function (e) {

                formData.hosting_plan = $('[name="hosting_plan"]:checked').val();

                // calcCart(2);

            })


            const checkStep3 = () => {
                if ($('[name="invoice[kind]"]:checked').val() === 'CORPORATE') {
                    $('.net-step-invoice').find('.s-corporate').fadeIn(800);
                    $('.net-step-invoice').find('.s-individual').hide();
                } else {
                    $('.net-step-invoice').find('.s-corporate').hide();
                    $('.net-step-invoice').find('.s-individual').fadeIn(800);
                }
            }
            $(document).on('change', '[name="invoice[kind]"]', checkStep3);


            checkStep3();
            initStepper();
            initValidation();
            handleForm();

            function getInstallments(val) {
                $('#installments-body').html('<tr><td colspan="3" class="text-center"> <span class="spinner-border text-primary align-middle ms-2"></span></td></tr>');
                $.ajax({
                    type: 'POST',
                    url: 'purchase/check_card',
                    data: {
                        cardNumber: val,
                        amount: cartTotal,
                        _token: $('[name="_token"]').val()
                    },
                    dataType: 'json',
                    success: function (res) {
                        $('#installments-body').html('');
                        if (res.success === true) {

                            $.each(res.data, function (index, item) {

                                let html = '<tr class="cursor-pointer installment-row">' +
                                    '                                                            <td>' +
                                    '                                                                <div class="form-check form-check-custom form-check-solid">' +
                                    '                                                                    <input class="form-check-input" name="selectInstallment" type="radio" value="' + item.num + '"/>' +
                                    '                                                                <label class="fw-bold ms-2 fs-6" for="flexRadioDefault">' +
                                    '                                                                        ' + item.text + '' +
                                    '                                                                    </label>' +
                                    '</div>' +
                                    '                                                           </td>' +
                                    '                                                            <td class="fs-6">' + item.monthlyPrice + '</td>' +
                                    '                                                            <td data-net-amount="' + item.amount.totalPrice + '" class="fs-6 total-amount-row">' + item.totalPrice + '</td>' +
                                    '                                                        </tr>'
                                $('#installments-body').append(html);
                            })

                            $('[name="selectInstallment"]').eq(0).prop('checked', true).trigger('change');
                        }
                        console.log('taksit bilgisi', res);
                    }
                })
            }

            $('[name="card[cardNumber]"]').on('change', function (e) {
                let val = $(this).val().replaceAll(' ', '').replaceAll('_', '');
                getInstallments(val);
            })
            $('[name="card[cardNumber]"]').on('keyup', function (e) {
                let val = $(this).val().replaceAll(' ', '').replaceAll('_', '');
                if (val.length < 6) {
                    $('#installments-body').html('');
                    return false;
                }
                if (val.length > 6) return false;

                getInstallments(val)
            })

            $(document).on('click', '.installment-row', function (e) {
                $(this).find('input[name="selectInstallment"]').prop('checked', true).trigger('change');
            })

            $(document).on('change', 'input[name="selectInstallment"]', function (e) {
                var totalAmount = $(this).closest('tr').find('.total-amount-row').html();
                $('.cart-summary-area').find('.cart-payable-area').html(totalAmount);
            })
            // $('.select-license-button').eq(2).click();
            // stepperObj.goNext();
            // stepperObj.goNext();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTCreateAccount.init();
});
