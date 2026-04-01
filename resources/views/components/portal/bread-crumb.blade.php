@props([
    "data"
])
@php
    if (is_array($data)){
        $title = $data[0];
        unset($data[0]);
    } else{
        $title = $data;
    }
@endphp
<div>
    <!--begin::Title-->
    <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">{{$title}}</h1>
    <!--end::Title-->
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
        <li class="breadcrumb-item text-muted">
            <a href="{{route("portal.dashboard")}}" class="text-muted text-hover-primary"><i class="ki-duotone ki-home text-gray-700 fs-6 me-1"></i> {{ __("dashboard") }}</a>
        </li>
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        @if(is_array($data))
            @foreach($data as $key => $item)
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">
                    <a href="{{$item}}" class="text-muted text-hover-primary"> {{$key}}</a>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-300 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
            @endforeach
        @endif
        <!--begin::Item-->
        <li class="breadcrumb-item text-gray-900">
            {{$title}}
        </li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
</div>
