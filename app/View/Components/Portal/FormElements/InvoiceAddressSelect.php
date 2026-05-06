<?php

namespace App\View\Components\Portal\FormElements;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class InvoiceAddressSelect extends Component
{
    public function __construct(public $options = [])
    {
        $options = Auth::user()->addresses->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->title . " (" . $item?->city?->title . "/" . $item?->district?->title . ")",
                'extraParams' => $item
            ];
        });

        $this->options = $options;
    }

    public function render(): View|Closure|string
    {
        return view('components.portal.form-elements.invoice-address-select');
    }
}
