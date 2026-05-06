@props([
    'id' => '',
    'name' => '',
    'customClass' => '',
    'dropdownParent' => '',
    'allowClear' => false,
    'placeholder' => '&nbsp;',
    'hideSearch' => false,
    'options' => [
        ["label" => __("general"), "value" => "GENERAL"],
        ["label" => __("order"), "value" => "ORDER"],
        ["label" => __("accounting"), "value" => "ACCOUNTING"],
        ["label" => __("technical_support"), "value" => "TECHNICAL_SUPPORT"],
],
    'selectedOption' => '',
    'required' => '',
    'customAttr' => ''
])
<x-admin.form-elements.select :id="$id"
                              :name="$name"
                              :customClass="$customClass"
                              :dropdownParent="$dropdownParent"
                              :placeholder="$placeholder"
                              :options="$options"
                              :selectedOption="$selectedOption"
                              :allowClear="$allowClear"
                              :hideSearch="$hideSearch"
                              :customAttr="$customAttr"
                              :required="$required"/>
