<?php

namespace App\Http\Controllers\Admin;

use App\Events\AppointmentCreated;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AvailableAppointment;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;
use function view;

class CalendarController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        return view('admin.pages.calendar.index');
    }

    public function getEvents(Request $request)
    {
        $events = [];

        $start = Carbon::make($request->get('start'))->format('Y-m-d H:i:s');
        $end = Carbon::make($request->get('end'))->format('Y-m-d H:i:s');

        $categories = explode(',', $request->categories);

        /* Appointments::START */
        if (in_array("appointments", $categories)) {
            $appointments = Appointment::with("user")->where('start_at', '>=', $start)->where('end_at', '<=', $end)->get();

            foreach ($appointments as $appointment) {
                $events[] = [
                    'id' => 'AP' . $appointment->id,
                    'type' => 'appointment',
                    'appointment_id' => $appointment->id,
                    'title' => $appointment->user->id . " | " . $appointment->user->full_name,
                    'explanation' => '',
                    'start' => $appointment->start_at,
                    'end' => $appointment->end_at,
                    'className' => 'bg-success',
                    'allDay' => false,
                    'user' => $appointment->user->only('id', 'first_name', 'last_name'),
                    'appointment_type' => $appointment->type,
                    'editable' => true
                ];
            }
        }

        /* Appointments::END */

        /* Available Appointments::START */
        if (in_array("available_appointments", $categories)) {
            $availableAppointments = AvailableAppointment::where('start_date', '>=', $start)->where('end_date', '<=', $end)->get();

            foreach ($availableAppointments as $appointment) {
                $events[] = [
                    'id' => 'AAP' . $appointment->id,
                    'type' => 'available_appointment',
                    'available_appointment_id' => $appointment->id,
                    'title' => __("available_appointment_time"),
                    'start' => $appointment->start_date,
                    'end' => $appointment->end_date,
                    'className' => 'bg-secondary',
                    'allDay' => false,
                    'appointment_type' => $appointment->type,
                    'available_for' => $appointment->available_for,
                    'editable' => true
                ];
            }
        }
        /* Available Appointments::END */

        $events = array_map(function ($item){
            if ($item['start'] == $item['end']){
                $item['end'] = $item['end']->addMinutes(15);
            }
            return $item;
        },$events);
        return $events;
    }

    public function store(Request $request)
    {
        $defaultDateFormat = defaultDateFormat();
        $requestValidateData = [
            "event_type" => "required",
            "start_date" => "required|date_format:{$defaultDateFormat}",
            "end_date" => "required|date_format:{$defaultDateFormat}",
            "start_time" => "required",
            "end_time" => "required",
        ];
        $requestValidateDataValue = [
            'event_type.required' => __('custom_field_is_required', ['name' => __('event_type')]),
            'start_date.required' => __('custom_field_is_required', ['name' => __('start_date')]),
            'end_date.required' => __('custom_field_is_required', ['name' => __('end_date')]),
            'start_time.required' => __('custom_field_is_required', ['name' => __('start_time')]),
            'end_time.required' => __('custom_field_is_required', ['name' => __('end_time')]),
        ];
        $request->validate($requestValidateData, $requestValidateDataValue);
        DB::beginTransaction();
        try {
            $startDate = DateTime::createFromFormat($defaultDateFormat . ' H:i', $request->start_date . ' ' . $request->start_time)->format('Y-m-d H:i');
            $endDate = DateTime::createFromFormat($defaultDateFormat . ' H:i', $request->end_date . ' ' . $request->end_time)->format('Y-m-d H:i');

            $returnMessage = "";
            switch ($request->event_type) {
                case "available_appointment":

                    $type = $request->get('available_appointment_type') ?: 'ANY';
                    $availableFor = $request->get('available_for') ?: [];

                    AvailableAppointment::create([
                        "start_date" => $startDate,
                        "end_date" => $endDate,
                        'type' => $type,
                        'available_for' => $availableFor,
                        "created_by" => auth()->user()->id
                    ]);
                    $returnMessage = __("created_response", ["name" => __("available_appointment_time")]);
                    break;
                case 'appointment':
                    $requestValidateData = [
                        "user_id" => "required",
                        "appointment_type" => "required",
                    ];
                    $requestValidateDataValue = [
                        "user_id" => __('custom_field_is_required', ['name' => __(":name_selection", ["name" => __("family")])]),
                        "appointment_type" => __('custom_field_is_required', ['name' => __('appointment_type')]),
                    ];
                    $request->validate($requestValidateData, $requestValidateDataValue);

                    $appointment = Appointment::create([
                        "start_at" => $startDate,
                        "end_at" => $endDate,
                        "type" => $request->appointment_type,
                        "user_id" => $request->user_id,
                    ]);

                    if ($appointment){
                        event(new AppointmentCreated($appointment));
                    }
                    $returnMessage = __("created_response", ["name" => __("appointment")]);
                    break;
            }

            DB::commit();
            return $this->successResponse($returnMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $defaultDateFormat = defaultDateFormat();
        $requestValidateData = [
            "id" => "required",
            "event_type" => "required",
            "start_date" => "required|date_format:{$defaultDateFormat}",
            "end_date" => "required|date_format:{$defaultDateFormat}",
            "start_time" => "required",
            "end_time" => "required",
        ];
        $requestValidateDataValue = [
            'id.required' => __('custom_field_is_required', ['name' => __('event') . " id"]),
            'event_type.required' => __('custom_field_is_required', ['name' => __('event_type')]),
            'start_date.required' => __('custom_field_is_required', ['name' => __('start_date')]),
            'end_date.required' => __('custom_field_is_required', ['name' => __('end_date')]),
            'start_time.required' => __('custom_field_is_required', ['name' => __('start_time')]),
            'end_time.required' => __('custom_field_is_required', ['name' => __('end_time')]),
        ];
        $request->validate($requestValidateData, $requestValidateDataValue);
        DB::beginTransaction();
        try {
            $startDate = DateTime::createFromFormat($defaultDateFormat . ' H:i', $request->start_date . ' ' . $request->start_time)->format('Y-m-d H:i');
            $endDate = DateTime::createFromFormat($defaultDateFormat . ' H:i', $request->end_date . ' ' . $request->end_time)->format('Y-m-d H:i');

            $returnMessage = "";
            switch ($request->event_type) {
                case "available_appointment":
                    $availableAppointment = AvailableAppointment::findOrFail($request->id);

                    $type = $request->get('available_appointment_type') ?: 'ANY';
                    $availableFor = $request->available_for;



                    $availableAppointment->update([
                        "start_date" => $startDate,
                        "end_date" => $endDate,
                        'type' => $type,
                        'available_for' => $availableFor,
                    ]);
                    $returnMessage = __("edited_response", ["name" => __("available_appointment_time")]);
                    break;
                case 'appointment':
                    $requestValidateData = [
                        "user_id" => "required",
                        "appointment_type" => "required",
                    ];
                    $requestValidateDataValue = [
                        "user_id" => __('custom_field_is_required', ['name' => __(":name_selection", ["name" => __("family")])]),
                        "appointment_type" => __('custom_field_is_required', ['name' => __('appointment_type')]),
                    ];
                    $request->validate($requestValidateData, $requestValidateDataValue);

                    $appointment = Appointment::findOrFail($request->id);
                    $appointment->update([
                        "start_at" => $startDate,
                        "end_at" => $endDate,
                        "user_id" => $request->user_id,
                        "type" => $request->appointment_type
                    ]);
                    $returnMessage = __("edited_response", ["name" => __("appointment")]);
                    break;
            }
            DB::commit();
            return $this->successResponse($returnMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function eventDropUpdate(Request $request)
    {
        $requestValidateData = [
            "id" => "required",
            "event_type" => "required",
            "date" => "required|date_format:Y-m-d",
        ];
        $requestValidateDataValue = [
            'id.required' => __('custom_field_is_required', ['name' => __('event') . " id"]),
            'event_type.required' => __('custom_field_is_required', ['name' => __('event_type')]),
            'date.required' => "Tarih bulunamadı",
        ];
        $request->validate($requestValidateData, $requestValidateDataValue);

        DB::beginTransaction();
        try {
            $date = $request->date;
            $returnMessage = "";
            switch ($request->event_type) {
                case "available_appointment":
                    $availableAppointment = AvailableAppointment::findOrFail($request->id);
                    if (Carbon::parse($date)->isWeekend()){
                        return $this->errorResponse("Haftasonu randevu oluşturulamaz.");
                    }
                    $availableAppointment->update([
                        "start_date" => $date . " " . $availableAppointment->start_date->format("H:i"),
                        "end_date" => $date . " " . $availableAppointment->end_date->format("H:i"),
                    ]);
                    $returnMessage = __("edited_response", ["name" => __("available_appointment_time")]);
                    break;
                case 'appointment':
                    $appointment = Appointment::findOrFail($request->id);
                    if (Carbon::parse($date)->isWeekend()){
                        return $this->errorResponse("Haftasonu randevu oluşturulamaz.");
                    }
                    $appointment->update([
                        "start_at" => $date . " " . $appointment->start_at->format("H:i"),
                        "end_at" => $date . " " . $appointment->end_at->format("H:i"),
                    ]);
                    $returnMessage = __("edited_response", ["name" => __("appointment")]);
                    break;
            }
            DB::commit();
            return $this->successResponse($returnMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $defaultDateFormat = defaultDateFormat();
        $requestValidateData = [
            "event_type" => "required",
            "id" => "required",
        ];
        $requestValidateDataValue = [
            'event_type.required' => __('custom_field_is_required', ['name' => __('event_type')]),
            'id.required' => __('custom_field_is_required', ['name' => 'id']),
        ];
        $request->validate($requestValidateData, $requestValidateDataValue);

        try {
            $returnMessage = "";
            switch ($request->event_type) {
                case "available_appointment":
                    $availableAppointment = AvailableAppointment::findOrFail($request->id);
                    $availableAppointment->delete();
                    $returnMessage = __("deleted_response", ["name" => __("available_appointment_time")]);
                    break;
                case 'appointment':
                    $appointment = Appointment::findOrFail($request->id);
                    $appointment->delete();
                    $returnMessage = __("deleted_response", ["name" => __("appointment")]);
                    break;
            }
            DB::commit();
            return $this->successResponse($returnMessage);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }
}
