<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Price;
use App\Models\Product;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    use AjaxResponses;

    public function settings(Request $request)
    {
        $urls = config('access-controls.bank_urls_string');
        $urls = explode(',', $urls);

        $test_product_config = config('test_product');


        $test_product = [];
        $test_product_price = [];
        if ($test_product_config && $test_product_config['product_id']){
            $test_product = Product::find($test_product_config['product_id']);
            $test_product_price = Price::find($test_product_config['price_id']);
        }
//        return $test_product_price;
        $localtonetHttpVerify = (bool) config('services.localtonet.http_verify');

        return view('admin.pages.system.settings', compact('urls', 'test_product', 'test_product_price', 'localtonetHttpVerify'));

    }

    public function updateSettings(Request $request)
    {
//        return $request->all();
        $urls = $request->input('urls');
        if ($urls) {
            // Yeni içerik oluşturma
            $newContent = "<?php\n\nreturn [\n    'bank_urls_string' => '" . str_replace("\r\n", ',', $urls) . "',\n];\n";

            // config/access-controls.php dosyasını güncelleme
            $configPath = config_path('access-controls.php');
            File::put($configPath, $newContent);
        }

        $test_product = $request->test_product;

        if (!isset($test_product['status'])){
            $test_product['status'] = 0;
        }
        file_put_contents(config_path('test_product.php'), '<?php return ' . var_export($test_product, true) . ';');

        $localtonetHttpVerify = $request->boolean('localtonet_http_verify');
        file_put_contents(
            config_path('localtonet_settings.php'),
            '<?php return '.var_export(['http_verify' => $localtonetHttpVerify], true).';'
        );

        Artisan::call('config:clear');

        return redirect()->route('admin.settings')->with('form_success', 'Değişiklikler başarıyla kaydedildi.');
    }
}
