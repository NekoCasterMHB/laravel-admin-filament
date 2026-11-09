{{-- laravel-admin port: Collapse widget --}}
<div class="la-card la-widget la-collapse">
    @foreach ($collapse->getGroups() as $name => $content)
        <details>
            <summary>{{ $name }}</summary>
            <div class="la-collapse-body">{!! \Encore\Admin\Layout\Content::renderContent($content) !!}</div>
        </details>
    @endforeach
</div>
