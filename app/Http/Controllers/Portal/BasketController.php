<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\CouponCode;
use App\Models\Invoice;
use App\Models\Price;
use App\Models\Product;
use App\Traits\AjaxResponses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BasketController extends Controller
{
    use AjaxResponses, SoftDeletes;

    public function index()
    {
        $basket = Auth::user()->basket;
        return view("portal.pages.basket.index", compact("basket"));
    }

    public function applyCoupon(Request $request)
    {
        $basket = Auth::user()->basket;
        if (!$basket)
            return $this->errorResponse('Sepetiniz boş.');


        $code = CouponCode::where('is_active', 1)
            ->where('end_date', '>=', Carbon::today()->format('Y-m-d'))
            ->where('coupon_code', $request->code)->first();

        if (!$code)
            return $this->errorResponse('Geçersiz kod, lütfen tekrar deneyin.');

        $count_uses = Invoice::where('coupon_code_id', $code->id)->count();

        if ($code->use_limit && $code->use_limit > 0 && $count_uses >= $code->use_limit)
            return $this->errorResponse('Bu kupon artık geçerli değil.');

//        if (Invoice::where('coupon_code_id', $code->id)->where('user_id',Auth::id())->exists())
//            return $this->errorResponse('Bu kuponu daha önce kullandınız.');

        if ($code->only_new_users == 1){
            if (Invoice::whereStatus('PAID')->where('user_id',Auth::id())->exists()){
                return $this->errorResponse('Bu kupon yeni üyelere özeldir.');

            }
        }

        if ($code->product_ids) {
            foreach ($basket->items as $item) {
                if (!in_array($item->product_id, $code->product_ids)) {
                    return $this->errorResponse('Sepetinizde bu kupona uygun olmayan ürünler var.');
                }

            }
        }

        $basket->coupon_code_id = $code->id;
        $basket->coupon_code_text = $code->coupon_code;

        if($basket->save()){
            return $this->successResponse('Tebrikler! İndirim başarıyla sepetinize uygulandı!');
        }
        return $request->all();
    }

    public function removeCoupon(Request $request)
    {
        $basket = Auth::user()->basket;

        if ($basket){
            $basket->coupon_code_text = null;
            $basket->coupon_code_id = null;
            $basket->save();
        }

        return $this->successResponse('Kupon başarıyla silindi.');
    }

    public function addToBasket(Price $price, Request $request)
    {
        $price->load("product");

        $test_products = Product::testProducts();
        $test_products = $test_products->filter(function ($item) {
            return $item->usable === true;
        });
        $is_test_product = $price->is_test_product == 1;
        if ($is_test_product) {
            $test_product = $price->product;
            if (!$test_products->contains('id', $test_product->id)) {
                return $this->errorResponse('Daha önce ücretsiz test ürününden faydalandınız.');
            }
            $basket = Auth::user()->basket;
            if ($basket) {
                $basket_available = true;

                $basket->items->map(function ($item) use ($test_product, &$basket_available) {
                    if ($item->product_id == $test_product->id) {
                        $basket_available = false;
                    }
                });

                if (!$basket_available) {
                    return $this->errorResponse('Bu üründen en fazla 1 adet ekleyebilirsiniz.');
                }
            }
        }
        DB::beginTransaction();
        try {
            if (!$is_test_product && $price->product->is_active != 1) {
                return $this->errorResponse('Geçersiz istek.');
                DB::rollBack();
            }
            $basket = Auth::user()->basket;
            if (!$basket) {
                $basket = Basket::create([
                    "user_id" => Auth::id()
                ]);
            }

            /* start::SERVICE VALIDATE*/
            $serviceNames = collect($price->product->attrs)->pluck("name")->toArray();
            $serviceData = array_intersect_key($request->all(), array_flip($serviceNames));
            /* end::SERVICE VALIDATE*/

            if ($is_test_product && isset($test_product) && $test_product) {

            }
            BasketItem::create([
                "product_id"          => $price->product->id,
                "price_id"            => $price->id,
                "basket_id"           => $basket->id,
                "additional_services" => count($serviceData) <= 0 ? null : $serviceData,
                'is_test_product'     => ($is_test_product && isset($test_product) && $test_product) ? 1 : 0
            ]);

            $basket->coupon_code_id = null;
            $basket->coupon_code_text = null;
            $basket->save();

            DB::commit();
            return $this->successResponse("", ["redirectUrl" => route("portal.basket.index")]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function removeBasketItem(BasketItem $basketItem)
    {
        if ($basketItem->delete()) {
            $basket = Auth::user()->basket;
            $basket->coupon_code_id = null;
            $basket->coupon_code_text = null;
            $basket->save();
            return $this->successResponse("", ["basket_summary" => Auth::user()->basket->basketSummary()]);
        }
        return $this->errorResponse();
    }

    public function payment()
    {
        if (!Auth::user()->basket || Auth::user()->basket->items->count() == 0) {
            return redirect()->route("portal.dashboard");
        }
        $basket = Auth::user()->basket;
        return view("portal.pages.basket.payment.index", compact("basket"));
    }

    public function paymentPost(Request $request)
    {
        return $this->successResponse("payment post");
    }

    public function copyItemAddToBasket(BasketItem $basketItem)
    {
        $request = isset($basketItem->additional_services) ? new Request($basketItem->additional_services) : new Request();
        return $this->addToBasket($basketItem->price, $request);

        return $this->successResponse("");
    }

    public function changePeriodToBasket(Request $request, BasketItem $basketItem)
    {
        $test_product_id = config('test_product.product_id');
        $test_price_id = config('test_product.price_id');

        $basketItem->price_id = $request->price_id;
//        if (Auth::user()->getTestProduct() && $basketItem->product_id == $test_product_id) {
//            if ($test_price_id == $request->price_id) {
//                $basketItem->is_test_product = 1;
//            } else {
//                $basketItem->is_test_product = 0;
//            }
//        }
        if ($basketItem->save()) {
            return $this->successResponse("");
        }
        return $this->errorResponse(__("error_response"));
    }
}
