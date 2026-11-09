{{-- laravel-admin port: topbar language switcher --}}
@php($locales = \Encore\Admin\Support\Locales::enabled())
@php($current = app()->getLocale())
<x-filament::dropdown placement="bottom-end" width="xs">
    <x-slot name="trigger">
        <button type="button" class="la-locale-btn" title="{{ __('admin.language') }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16ZM3.06 9h2.45c.12-1.7.48-3.23 1-4.42A6.52 6.52 0 0 0 3.06 9Zm.02 2a6.52 6.52 0 0 0 3.42 4.42c-.52-1.19-.88-2.72-1-4.42H3.08ZM9 17a15.6 15.6 0 0 1-1.47-6H9v6Zm0-8H7.53c.13-2.1.6-3.9 1.47-5.28V9Zm2-5.28c.86 1.37 1.34 3.18 1.47 5.28H11V3.72Zm0 13.28v-6h1.47A15.6 15.6 0 0 1 11 17Zm4.5-8h-2.45a16.4 16.4 0 0 0-1-4.42 6.52 6.52 0 0 1 3.45 4.42Zm-3.42 6.42a16.4 16.4 0 0 0 1-4.42h2.44a6.52 6.52 0 0 1-3.44 4.42Z"/>
            </svg>
            <span>{{ \Encore\Admin\Support\Locales::label($current) }}</span>
        </button>
    </x-slot>
    <x-filament::dropdown.list>
        @foreach ($locales as $locale)
            <x-filament::dropdown.list.item
                tag="a"
                href="{{ route('admin.locale', ['locale' => $locale]) }}"
                :icon="$locale === $current ? 'heroicon-m-check-circle' : null"
                color="{{ $locale === $current ? 'primary' : 'gray' }}"
            >
                {{ \Encore\Admin\Support\Locales::label($locale) }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
