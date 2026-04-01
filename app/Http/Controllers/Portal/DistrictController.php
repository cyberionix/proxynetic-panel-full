<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function search(Request $request)
    {
        if (!$request->city_id) {
            return response()->json([
                "message" => __("choose_a_city")
            ], 404);
        }
        $term = isset($request->term["term"]) ? $request->term["term"] : '';
        $districts = District::where("city_id", $request->city_id)->where("title", "LIKE", "%" . $term . "%")->get();
        $result = [];
        foreach ($districts as $district) {
            $result[] = [
                "id" => $district->id,
                "name" => $district->title
            ];
        }

        return response()->json([
            "items" => $result
        ]);
    }
}
