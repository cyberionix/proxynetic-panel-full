<?php

namespace App\Exceptions;

use App\Services\Sms\IletiMerkezi\Exceptions\CouldNotSendNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register():void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof NotFoundHttpException){
            return response()->json(['success' => false]);
        }
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $errors = array_values($e->errors());
            return response()->json([
                'success' => false,
                'message' => isset($errors[0]) && isset($errors[0][0]) ? $errors[0][0] : __('form_has_errors'),
                'errors'  => $e->errors(),
            ], 422);
        }
        else if ($e instanceof ModelNotFoundException){
            if ($request->method() == 'POST'){
                return response()->json([
                    'success' => false,
                    'message' => __("record_not_found"),
                ], 404);
            }else{
                dd("Kayıt bulamadım");
            }
        }else if($e instanceof EInvoiceManagerException){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }else if($e instanceof LocaltonetException){
            if (Auth::guard('admin')->check()){
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Sistemsel bir hata oluştu. Lütfen destek talebi oluşturarak yardım talep edin.'
            ]);
        }

        return parent::render($request, $e);
    }

    public function report(Throwable $e)
    {
        if ($e instanceof LocaltonetException){
            // yöneticiye bildirim at vsvsvs
        }
         parent::report($e);
    }
}
