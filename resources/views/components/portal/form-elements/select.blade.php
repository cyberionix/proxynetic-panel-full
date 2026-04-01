@props([
'id' => '',
'name' => '',
'customClass' => '',
'dropdownParent' => '',
'allowClear' => false,
'placeholder' => '&nbsp;',
'hideSearch' => false,
'options' => [],
'selectedOption' => '',
'required' => '',
'multiple' => false,
'customAttr' => '',
'ajaxSelect2' => false
])
<select id="{{$id}}" name="{{$name}}" data-control="select2" {{$multiple ? 'multiple="multiple"' : ''}}
        @if($dropdownParent) data-dropdown-parent="{{$dropdownParent}}" @endif
        data-allow-clear="{{$allowClear}}"
        data-placeholder="{!! $placeholder ?: "&nbsp;" !!}"
        {{$hideSearch ? "data-hide-search=true" : ""}}
        class="form-select  {{$customClass ?? ""}}" {{$customAttr}} {{$required}}>
    <option value=""></option>
    @if($ajaxSelect2 && $selectedOption)
        <option value="{{$selectedOption["value"]}}" selected>{{$selectedOption["label"]}}</option>
    @else
        @foreach($options as $option)
            <option
                value="{{$option["value"]}}"
                {{$selectedOption == $option["value"] ? "selected" : ""}}
                {{isset($option["extraParams"]) && $option["extraParams"] ? "data-np-extra-params=" . $option["extraParams"] : ""}}
            >{{$option["label"]}}</option>
        @endforeach
    @endif
</select>
