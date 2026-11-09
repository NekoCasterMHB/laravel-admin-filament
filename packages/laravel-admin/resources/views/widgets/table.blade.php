{{-- laravel-admin port: Table widget --}}
<div class="la-card la-widget la-widget-table">
    @if ($table->getTitle())
        <div class="la-card-header"><strong>{{ $table->getTitle() }}</strong></div>
    @endif
    <table>
        <thead>
            <tr>
                @foreach ($table->getHeaders() as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($table->getRows() as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{!! $cell !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($table->getHeaders()) ?: 1 }}" style="color: var(--la-card-muted);">—</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
