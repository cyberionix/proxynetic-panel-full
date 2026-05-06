<?php

namespace App\View\Components\Admin\FormElements;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SupportStatusesSelect extends Component
{
    public $options;
    public function __construct()
    {
        $options = [
            [
                "label" => "Yanıt bekliyor",
                "value" => "WAITING_FOR_AN_ANSWER"
            ],
            [
                "label" => "Yanıtlandı",
                "value" => "ANSWERED"
            ],
            [
                "label" => "Çözümlendi",
                "value" => "RESOLVED"
            ],
        ];
        $this->options = $options;
    }
    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.support-statuses-select');
    }
}
