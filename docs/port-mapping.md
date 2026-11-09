# port-mapping.md — laravel-admin DSL → Filament 5 映射总表

> 本包(`packages/laravel-admin`)以 `Encore\Admin\` 命名空间重建原版 API,底层渲染委托 Filament v5。
> 控制器保持纯 DSL 类(`grid()/detail()/form()`),由 `Encore\Admin\Filament\Resource` 壳桥接。

## 页面架构

| 原版 | 移植版 |
|---|---|
| `Admin::routes()` 注册 HTTP resource 路由 | 注册 Filament Resources/Pages 到 Panel(`Admin::$resources/$pages`) |
| `AuthController` getLogin/postLogin(HTTP) | `Filament\Auth\Pages\Login` 子类(username 字段 + remember),URI `admin/auth/login` |
| `AuthController@setting` GET/PUT | `SettingController` Filament Page,URI `admin/auth/setting`,用户菜单入口 |
| `admin.permission` 中间件(path+method 匹配) | 资源壳 `canAccess()` + Panel `SetUpPanel`;Page 内同桥(如 MenuController) |
| `admin.log` 中间件 | Panel `->middleware([LogOperation::class])` |
| `HandleController` 4 个 AJAX 端点 | 退役 — Filament Actions(Livewire)原生承担 |
| 菜单侧边栏(admin_menu 渲染) | `MenuProvider` + `NavigationBuilder`(DB 驱动,含角色/权限过滤、fa-* 图标映射) |
| `/` 首页 Content | `HomeController`(Filament Page,`$routePath='/'`)经 Content→组件转换渲染 |

## Grid → Table

| DSL | Filament |
|---|---|
| `$grid->column('id','ID')->sortable()` | `TextColumn::make('id')->sortable()` |
| `->display(fn)`(闭包绑定行模型,可返回 HTML) | `state(Closure::bind(...))` + `->html()` |
| `->pluck('name')->label()` | `state(fn) pluck` + `->badge()` |
| `->label('info'/'primary'/…)` | `->badge()->color(map)`(success/info/primary/warning/danger→gray) |
| `->image()` / `->switch()` / `->editable()` / `->select(options)` | `ImageColumn` / `ToggleColumn` / `TextInputColumn` / `SelectColumn` |
| `->checkbox()`(布尔图标) | `IconColumn->boolean()` |
| `->badge(style)` / `->limit(n)` / `->secret(mask)` / `->prefix()` / `->suffix()` / `->copyable()` / `->link()` / `->downloadable()` / `->progressbar()` / `->orderable(col)` | TextColumn 等效实现;`orderable` → Table `reorderable(col)` |
| `$grid->model()->orderBy(...)` | `Grid\Model` 捕获,`modifyQueryUsing` 回放 |
| `$grid->actions(fn(Actions $actions))` disable{Edit,View,Delete} / getKey / row | View/Edit/Delete `visible()` 逐行执行回调 flags |
| `$grid->tools()->batch()->disableDelete()` | 批量 `DeleteBulkAction` 隐藏 |
| `$grid->disableCreateButton()` | List 页 CreateAction 不注册 |
| `$grid->filter(fn(Filter $filter))` equal/like/ilike/gt/lt/startsWith/endsWith/between/in/notIn/where + select/radio/checkbox/datetime/multipleSelect presenter | `SelectFilter`(equal/select、in/notIn multiple)、自定义 `Filter->form([...])` + query(between 双输入) |
| CSV 导出按钮 | header `Export` Action,流式 fputcsv(无队列/无新表) |

## Form → Schema

| DSL | Filament |
|---|---|
| `text/email/password/image/file/select/multipleSelect/listbox/textarea/icon/hidden/tags` | TextInput(+password/email)/FileUpload(admin 盘+目录)/Select(multiple;关系字段自动 `->relationship()` 同步)/Textarea/TextInput(+suffix)/Hidden/TagsInput |
| `editor` / `markdown` | RichEditor / MarkdownEditor |
| `url/ip/mobile/number/currency/decimal` | TextInput(url/ip/tel/numeric/step/prefix) |
| `switch/radio/checkbox(+button/card 样式差异)` | Toggle/Radio/CheckboxList |
| `color/date/datetime/time/year/month/timezone` | ColorPicker/DatePicker/DateTimePicker/TimePicker/Select(年份段)/Select(月)/Select(时区) |
| `rate` / `slider` / `divider` / `html` / `keyvalue` | Slider(0-5 step1)/ Slider / Divider / Html / KeyValue |
| `hasmany/table`(嵌套闭包) | `Repeater`(关系字段自动 relationship)|
| `embeds` | `Group->schema()` |
| `$form->tab('T', fn)` | `Tabs` 组件分组 |
| `->rules/creationRules/updateRules`(含 `{{id}}` 占位) | 操作感知 `->rules()` + 编辑态占位符替换 |
| `->default/->help/->ignore/->saving/->saved` | default/helperText/process() 管线剥离/saving 回调/mutateFormDataBefore{Create,Save} |

## Show → Infolist

`field(name,label)` → `TextEntry`;`as(fn)`(绑定行模型)→ `formatStateUsing`;`label()` → `badge()`;`unescape()` → `html()`。

## Layout / Widgets

`Content/Row/Column`(12 栅格)→ Filament `Grid(columnSpan)` + `Html` 组件;`Widgets\{Box,InfoBox,Callout,Alert,Table,Tab,Collapse,Form}` → Tailwind 版 Blade(views/widgets/*);`Tree` → `filament-tree` 页(列名原生兼容,`default_parent_id=0`)。

## 数据库与认证

- 9 张 `admin_*` 表逐列保真(见 migration);框架表仅 `migrations`(簿记);session/cache=file、queue=sync,不引入任何新表。
- 登录:username+password+remember,guard `admin`,provider `Administrator`;`FilamentUser::canAccessPanel` 恒 true(与原版一致);`HasAvatar` 输出 `avatar` 列。
- 操作日志:`admin_operation_log`,except 路径、allowed_methods 语义不变。
- 权限:`shouldPassThrough(Request)` path+method 匹配逻辑逐字保留;资源/页面的 admin URI 合成请求做匹配;`check_route_permission/check_menu_roles/menu_bind_permission/menu_exclude` 全部生效。
- 菜单导航:`admin_menu` 逐行(NavigationBuilder),`admin_role_menu` 角色过滤 + `permission` slug 过滤 + order 排序 + fa4→fa6 图标映射(`IconResolver`)。
