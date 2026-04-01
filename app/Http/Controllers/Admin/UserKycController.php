<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class UserKycController extends Controller
{
    use AjaxResponses;
    public function forceKycActive(User $user)
    {
        DB::beginTransaction();
        try {
            $user->update([
                "is_force_kyc" => 1
            ]);

            $user->kyc()->updateOrCreate(
                ['user_id' => $user->id],
                ["status" => "WAITING_FOR_DOCS"]
            );

            DB::commit();
            return $this->successResponse("KYC Doğrulama zorunlu tutuldu. Kullanıcı doğrulama yapmadan sistemde hareket edemez.");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }
    public function forceKycPassive(User $user)
    {
        DB::beginTransaction();
        try {
            $user->update([
                "is_force_kyc" => 0
            ]);

            $user->kyc()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    "status" => null,
                    "verified_at" => null
                ]
            );

            DB::commit();
            return $this->successResponse("KYC doğrulama zorunluluğu kaldırıldı.");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }
    public function confirmedKyc(User $user)
    {
        DB::beginTransaction();
        try {
            $user->kyc()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    "status" => "CONFIRMED",
                    "verified_at" => Carbon::now()
                ]
            );

            DB::commit();
            return $this->successResponse("KYC Onaylandı.");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }
    public function notConfirmedKyc(User $user)
    {
        DB::beginTransaction();
        try {
            $user->kyc()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    "status" => "NOT_CONFIRMED",
                    "verified_at" => null
                ]
            );

            DB::commit();
            return $this->successResponse("KYC Reddedildi.");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }
    public function cardFrontSideImage(User $user)
    {
        $path = storage_path('app/uploads/kyc/' . $user->id . "/" . $user->kyc->card_front_side);

        if (!file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
    public function cardBackSideImage(User $user)
    {
        $path = storage_path('app/uploads/kyc/' . $user->id . "/" . $user->kyc->card_back_side);

        if (!file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
    public function selfieImage(User $user)
    {
        $path = storage_path('app/uploads/kyc/' . $user->id . "/" . $user->kyc->selfie);

        if (!file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

}
