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
    'customAttr' => '',
    'select2' => true
])
@if($select2)
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

@else
    <select name="{{$name}}" class="form-select {{$customClass}}" {{$customAttr}}>
        @foreach($options as $option)
            <option value="{{$option["value"]}}">{{$option["label"]}}</option>
        @endforeach
    </select>
@endif
