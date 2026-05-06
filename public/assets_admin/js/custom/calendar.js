"use strict";

let KTGeneralFullCalendarBasicDemos = function () {
    let exampleBasic = function () {
        let todayDate = moment().startOf('day'),
            TODAY = todayDate.format('YYYY-MM-DD'),
            calendarEl = document.getElementById('kt_fullcalendar'),
            form = $("#primaryEventForm"),
            formText = "#primaryEventForm",
            modal = $("#primaryEventModal"),
            modalText = "#primaryEventModal",
            calendar = new FullCalendar.Calendar(calendarEl, {
                locale: "tr",
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },

                height: 800,
                contentHeight: 780,
                aspectRatio: 3,  // see: https://fullcalendar.io/docs/aspectRatio

                nowIndicator: true,
                now: TODAY,

                initialView: 'dayGridMonth',
                initialDate: TODAY,

                selectable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                navLinks: true,
                editable: true,

                events: {
                    url: hostUrl + 'calendar/get-events',
                    extraParams: function () {
                        var checkedVals = [];
                        $(".filterCheckboxes").each(function (index, item) {
                            if ($(item).is(':checked')) {
                                checkedVals.push($(item).attr('value'));
                            }
                        })
                        return {
                            categories: checkedVals,
                            // search: $('#searchInput').val()
                        };
                    }
                },
                select: function (e) {
                    resetForm(form);
                    modal.find(".modal-header h2").text(modal.find(".modal-header").data("create-text"))

                    form.find("[name='start_date']").val(moment(e.startStr).format("DD/MM/YYYY")).trigger("change");
                    form.find("[name='end_date']").val(moment(e.startStr).format("DD/MM/YYYY")).trigger("change");
                    form.find("[name='event_type']").prop("disabled", false);
                    form.find(".appointment").hide();

                    modal.modal("show");
                },
                eventClick: function (e) {
                    if (e.event.durationEditable) {
                        resetForm(form);

                        modal.modal("show");
                        modal.find(".modal-header h2").text(modal.find(".modal-header").data("edit-text"))

                        if (e.event.extendedProps.type === "available_appointment") {
                            form.find("[name='event_type']").val("available_appointment").trigger("input");
                            form.find("[name='id']").val(e.event.extendedProps.available_appointment_id).trigger("input");

                            form.find("[name='available_for[]']").val(e.event.extendedProps.available_for).trigger("change");

                            form.find("[name='available_appointment_type']").val(e.event.extendedProps.appointment_type).trigger("change");
                        } else if (e.event.extendedProps.type === "appointment") {
                            form.find("[name='id']").val(e.event.extendedProps.appointment_id).trigger("input");
                            form.find("[name='event_type']").val("appointment").trigger("input");
                            form.find("[name='user_id']").append(`<option value="${e.event.extendedProps.user.id}" selected>${e.event.extendedProps.user.id} | ${e.event.extendedProps.user.first_name} ${e.event.extendedProps.user.last_name}</option>`);
                            form.find("[name='appointment_type']").val(e.event.extendedProps.appointment_type).trigger("change");
                            console.log(e.event.extendedProps);
                        }
                        form.find("[name='event_type']").prop("disabled", true).trigger("change");


                        form.find("[name='start_date']").val(moment(e.event.startStr).format("DD/MM/YYYY")).trigger("change");
                        form.find("[name='start_time']").val(moment(e.event.startStr).format("HH:mm")).trigger("input");
                        form.find("[name='end_date']").val(moment(e.event.endStr).format("DD/MM/YYYY")).trigger("change");
                        form.find("[name='end_time']").val(moment(e.event.endStr).format("HH:mm")).trigger("input");

                        modal.modal("show");
                    }
                },
                eventDrop: function (e) {
                    let data = {
                        _token: $("#csrfToken").val(),
                        date: moment(e.event.startStr).format("YYYY-MM-DD")
                    };

                    if (e.event.extendedProps.type === "appointment") {
                        data.id = e.event.extendedProps.appointment_id
                        data.event_type = "appointment"

                    } else if (e.event.extendedProps.type === "available_appointment") {
                        data.id = e.event.extendedProps.available_appointment_id
                        data.event_type = "available_appointment"
                    }

                    $.ajax({
                        type: "POST",
                        url: $("#eventDropUpdateUrl").val(),
                        dataType: "json",
                        data: data,
                        complete: function (data, status) {
                            let res = data.responseJSON;
                            if (res && res.success === true) {
                                toastr.success(res?.message ?? "Başarılı");
                            } else {
                                exampleBasic()
                                toastr.error(res?.message ?? "Hata");
                            }
                        }
                    })
                },
                eventResize: function (e) {
                    // console.log("eventResize", e);
                },
                eventContent: function (info) {
                    var element = $(info.el);

                    if (info.event.extendedProps && info.event.extendedProps.description) {
                        if (element.hasClass('fc-day-grid-event')) {
                            element.data('content', info.event.extendedProps.description);
                            element.data('placement', 'top');
                            KTApp.initPopover(element);
                        } else if (element.hasClass('fc-time-grid-event')) {
                            element.find('.fc-title').append('<div class="fc-description">' + info.event.extendedProps.description + '</div>');
                        } else if (element.find('.fc-list-item-title').lenght !== 0) {
                            element.find('.fc-list-item-title').append('<div class="fc-description">' + info.event.extendedProps.description + '</div>');
                        }
                    }
                }
            });

        calendar.render();

        $(document).on("click", ".addEventBtn", function () {
            resetForm(form);

            modal.find(".modal-header h2").text(modal.find(".modal-header").data("create-text"))
            form.find("[name='event_type']").prop("disabled", false);
            form.find(".appointment").hide();
            modal.modal("show");
        });

        $(document).on("change", ".filterCheckboxes", function () {
            calendar.refetchEvents();
        })

        $(document).on("change", `${formText} [name='event_type']`, function () {
            let value = form.find("[name='event_type']").val();

            if (value === "available_appointment") {
                form.find(".available-appointment").fadeIn(150);
                form.find(".appointment").fadeOut(150);
            } else if (value === "appointment") {
                form.find(".appointment").fadeIn(150);
                form.find(".available-appointment").fadeOut(150);
            }
        })

        $(document).on("change", `${formText} [name='start_date']`, function () {
            if (modal.is(':visible')) {
                form.find("[name='end_date']").val(form.find("[name='start_date']").val()).trigger("input");
            }
        })

        $(document).on("submit", formText, function (e) {
            e.preventDefault();
            let url = form.find("[name='id']").val() ? form.data("update-url") : form.data("create-url"),
                formData = new FormData(this);
            formData.append("event_type", form.find("[name='event_type']").val())
            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    propSubmitButton(form.find("button[type='submit']"), 1);
                },
                complete: function (data, status) {
                    let res = data.responseJSON;
                    propSubmitButton(form.find("button[type='submit']"), 0);
                    if (res && res.success === true) {
                        calendar.refetchEvents();
                        Swal.fire({
                            title: "Başarılı",
                            text: res?.message ?? "",
                            icon: "success",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "Kapat"
                        }).then(() => {
                            modal.modal("hide")
                            resetForm(form)
                        })
                    } else {
                        Swal.fire({
                            title: "Hata",
                            text: res?.message ?? "",
                            icon: "error",
                            showConfirmButton: 0,
                            showCancelButton: 1,
                            cancelButtonText: "Kapat"
                        })
                    }
                }
            })
        });

        $(document).on("click", `${modalText} .deleteEventBtn`, function () {
            let res,
                id = form.find("[name='id']").val(),
                eventType = form.find("[name='event_type']").val(),
                url = $(this).data("url"),
                token = $(this).data("token")

            Swal.fire({
                icon: 'warning',
                title: "Uyarı",
                text: 'Silmek istediğinize emin misiniz?',
                showConfirmButton: 1,
                showCancelButton: 1,
                cancelButtonText: "Kapat",
                confirmButtonText: "Evet",
            }).then((result) => {
                if (result.isConfirmed === true) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        dataType: "json",
                        data: {
                            _token: token,
                            id: id,
                            event_type: eventType
                        },
                        complete: function (data, status) {
                            res = data.responseJSON;
                            if (res && res.success === true) {
                                calendar.refetchEvents();
                                Swal.fire({
                                    title: "Başarılı",
                                    text: res.message,
                                    icon: "success",
                                    showConfirmButton: 0,
                                    showCancelButton: 1,
                                    cancelButtonText: "Kapat"
                                }).then(() => {
                                    modal.modal("hide")
                                    resetForm(form)
                                })
                            } else {
                                Swal.fire({
                                    title: "Hata",
                                    text: res?.message ?? "",
                                    icon: "error",
                                    showConfirmButton: 0,
                                    showCancelButton: 1,
                                    cancelButtonText: "Kapat",
                                })
                            }
                        }
                    })
                }
            });
        });
    }
    return {
        init: function () {
            exampleBasic();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTGeneralFullCalendarBasicDemos.init();
});
