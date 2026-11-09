{{-- laravel-admin port: Callout widget --}}
<div class="la-callout la-style-{{ $callout->getStyle() }} la-widget">
    <strong>{{ $callout->getTitle() }}</strong>
    <div class="la-content">{!! $callout->getContent() !!}</div>
</div>
