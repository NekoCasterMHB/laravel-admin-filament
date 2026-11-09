{{-- laravel-admin port: standalone Form widget (display-only rendering;
    functional forms bridge into Filament schemas via toFilamentComponents) --}}
<div class="la-form-widget">
    @foreach ($form->getFields() as $entry)
        @if ($entry['type'] !== 'hidden')
            <div class="la-form-field">
                <label>{{ $entry['label'] ?? $entry['name'] }}</label>
                <input type="text" name="{{ $entry['name'] }}"
                       @unless (isset($entry['field']) && $entry['field']->getOptions() === []) readonly placeholder="(options provided via Filament bridge)" @endunless>
            </div>
        @endif
    @endforeach
</div>
