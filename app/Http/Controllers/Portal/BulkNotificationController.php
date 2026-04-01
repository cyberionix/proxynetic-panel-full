<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BulkNotification;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class BulkNotificationController extends Controller
{
    use AjaxResponses;
    public function find(Request $request, \App\Models\BulkNotification $bulkNotification)
    {
        if ($request->is_read){
            $ids = $bulkNotification->reader_ids;
            $ids[] = auth()->user()->id;
            $bulkNotification->reader_ids = array_unique($ids);
            $bulkNotification->save();
        }
        return $this->successResponse("", ["data" => $bulkNotification]);
    }

    public function list()
    {
        $notifications = BulkNotification::orderBy('created_at', 'desc')->get();
        $alert = false;
        $count = 0;
        foreach ($notifications as $notification) {
            if (!$notification->is_read) {
                $count++;
                $alert = true;
            }
        }
        return $this->successResponse("", [
            "alert" => $alert,
            "count" => $count,
            "data" => $notifications
        ]);
    }
}
