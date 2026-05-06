<?php

namespace App\View\Components\Admin\FormElements;

use App\Models\Country;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;

class CountrySelect extends Component
{
    public $options;
    public function __construct($options = [])
    {
        if (! Schema::hasTable('countries')) {
            $this->options = collect();

            return;
        }

        $options = Country::query()->orderBy('title')->get()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->title,
            ];
        });

        $this->options = $options;
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.country-select');
    }
}
