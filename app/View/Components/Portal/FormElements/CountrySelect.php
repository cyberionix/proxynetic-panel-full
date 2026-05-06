<?php

namespace App\View\Components\Portal\FormElements;

use App\Models\Country;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CountrySelect extends Component
{
    public function __construct(public $options = [])
    {
        $options = Country::all()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->title,
            ];
        });

        $this->options = $options;
    }

    public function render(): View|Closure|string
    {
        return view('components.portal.form-elements.country-select');
    }
}
