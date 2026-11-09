<?php

// laravel-admin port: the package ships `zh_CN`/`zh_Hans` directories, which do
// not match the application locale `zh-CN`, so this override provides it.
return [
    'root' => '根',

    'button.save' => '保存',
    'button.expand_all' => '全部展开',
    'button.collapse_all' => '全部折叠',

    'components.tree.buttons.select_all.label' => '选择全部',
    'components.tree.buttons.deselect_all.label' => '取消全选',
    'components.tree.buttons.expand_all.label' => '全部展开',
    'components.tree.buttons.collapse_all.label' => '全部折叠',

    'actions.delete.confirmation.with_children' => '你确定要删除这条记录及其子记录吗？',
];
