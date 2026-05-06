<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function searchByProduct(Request $request)
    {
        if (!$request->product_id) {
            return response()->json([
                "message" => "Ürün seçmelisiniz"
            ], 404);
        }
        $data = Price::whereProductId($request->product_id)->get();
        $result = [];
        foreach ($data as $item) {
            $result[] = [
                "id" => $item->id,
                'name' => $item->duration . " " . __(mb_strtolower($item->duration_unit)) . " (" . showBalance($item->price, true) . ")",
                'duration' => $item->duration,
                'duration_unit' => $item->duration_unit,
            ];
        }

        return response()->json([
            "items" => $result
        ]);
    }
}
