<div>
    @if(isset($element['label']))
        <!--begin::Label-->
        <label class="form-label fw-bold {{$required ? "required" : ""}}">
            {!! nl2br($element['label']) !!}
        </label>
        <!--end::Label-->
    @endif
    @if($element['type'] == "select")
        <x-portal.form-elements.select :name="$element['name']"
                                       :options="$options"
                                       :selectedOption="$value"
                                       :customAttr="$attrs ?? null"
        />
    @elseif($element['type'] == "radio")
        @foreach($element["options"] as $optionValue => $optionLabel)
            <label class="form-check form-check-custom form-check-solid mb-3">
                <!--begin::Input-->
                <input class="form-check-input" type="radio" name="{{$element['name']}}"
                       value="{{$optionValue}}" {{$optionValue == $value ? "checked" : ""}} {{$attrs}}>
                <!--end::Input-->
                <!--begin::Label-->
                <span class="form-check-label text-gray-800 d-flex flex-column align-items-start">
                    {{$optionLabel}}
                </span>
                <!--end::Label-->
            </label>
        @endforeach
    @elseif($element['type'] == "checkbox")
        @foreach($element["options"] as $optionValue => $optionLabel)
            <label class="form-check form-check-custom form-check-solid mb-3">
                <!--begin::Input-->
                <input class="form-check-input" type="checkbox" name="{{$element['name']}}[]"
                       value="{{$optionValue}}" {{$value && in_array($optionValue, $value) ? "checked" : ""}} {{$attrs}}>
                <!--end::Input-->
                <!--begin::Label-->
                <span class="form-check-label text-gray-800 d-flex flex-column align-items-start">
                    {{$optionLabel}}
                </span>
                <!--end::Label-->
            </label>
        @endforeach
    @elseif($element['type'] == "textarea")
        <textarea name="{{$element['name']}}" class="form-control form-control-lg form-control-solid" {{$attrs}}>{{$value}}</textarea>
    @else
        <input type="text" name="{{$element['name']}}" value="{{$value}}"
               class="form-control form-control-lg form-control-solid" {{$attrs}}>
    @endif
</div>
