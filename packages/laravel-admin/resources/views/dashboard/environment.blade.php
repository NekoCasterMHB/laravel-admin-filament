{{-- laravel-admin port: environment box --}}
<div class="la-card">
    <div class="la-card-header">
        <strong>Environment</strong>
    </div>
    <div class="la-card-body">
        <table class="la-table">
            <tbody>
                @foreach ($envs as $env)
                    <tr>
                        <td class="la-key la-w36">{{ $env['name'] }}</td>
                        <td class="la-value">{{ $env['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
