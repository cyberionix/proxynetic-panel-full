<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class PublicInvoiceController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->query('token');
        if (!$token) abort(404);

        $invoice = Invoice::where('share_token', $token)->firstOrFail();
        $invoice->load('items', 'user');

        return view('public.invoice', compact('invoice'));
    }
}
