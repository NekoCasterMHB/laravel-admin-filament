{{-- laravel-admin port: topbar user menu (original AdminLTE dropdown:
     avatar + name, user setting, language sub-menu, sign out) --}}
@php($user = \Encore\Admin\Facades\Admin::user())
@php($avatar = $user?->avatar)
@php($locales = \Encore\Admin\Support\Locales::enabled())
@php($switchable = \Encore\Admin\Support\Locales::switchable() && count($locales) > 1)
@php($current = app()->getLocale())

<div class="la-user-menu" x-data="{ open: false, sub: false }" @click.outside="open = false; sub = false" @keydown.escape.window="open = false; sub = false">
    <button type="button" class="la-user-trigger" @click="open = !open">
        @if ($avatar)
            <img src="{{ $avatar }}" alt="" class="la-avatar">
        @else
            <span class="la-avatar la-avatar-fallback">{{ mb_substr($user?->name ?? '?', 0, 1) }}</span>
        @endif
        <span class="la-user-name">{{ $user?->name }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="la-user-caret">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
        </svg>
    </button>

    <div class="la-user-dropdown" x-show="open" x-cloak x-transition.opacity.duration.120ms>
        <div class="la-user-header">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="" class="la-avatar la-avatar-lg">
            @else
                <span class="la-avatar la-avatar-lg la-avatar-fallback">{{ mb_substr($user?->name ?? '?', 0, 1) }}</span>
            @endif
            <div class="la-user-header-text">
                <strong>{{ $user?->name }}</strong>
                <span>{{ $user?->username }}</span>
            </div>
        </div>

        <a href="{{ admin_url('auth/setting') }}" class="la-menu-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" class="la-menu-icon">
                <path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"/>
            </svg>
            <span>{{ __('admin.user_setting') }}</span>
        </a>

        @if ($switchable)
            <div class="la-menu-item la-menu-item-sub" :class="{ 'la-sub-open': sub }"
                 @mouseenter="sub = true" @click.prevent="sub = !sub">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" class="la-menu-icon">
                    <path d="M57.7 193l9.4 16.4c8.3 14.5 21.9 25.2 38 29.8L163 255.7c17.2 4.9 29 20.6 29 38.5v39.9c0 11 6.2 21 16 25.9s16 14.9 16 25.9v39c0 15.6 14.9 26.9 29.9 22.6c16.1-4.6 28.6-17.5 32.7-33.8l2.8-11.2c4.2-16.9 15.2-31.4 30.3-40l8.1-4.6c15-8.5 24.2-24.5 24.2-41.7v-8.3c0-12.7-5.1-24.9-14.1-33.9l-3.9-3.9c-9-9-21.2-14.1-33.9-14.1H257c-11.1 0-22.1-2.9-31.8-8.4l-34.5-19.7c-4.3-2.5-7.6-6.5-9.2-11.3c-3.2-9.6 1.1-20 9.6-24.8l9.9-5.1c14.8-7.6 16.8-28.2 4.1-38.6l-5.7-4.7c-10.3-8.5-25.1-6.7-33.2 4.1l-8.3 11.1c-10.6 14.2-25.2 24.9-41.7 30.4l-5.1 1.7c-11.8 3.9-24.5 2.1-34.7-5.1l-6.3-4.5c-13.2-9.4-16.6-27.5-7.9-41.1l4.3-6.7c6-9.3 12.9-18 20.5-26l4.7-4.9c10.8-11.3 25.7-18.2 41.4-19.6l8.4-.7c12.4-1 24.5 3.6 33.1 12.6l6.9 7.2c5.8 6 13.5 9.9 21.6 10.7l36.7 3.7c8.6 .8 16.4 5.4 21.6 12.4l4.4 5.9c6.4 8.6 15.7 14.7 26.1 16.9l25.8 5.5c14.2 3 24.6 15.4 25.1 29.9l.1 3.7c.2 6.9-1.6 13.8-5.1 19.8z"/>
                </svg>
                <span>{{ __('admin.language') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="la-sub-caret">
                    <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                </svg>

                <div class="la-submenu" x-show="sub" x-cloak>
                    @foreach ($locales as $locale)
                        <a href="{{ route('admin.locale', ['locale' => $locale]) }}"
                           class="la-menu-item {{ $locale === $current ? 'la-menu-item-active' : '' }}">
                            <span class="la-menu-check">
                                @if ($locale === $current)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            <span>{{ \Encore\Admin\Support\Locales::label($locale) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="la-menu-divider"></div>

        <form method="POST" action="{{ admin_url('logout') }}">
            @csrf
            <button type="submit" class="la-menu-item la-menu-item-danger">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" class="la-menu-icon">
                    <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/>
                </svg>
                <span>{{ __('admin.logout') }}</span>
            </button>
        </form>
    </div>
</div>
