<?php

namespace App\View\Components\Admin\FormElements;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DurationUnitSelect extends Component
{
    public $options;
    public function __construct($options = [])
    {
        $options = [
            [
                'value' => "DAILY",
                'label' => __("daily"),
            ],
            [
                'value' => "WEEKLY",
                'label' => __("weekly"),
            ],
            [
                'value' => "MONTHLY",
                'label' => __("monthly"),
            ],
            [
                'value' => "YEARLY",
                'label' => __("yearly"),
            ],
            [
                'value' => "ONE_TIME",
                'label' => __("one_time"),
            ]
        ];

        $this->options = $options;
    }
    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.duration-unit-select');
    }
}
