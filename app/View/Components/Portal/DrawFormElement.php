<?php

namespace App\View\Components\Portal;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DrawFormElement extends Component
{
    public $element, $options, $attrs, $value, $required;

    public function __construct($element, $value = null)
    {
        $attrs = $element["attrs"] ?? null;
        switch ($element["type"]) {
            case "select":
                $this->options = collect($element["options"])->map(function ($item) {
                    $res = [
                        'label' => $item["label"],
                        'value' => $item["value"]
                    ];

                    if (isset($item["extraParams"]) && $item["extraParams"]) {
                        $res["extraParams"] = base64_encode(serialize($item["extraParams"]));
                    }

                    return $res;
                })->toArray();
                break;
            case "checkbox":
                $attrs = str_replace("required", "", $attrs);
                break;
        }

        $this->attrs = str_replace(",", " ", $attrs);
        $this->required = in_array('required', explode(',', $element["attrs"] ?? ""));
        $this->element = $element;
        $this->value = $value;
    }

    public function render(): View|Closure|string
    {
        return view('components.portal.draw-form-element');
    }
}
