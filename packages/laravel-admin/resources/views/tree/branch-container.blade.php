{{-- laravel-admin port: tree container --}}
<div class="la-card la-widget la-tree">
    @php($items = $tree->treeData())
    @include('admin.tree.branch-items', ['items' => $items])
    @if (empty($items))
        <span class="la-empty">—</span>
    @endif
</div>
