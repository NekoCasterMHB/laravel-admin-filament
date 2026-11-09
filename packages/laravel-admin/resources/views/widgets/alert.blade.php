{{-- laravel-admin port: Alert widget --}}
<div class="la-alert la-style-{{ $alert->getStyle() }} la-widget">
    @if ($alert->getTitle())
        <strong>{{ $alert->getTitle() }}</strong>
    @endif
    <div>{!! $alert->getContent() !!}</div>
</div>
