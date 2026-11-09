@php($current = $getState())
@php($icons = \Encore\Admin\Support\IconCatalog::all())

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="la-iconpicker" x-data="{ open: false, q: '' }" @click.outside="open = false" @keydown.escape.window="open = false">
        <div class="la-iconpicker-row">
            <span class="la-iconpicker-preview" title="{{ $current ?: 'fa-bars' }}">
                @svg($current ? \Encore\Admin\Support\IconResolver::resolve($current) : 'fas-bars')
            </span>

            <input
                id="{{ $getId() }}"
                type="text"
                x-ref="input"
                wire:model.live.debounce.400ms="{{ $getStatePath() }}"
                placeholder="fa-users"
                class="la-iconpicker-input"
            />

            <button type="button" class="la-btn la-iconpicker-toggle" @click="open = !open">
                Choose…
            </button>
        </div>

        <div class="la-iconpicker-panel" x-show="open" x-cloak>
            <input type="text" x-model="q" placeholder="Search icons…" class="la-iconpicker-search" autocomplete="off">

            <div class="la-iconpicker-grid">
                @foreach ($icons as $icon)
                    <button
                        type="button"
                        class="la-iconpicker-item {{ $current === 'fa-'.$icon || $current === $icon ? 'la-iconpicker-item-active' : '' }}"
                        title="fa-{{ $icon }}"
                        data-name="{{ $icon }}"
                        x-show="!q || @js($icon).includes(q.toLowerCase())"
                        @click="
                            $refs.input.value = 'fa-{{ $icon }}';
                            $refs.input.dispatchEvent(new Event('input', { bubbles: true }));
                            open = false;
                        "
                    >@svg('fas-'.$icon)</button>
                @endforeach
            </div>
        </div>
    </div>
</x-dynamic-component>
