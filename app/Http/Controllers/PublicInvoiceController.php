<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\Invoice;
use App\Services\PaytrService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class PublicInvoiceController extends Controller
{
    use AjaxResponses;

    public function show(Request $request)
    {
        $token = $request->query('token');
        if (!$token) abort(404);

        $invoice = Invoice::where('share_token', $token)->firstOrFail();
        $invoice->load('items', 'user');

        return view('public.invoice', compact('invoice'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'card_name' => 'required',
            'card_number' => 'required|numeric|digits:16',
            'card_exp_month' => 'required|numeric',
            'card_exp_year' => 'required|numeric|digits:2',
            'card_cvv' => 'required|numeric|digits:3',
        ], [
            'card_name.required' => 'Kart üzerindeki isim zorunludur.',
            'card_number.required' => 'Kart numarası zorunludur.',
            'card_number.numeric' => 'Kart numarası geçersiz.',
            'card_number.digits' => 'Kart numarası 16 haneli olmalıdır.',
            'card_exp_month.required' => 'Son kullanma ayı zorunludur.',
            'card_exp_year.required' => 'Son kullanma yılı zorunludur.',
            'card_cvv.required' => 'CVV zorunludur.',
            'card_cvv.digits' => 'CVV 3 haneli olmalıdır.',
        ]);

        $invoice = Invoice::where('share_token', $request->token)->firstOrFail();
        $invoice->load('items', 'user');

        if ($invoice->status === 'PAID') {
            return back()->with('error', 'Bu fatura zaten ödenmiş.');
        }
        if ($invoice->status === 'CANCELLED') {
            return back()->with('error', 'Bu fatura iptal edilmiş.');
        }

        DB::beginTransaction();
        try {
            $netToken = Uuid::uuid4()->toString();

            $checkout = Checkout::create([
                'type' => 'CREDIT_CARD',
                'status' => '3DS_REDIRECTED',
                'amount' => $invoice->total_price_with_vat,
                'uuid_value' => $netToken,
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'extra_params' => [
                    'invoice_address_data' => $invoice->invoice_address,
                    'public_token' => $invoice->share_token,
                ]
            ]);

            $setBasket = [];
            foreach ($invoice->items as $item) {
                $setBasket[] = [$item->name, $item->total_price_with_vat, 1];
            }

            $user = $invoice->user;
            $invoiceAddress = $invoice->invoice_address;

            $okUrl = route('public.invoice.paymentResult', ['token' => $invoice->share_token]);
            $failUrl = route('public.invoice.paymentResult', ['token' => $invoice->share_token]);

            $data = [
                'cc_owner' => $request->card_name,
                'card_number' => $request->card_number,
                'expiry_month' => $request->card_exp_month,
                'expiry_year' => $request->card_exp_year,
                'cvv' => $request->card_cvv,
                'name' => $user->full_name ?? $request->card_name,
                'email' => $user->email ?? 'noreply@proxynetic.com',
                'address' => @$invoiceAddress['address'] . ' ' . @$invoiceAddress['city']['title'] . '/' . @$invoiceAddress['district']['title'],
                'phone' => $user->phone ?? '905000101010',
                'checkout_id' => $checkout->id + 51,
            ];

            $paymentArea = (new PaytrService($okUrl, $failUrl))->sendData($data, $invoice->total_price_with_vat, $setBasket);
            DB::commit();
            return $paymentArea;
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ödeme işlemi başlatılırken bir hata oluştu: ' . $e->getMessage());
        }
    }

    public function paymentResult(Request $request)
    {
        $token = $request->query('token');
        if (!$token) abort(404);

        $invoiceUrl = route('public.invoice.show', ['token' => $token]);

        if (isset($request->fail_message)) {
            return redirect($invoiceUrl)->with('payment_status', 'error')->with('payment_message', 'Ödeme başarısız: ' . $request->fail_message);
        } else {
            return redirect($invoiceUrl)->with('payment_status', 'success')->with('payment_message', 'Ödemeniz başarıyla alındı.');
        }
    }
}
