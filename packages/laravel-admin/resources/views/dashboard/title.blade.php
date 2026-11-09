{{-- laravel-admin port: dashboard title (original AdminLTE style:
     large thin centred title + small uppercase grey links) --}}
@php($user = \Encore\Admin\Facades\Admin::user())
@php($repo = 'https://github.com/NekoCasterMHB/laravel-admin-filament')
<div class="la-deprecated-title">
    <div class="la-big-title">{{ config('admin.name') }}</div>

    <div class="la-title-links">
        <a href="{{ $repo }}" target="_blank">Github</a>
        <a href="{{ $repo }}/blob/main/docs/port-mapping.md" target="_blank">Documentation</a>
        <a href="https://github.com/z-song/laravel-admin" target="_blank">Upstream</a>
    </div>

    <div class="la-title-meta">
        @if ($user)
            <span>{{ __('admin.welcome_back') }} {{ $user->name }}</span>
            <span class="la-meta-sep">·</span>
        @endif
        @if (config('admin.show_version'))
            <span>v{{ \Encore\Admin\Admin::VERSION }}</span>
            <span class="la-meta-sep">·</span>
        @endif
        <span>{{ now()->format('Y-m-d H:i') }}</span>
    </div>
</div>
