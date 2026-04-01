@props([
    'id' => '',
    'name' => '',
    'customClass' => '',
    'dropdownParent' => '',
    'allowClear' => false,
    'placeholder' => '&nbsp;',
    'hideSearch' => false,
    'options' => [
        ["label" => __("low"), "value" => "LOW"],
        ["label" => __("medium"), "value" => "MEDIUM"],
        ["label" => __("high"), "value" => "HIGH"],
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
                              :ajaxSelect2="true"
                              :required="$required"/>
