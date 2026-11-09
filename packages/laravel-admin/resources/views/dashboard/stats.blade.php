{{-- laravel-admin port: dashboard stat cards (muted, borderless accents) --}}
<div class="la-stats">
    @foreach ($stats as $stat)
        <div class="la-stat">
            <span class="la-stat-icon la-icon">
                @svg($stat['icon'])
            </span>
            <div class="la-stat-body">
                <div class="la-stat-value">{{ $stat['value'] }}</div>
                <div class="la-stat-label">{{ $stat['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>
