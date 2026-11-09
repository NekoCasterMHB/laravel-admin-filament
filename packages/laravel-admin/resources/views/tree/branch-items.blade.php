<ul>
    @foreach ($items as $item)
        <li>
            <div class="la-branch">
                @if (!empty($item['icon']))
                    <span class="la-branch-icon">@svg(\Encore\Admin\Support\IconResolver::resolve($item['icon']))</span>
                @endif
                <span class="la-branch-title">{!! $tree->renderBranch($item) !!}</span>
            </div>
            @if (!empty($item['children']))
                <div class="la-branch-children">
                    @include('admin.tree.branch-items', ['items' => $item['children']])
                </div>
            @endif
        </li>
    @endforeach
</ul>
