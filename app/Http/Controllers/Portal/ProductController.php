<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use AjaxResponses;

    public function index(ProductCategory $productCategory)
    {
        $productCategory->load(["children","products"]);
        return view("portal.pages.products.index", compact("productCategory"));
    }

    public function show(Product $product)
    {
        if (!$product->is_active) abort(404);
        $product->load(["category", "prices"]);
        // Render direct-product view by routing into the category page
        $productCategory = $product->category;
        if (!$productCategory) abort(404);
        $productCategory->load(["children","products"]);
        // Mark the focused product so the view can highlight/scroll-to it
        return view("portal.pages.products.index", compact("productCategory") + ["focusProductId" => $product->id]);
    }

    public function testProduct()
    {
        if (Auth::check() && Auth::user()->block_test_producs) return redirect()->route('portal.dashboard');

        $products = Product::testProducts();
        return view("portal.pages.products.testProduct", compact("products"));
    }
}
