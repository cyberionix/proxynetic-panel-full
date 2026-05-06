<?php

namespace App\View\Components\Admin\FormElements;

use App\Models\UserAddress;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserAddressSelect extends Component
{

    public $options;

    public function __construct(public $userId)
    {
        $options = UserAddress::with(["city", "district"])->whereUserId($userId)->get()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->title . " | " . $item?->district?->title . "-" . $item?->city?->title,
                'extraParams' => $item
            ];
        });

        $this->options = $options;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.user-address-select');
    }
}
