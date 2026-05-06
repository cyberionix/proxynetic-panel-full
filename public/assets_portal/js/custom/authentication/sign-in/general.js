"use strict";

// Class definition
var KTSigninGeneral = function () {
    // Elements
    var form;
    var submitButton;
    var validator;

    // Handle form
    var handleValidation = function (e) {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'email': {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message: 'Geçerli bir e-posta adresi girmelisiniz.',
                            },
                            notEmpty: {
                                message: 'Geçerli bir e-posta adresi girmelisiniz.'
                            }
                        }
                    },
                    'password': {
                        validators: {
                            notEmpty: {
                                message: 'Sisteme kayıtlı parolanızı girmelisiniz.'
                            }
                        }
                    }
                },
                plugins: {
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',  // comment to enable invalid state icons
                        eleValidClass: '' // comment to enable valid state icons
                    })
                }
            }
        );
    }

    var handleSubmitDemo = function (e) {
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            // Prevent button default action
            e.preventDefault();
            console.log( $("#signInForm").attr("action"))
            // Validate form
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    $.ajax({
                        type: "POST",
                        url: $("#signInForm").attr("action"),
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            email: $("#signInForm [name='email']").val(),
                            password: $("#signInForm [name='password']").val(),
                        },
                        beforeSend: function () {
                            propSubmitButton($("#kt_sign_in_submit"))
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
                                    window.location.href = res.redirect;
                                }, 1000);
                            } else {
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
                        complete: function () {
                            propSubmitButton($("#kt_sign_in_submit"),false)
                        }
                    });
                }
            });
        });
    }

    var handleSubmitAjax = function (e) {
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            // Prevent button default action
            e.preventDefault();

            // Validate form
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    // Hide loading indication
                    submitButton.removeAttribute('data-kt-indicator');

                    // Enable button
                    submitButton.disabled = false;

                    // Check axios library docs: https://axios-http.com/docs/intro
                    axios.post('/your/ajax/login/url', {
                        email: form.querySelector('[name="email"]').value,
                        password: form.querySelector('[name="password"]').value
                    }).then(function (response) {
                        if (response) {
                            form.querySelector('[name="email"]').value = "";
                            form.querySelector('[name="password"]').value = "";

                            const redirectUrl = form.getAttribute('data-kt-redirect-url');

                            if (redirectUrl) {
                                location.href = redirectUrl;
                            }
                        } else {
                            // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                            Swal.fire({
                                text: "Sorry, the email or password is incorrect, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    }).catch(function (error) {
                        Swal.fire({
                            text: "Sorry, looks like there are some errors detected, please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    });
                } else {
                    // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                    Swal.fire({
                        text: "Sorry, looks like there are some errors detected, please try again.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            });
        });
    }

    // Public functions
    return {
        // Initialization
        init: function () {
            form = document.querySelector('#signInForm');
            submitButton = document.querySelector('#kt_sign_in_submit');

            handleValidation();
            handleSubmitDemo(); // used for demo purposes only, if you use the below ajax version you can uncomment this one
            //handleSubmitAjax(); // use for ajax submit
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});
