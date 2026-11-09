{{-- laravel-admin port: dependencies box --}}
<div class="la-card">
    <div class="la-card-header">
        <strong>Dependencies</strong>
    </div>
    <div class="la-card-body">
        <table class="la-table">
            <tbody>
                @foreach ($dependencies as $name => $version)
                    <tr>
                        <td class="la-key la-w56">{{ $name }}</td>
                        <td class="la-value">{{ $version }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
