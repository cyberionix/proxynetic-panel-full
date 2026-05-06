@props([
    'id' => '',
    'name' => 'district_id',
    'customClass' => 'districtSelect',
    'dropdownParent' => '',
    'allowClear' => false,
    'placeholder' => '&nbsp;',
    'hideSearch' => false,
    'options' => [],
    'selectedOption' => '',
    'required' => '',
    'customAttr' => '',
    'cityClass' => 'citySelect'
])
<x-admin.form-elements.select :id="$id"
                              :name="$name"
                              :customClass="$customClass"
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
                placeholder: "{{__(":name_selection", ["name" => __("district")])}}",
                allowClear: true,
                tags: false,
                language: {
                    searching: function () {
                        return "{{__("searching")}}...";
                    },
                    "noResults": function () {
                        return "{{__("result_not_found")}}";
                    },
                    "errorLoading": function () {
                        return '{{__("choose_a_city")}}';
                    }
                },
                ajax: {
                    url: "{{route("portal.district.search")}}",
                    type: "GET",
                    dataType: 'json',
                    quietMillis: 50,
                    data: function (term) {
                        return {
                            _token: "{{csrf_token()}}",
                            term: term,
                            city_id: $(".{{$cityClass}}").val()
                        };
                    },
                    processResults: function (data) {
                        var res = data.items.map(function (item) {
                            return {
                                id: item.id,
                                text: item.name
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
