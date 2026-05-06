<?php

namespace App\View\Components\Portal\FormElements;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class MyOrderSelect extends Component
{
    public function __construct(public $options = [])
    {
        $options = Auth::user()->orders->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => @$item->product_data["name"] . " (#" . $item->id . ")",
                'extraParams' => $item
            ];
        });

        $this->options = $options;
    }


    public function render(): View|Closure|string
    {
        return view('components.portal.form-elements.my-order-select');
    }
}
