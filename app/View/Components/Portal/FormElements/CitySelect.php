<?php

namespace App\View\Components\Portal\FormElements;

use App\Models\City;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CitySelect extends Component
{
    public function __construct(public $options = [])
    {
        $options = City::all()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->title,
            ];
        });

        $this->options = $options;
    }


    public function render(): View|Closure|string
    {
        return view('components.portal.form-elements.city-select');
    }
}
