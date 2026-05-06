"use strict";

// Class definition
var KTSignupGeneral = function () {
    // Elements
    var form;
    var submitButton;
    var validator;
    var passwordMeter;

    // Handle form
    var handleForm = function (e) {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'name': {
                        validators: {
                            notEmpty: {
                                message: 'Zorunlu'
                            }
                        }
                    },
                    'surname': {
                        validators: {
                            notEmpty: {
                                message: 'Zorunlu'
                            }
                        }
                    },
                    'email': {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message: 'Değer geçerli bir e-posta adresi değil',
                            },
                            notEmpty: {
                                message: 'Zorunlu'
                            }
                        }
                    },
                    'password': {
                        validators: {
                            notEmpty: {
                                message: 'Zorunlu'
                            }
                        }
                    },
                    'password_confirmation': {
                        validators: {
                            notEmpty: {
                                message: 'Zorunlu'
                            },
                            identical: {
                                compare: function () {
                                    return form.querySelector('[name="password"]').value;
                                },
                                message: 'Parola ve onayı aynı değil'
                            }
                        }
                    },
                    'toc': {
                        validators: {
                            notEmpty: {
                                message: 'KVKK aydınlatma metnini kabul etmelisiniz'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger({
                        event: {
                            password: false
                        }
                    }),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',  // comment to enable invalid state icons
                        eleValidClass: '' // comment to enable valid state icons
                    })
                }
            }
        );

        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            validator.revalidateField('password');

            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    $.ajax({
                        type: "POST",
                        url: $("#kt_sign_up_form").attr("action"),
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            name: $("#kt_sign_up_form [name='name']").val(),
                            surname: $("#kt_sign_up_form [name='surname']").val(),
                            email: $("#kt_sign_up_form [name='email']").val(),
                            password: $("#kt_sign_up_form [name='password']").val(),
                            password_confirmation: $("#kt_sign_up_form [name='password_confirmation']").val(),
                        },
                        dataType: 'json',
                        beforeSend: function () {
                            propSubmitButton($("#kt_sign_up_submit"))
                            $(".errorMessagesAlert").html("").hide();
                        },
                        success: function (res) {
                            if (res.status === 1) {
                                Swal.fire({
                                    text: res.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    showConfirmButton: !1,
                                    showCancelButton: !1,
                                    allowOutsideClick: !1
                                })
                                setTimeout(function () {
                                    window.location.href = res.redirectUrl;
                                }, 2500);
                            } else {
                                if (res.validateMsg) {
                                    $.each(res.validateMsg, function (i, v) {
                                        $(".errorMessagesAlert").append(v + "<br>")
                                    })
                                    $(".errorMessagesAlert").show();
                                    return true;
                                }

                                Swal.fire({
                                    text: res.message,
                                    icon: "warning",
                                    showConfirmButton: !1,
                                    cancelButtonText: "Kapat",
                                    showCancelButton: !0,
                                    allowOutsideClick: !1
                                })
                            }
                        },
                        complete: function (){
                            propSubmitButton($("#kt_sign_up_submit"),false)
                        }
                    });
                }
            });
        });

        // Handle password input
        form.querySelector('input[name="password"]').addEventListener('input', function () {
            if (this.value.length > 0) {
                validator.updateFieldStatus('password', 'NotValidated');
            }
        });
    }

    // Password input validation
    var validatePassword = function () {
        return (passwordMeter.getScore() === 100);
    }

    // Public functions
    return {
        // Initialization
        init: function () {
            // Elements
            form = document.querySelector('#kt_sign_up_form');
            submitButton = document.querySelector('#kt_sign_up_submit');
            passwordMeter = KTPasswordMeter.getInstance(form.querySelector('[data-kt-password-meter="true"]'));

            handleForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTSignupGeneral.init();
});
