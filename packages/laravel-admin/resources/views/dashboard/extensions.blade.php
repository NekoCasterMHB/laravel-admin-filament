{{-- laravel-admin port: extensions box --}}
<div class="la-card">
    <div class="la-card-header">
        <strong>Extensions</strong>
    </div>
    <div class="la-card-body">
        <table class="la-table">
            <tbody>
                @foreach ($extensions as $extension)
                    <tr>
                        <td class="la-key la-w8">
                            @if ($extension['installed'])
                                <span style="color: var(--success-500, #22c55e);">✔</span>
                            @else
                                <span style="color: var(--la-card-muted);">–</span>
                            @endif
                        </td>
                        <td class="la-value">
                            <a href="{{ $extension['link'] }}" target="_blank">{{ $extension['name'] }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
