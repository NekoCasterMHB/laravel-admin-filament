{{-- laravel-admin port: InfoBox widget --}}
@php
    $colorClass = match ($box->getStyle()) {
        'success' => 'la-text-success',
        'danger' => 'la-text-danger',
        'warning' => 'la-text-warning',
        default => 'la-text-info',
    };
@endphp
<div class="la-card la-widget">
    <div class="la-infobox">
        <span class="la-infobox-icon {{ $colorClass }}">
            @svg(\Encore\Admin\Support\IconResolver::resolve($box->getIcon()))
        </span>
        <div>
            <div class="la-infobox-value">{{ $box->getInfo() }}</div>
            <div class="la-infobox-text">{{ $box->getText() }}</div>
        </div>
    </div>
    <div class="la-infobox-topic">{{ $box->getTopic() }}</div>
</div>
