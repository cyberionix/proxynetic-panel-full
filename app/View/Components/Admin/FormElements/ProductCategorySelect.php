<?php

namespace App\View\Components\Admin\FormElements;

use App\Models\ProductCategory;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCategorySelect extends Component
{
    public $options;
    public function __construct($options = [])
    {
        $options = ProductCategory::all()->map(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->name,
            ];
        });

        $this->options = $options;
    }
    public function render(): View|Closure|string
    {
        return view('components.admin.form-elements.product-category-select');
    }
}
