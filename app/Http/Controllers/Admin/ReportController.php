<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function financialReports(Request $request)
    {
        $data = [
            'submit' => false
        ];

        if ($request->isMethod('POST')) {
            $daterange = $request->date_range;
            $data['submit'] = true;
            $product_category_id = $request->product_category_id;
            $product_id = $request->product_id;

            $explode = explode(' - ', $daterange);
            $date1 = Carbon::createFromFormat('d/m/Y', $explode[0])->format('Y-m-d') . ' 00:00:00';
            $date2 = Carbon::createFromFormat('d/m/Y', $explode[1])->format('Y-m-d') . ' 23:59:59';

            $checkouts = Checkout::with(['user'])
                ->where('paid_at', '>=', $date1)
                ->where('paid_at', '<=', $date2)
                ->whereStatus('COMPLETED')->get();

            $count_checkout = $checkouts->count();
            $sum_checkout = $checkouts->sum('amount');

            $invoices = Invoice::with([
                'user', 'items.product'
            ])
                ->whereHas('items', function ($query) use ($product_id, $product_category_id) {
                    if ($product_id) {
                        $query->where('product_id', $product_id);
                    }
                    if ($product_category_id) {

                        $query->whereHas('product', function ($query) use ($product_category_id) {
                            $query->where('category_id', $product_category_id);
                        });
                    }

                })
                ->where('invoice_date', '>=', $date1)
                ->where('invoice_date', '<=', $date2)
                ->whereStatus('PAID')
                ->get();

            $matchedItems = $invoices->flatMap(function ($invoice) use ($product_id, $product_category_id) {
                return $invoice->items->filter(function ($item) use ($product_id, $product_category_id) {
                    $matchesProductId = !$product_id || $item->product_id == $product_id;
                    $matchesProductCategoryId = !$product_category_id || $item->product->category_id == $product_category_id;
                    return $matchesProductId && $matchesProductCategoryId;
                })->map(function ($item) use ($invoice) {
                    $item->invoice_user = $invoice->user;
                    return $item;
                });
            });

            $invoice_reports = [
                'count'                => $invoices->count(),
                'total_price'          => $matchedItems->sum('total_price'),
                'total_price_with_vat' => $matchedItems->sum('total_price_with_vat'),
                'draw_total_price'     => showBalance($matchedItems->sum('total_price'),true),
                'draw_total_price_with_vat' => showBalance($matchedItems->sum('total_price_with_vat'),true),
            ];


            $checkout_reports = [
                'count'    => $count_checkout,
                'sum'      => $sum_checkout,
                'sum_draw' => showBalance($sum_checkout,true)
            ];

//            return $matchedItems;

            $data['report'] = [
                'invoice'  => $invoice_reports,
                'checkout' => $checkout_reports,
                'invoice_items' => $matchedItems
            ];

        }
        $products = Product::all();
        $product_categories = ProductCategory::all();
        $data['products'] = $products;
        $data['product_categories'] = $product_categories;

        return view('admin.pages.reports.financial', $data);
    }

    public function financialReportsAjax(Request $request)
    {
        $daterange = $request->date_range;
        $product_category_id = $request->product_category_id;
        $product_id = $request->product_id;

        $explode = explode(' - ', $daterange);
        $date1 = Carbon::createFromFormat('d/m/Y', $explode[0]);
        $date2 = Carbon::createFromFormat('d/m/Y', $explode[1]);

        $invoices = Invoice::with('user')->where('invoice_date', '>=', $date1)
            ->where('invoice_date', '<=', $date2)
            ->whereStatus('PAID')
            ->get();


        return [
            'success' => true,
            'data'    => [
                'order_count'  => 1,
                'order_amount' => 1,
            ]
        ];
    }
}
