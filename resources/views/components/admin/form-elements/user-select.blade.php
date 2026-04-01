@props([
    'id' => '',
    'name' => '',
    'customClass' => 'userSelect',
    'isSolid' => true,
    'dropdownParent' => '',
    'allowClear' => false,
    'placeholder' => '&nbsp;',
    'hideSearch' => false,
    'options' => [],
    'selectedOption' => '',
    'required' => '',
    'customAttr' => '',
    'relations' => null
])
<x-admin.form-elements.select :id="$id"
                              :name="$name"
                              :customClass="$customClass"
                              :isSolid="$isSolid"
                              :dropdownParent="$dropdownParent"
                              :placeholder="$placeholder"
                              :selectedOption="$selectedOption"
                              :allowClear="$allowClear"
                              :hideSearch="$hideSearch"
                              :customAttr="$customAttr"
                              :ajaxSelect2="true"
                              :required="$required"/>
@push('js')
    <script>
        $(document).ready(function () {
            let customClass = "{{$customClass}}";
            let select = $(`.${customClass.split(' ')[0]}`);
            select.select2({
                tags: false,
                language: {
                    searching: function () {
                        return "{{__("searching")}}...";
                    },
                    inputTooShort: function () {
                        return "{{__("custom_field_is_min_size", ["name" => __("search"), "size" => 3])}}";
                    },
                    "noResults": function () {
                        return "{{__("result_not_found")}}";
                    }
                },
                placeholder: "{{__("family_selection")}}",
                ajax: {
                    url: '{{route("admin.users.search")}}',
                    dataType: 'json',
                    type: "GET",
                    quietMillis: 50,
                    data: function (term) {
                        return {
                            term: term,
                            relations: "{{$relations}}"
                        };
                    },
                    processResults: function (data) {
                        var res = data.items.map(function (item) {
                            return {
                                id: item.id,
                                text: item.name,
                                extraParams: item.extraParams
                            };
                        });
                        return {
                            results: res
                        };
                    }
                }
            });
        });
    </script>
@endpush
