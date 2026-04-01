<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $allCategories = ProductCategory::with("children")->get();
        $categories = $allCategories->whereNull("parent_id");

        $upCategoryOptions = [];
        foreach ($allCategories as $allCategory) {
            if ($allCategory->parent_id != null) continue;
            $upCategoryOptions[] = [
                "label" => $allCategory->name,
                "value" => $allCategory->id
            ];
        }

        return view("admin.pages.products.categories.index", compact(["upCategoryOptions","categories"]));
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required",
        ], [
            'name.required' => __('custom_field_is_required', ['name' => __("title")]),
        ]);

        $create = ProductCategory::create([
           "name" => $request->name,
           "seq" => $request->seq ?: 999,
           "parent_id" => $request->parent_id ?? null
        ]);

        if ($create) {
            return $this->successResponse(__("created_response", ["name" => __("product_category")]));
        }
        return $this->errorResponse();
    }

    public function find(ProductCategory $productCategory)
    {
        $productCategory->load("children");
        return $this->successResponse("", ["data" => $productCategory]);
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            "name" => "required",
        ], [
            'name.required' => __('custom_field_is_required', ['name' => __("title")]),
        ]);

        $productCategory->name = $request->name;
        $productCategory->seq = intval($request->seq) ?: 999;
        $productCategory->parent_id = $request->parent_id ?: null;

        if ($productCategory->save()) {
            return $this->successResponse(__("edited_response", ["name" => __("product_category")]));
        }
        return $this->errorResponse();
    }

    public function delete(ProductCategory $productCategory)
    {
        $productCategory->load("children");
        if (count($productCategory->children) > 0) {
            foreach ($productCategory->children as $child) {
                ProductCategory::whereId($child->id)->delete();
            }
        }
        $productCategory->delete();
        return $this->successResponse(__("deleted_response", ["name" => __("product_category")]));
    }
}
