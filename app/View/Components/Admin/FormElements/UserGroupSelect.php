<?php

namespace App\View\Components\Admin\FormElements;

use App\Models\UserGroup;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UserGroupSelect extends Component
{
    public $options;
    public function __construct($options = [])
    {
        $options = UserGroup::all()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->name,
            ];
        });

        $this->options = $options;
    }


    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.user-group-select');
    }
}
