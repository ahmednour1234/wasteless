@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();
@endphp
<style>
  .btn-primary {
    color: #fff;
    background-color: #2596be;
    border-color: #2596be;
}
</style>
@isset($configData["layout"])
@include((( $configData["layout"] === 'horizontal') ? 'layouts.horizontalLayout' :
(( $configData["layout"] === 'blank') ? 'layouts.blankLayout' :
(($configData["layout"] === 'front') ? 'layouts.layoutFront' : 'layouts.contentNavbarLayout') )))
@endisset
