{{-- laravel-admin port: Tab widget --}}
<div class="la-card la-widget">
    <div class="la-tabs-nav">
        @foreach (array_keys($tab->getTabs()) as $i => $name)
            <span @if ($i === 0) class="la-active" @endif>{{ $name }}</span>
        @endforeach
    </div>
    <div class="la-tabs-body">
        @foreach ($tab->getTabs() as $name => $content)
            <div>
                <div class="la-tab-title">{{ $name }}</div>
                <div>{!! \Encore\Admin\Layout\Content::renderContent($content) !!}</div>
            </div>
        @endforeach
    </div>
</div>
