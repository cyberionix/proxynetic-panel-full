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

    public function testProduct()
    {
        if (Auth::user()->block_test_producs) return redirect()->route('portal.dashboard');

        $products = Product::testProducts();
        return view("portal.pages.products.testProduct", compact("products"));
    }
}
