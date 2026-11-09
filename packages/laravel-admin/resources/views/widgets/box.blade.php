{{-- laravel-admin port: Box widget --}}
@php
$ringClass = match ($box->style) {
    'primary' => 'la-text-primary',
    'success' => 'la-text-success',
    'info' => 'la-text-info',
    'warning' => 'la-text-warning',
    'danger' => 'la-text-danger',
    default => '',
};
@endphp
<div class="la-card la-widget">
    <div class="la-card-header">
        <strong class="{{ $ringClass }}">{{ $box->getTitle() }}</strong>
    </div>
    <div class="la-card-body">{!! $box->getContent() !!}</div>
</div>
