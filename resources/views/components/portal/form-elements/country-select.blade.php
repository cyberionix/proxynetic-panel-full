@props([
    'id' => '',
    'name' => 'country_id',
    'customClass' => '',
    'dropdownParent' => '',
    'allowClear' => false,
    'placeholder' => '&nbsp;',
    'hideSearch' => false,
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
