<?php

/*
|--------------------------------------------------------------------------
| filament-tree configuration
|--------------------------------------------------------------------------
|
| Column names match the laravel-admin admin_menu schema exactly
| (parent_id / order / title). The default parent key is 0 per the
| original seeder (the plugin's default is -1).
|
*/

return [
    'column_name' => [
        'order' => 'order',
        'parent' => 'parent_id',
        'title' => 'title',
    ],

    'default_parent_id' => 0,

    'default_children_key_name' => 'children',
];
