# differences.md — 与原版 laravel-admin 的差异清单

## 结构保真度

- `app/Admin/{bootstrap.php, routes.php, Controllers}`、`config/admin.php`、migration/seeder、`lang/`、`Encore\Admin\` 命名空间与目录树 **全部保留**。
- 控制器不再继承任何 Filament 类(纯 DSL 类);每个内建资源配有一个极薄的 Filament 壳(`Encore\Admin\Filament\Resources\*Resource`),声明 `controller()` 即可。自定义资源 = 写一个 DSL 控制器 + 一个 10 行壳。
- 端点 URI 与原版一致:`/admin/auth/users|roles|permissions|logs|menu|login|setting`、`/admin`;另有移植新增端点 `GET /admin/locale/{locale}`(动态语言切换,写入会话)。

## 有意差异(移植语义)

1. **UI 引擎**:AdminLTE/jQuery/Pjax/jQuery-UI 全部退役,渲染为 Filament(Livewire 4)。视觉为 Filament 现代风格,不逐像素复刻 AdminLTE。
2. **HandleController**(`_handle_form_/_handle_action_/_handle_selectable_/_handle_renderable_` AJAX 端点)退役:行/批量/表单动作由 Filament Actions(Livewire)原生处理。
3. **PJAX**:`Encore\Admin\Middleware\Pjax` 保留为直通(alias `admin.pjax` 兼容)。
4. **登录**:字段从 email 改 username(原版即 username 登录);URI `admin/auth/login`;登出为 Filament 原生 `POST admin/logout`(原版 GET `auth/logout`,CSRF 安全实践)。
5. **可见性**:控制器 `grid()/detail()/form()/title()` 从 protected 放宽为 public(Filament 桥需要;子类声明也须 public)。
6. **模型声明**:资源模型改为在**壳类**上通过 `controller()->grid()` 推导(控制器保持 `new Grid(new Model)` 原样)。
7. **MenuController**:树形拖拽页由 `solution-forest/filament-tree`(支持 Filament v5)实现;列名经 config 与 `admin_menu` 原生对齐;`default_parent_id=0`(插件默认 -1)。原版"右侧新建 Box"变为树的 Create 弹窗(字段一致)。
8. **`title()` 可见性**同 5;`description()` 方法新增(原为属性 `$description` 数组,属性保留)。
9. **语言包**:en + zh-CN 为权威(含全部键);其余 21 语言机械搬运,新增键依赖 Laravel 的 en 回退;另支持顶栏动态切换(见 README「语言」)。
10. **侧边栏导航**:完全由 `admin_menu` 表渲染(经 `MenuProvider` + Filament 导航 builder,渲染时惰性求值),与原版 AdminLTE 侧边栏一致 — "Admin" 作为**可折叠的导航分组**,五个内建页面为组内条目,**每条保留自己的 fa-* 图标**。注:Filament 不允许"分组图标 + 条目图标"并存,故分组仅显示标签(带折叠箭头),图标落在条目上(与原版一致)。资源/页面/Cluster 的**自动导航注册已全部关闭**,Cluster 的**页内子导航也已关闭**,层级只存在于侧边栏。角色/权限过滤与 active 高亮保留。
11. **仪表盘**:沿用原版素净风格 —— 居中细体大标题 + 小号大写灰色链接(Github/Documentation/Demo)+ 灰色元信息行,以及原 Environment/Extensions/Dependencies 三个朴素材质信息盒;另加一行低对比度统计卡(用户/角色/权限/日志数,查现有表,无新表)。不使用渐变/彩色大色块。
12. **样式方案**:移植视图(仪表盘/Widgets/树)改用**自包含纯 CSS**(`resources/css/admin.css`,经 HEAD 渲染钩子内联,类前缀 `la-`,支持 `.dark` 暗色)。原因:Filament 发布的是**预编译 Tailwind**,只含其自身 UI 用到的类,自定义视图里的 Tailwind 工具类不会生效。

## 组件级差异(DSL 保留、视觉/机制等效)

- `listbox`(双栏)→ 可搜索多选下拉;`checkbox/radio` 的 button/card 皮肤 → 标准 CheckboxList/Radio/ToggleButtons 风格。
- `expand/modal/modal 大内容` → `limit()` 截断 + 原生 tooltip(`modal` 弹窗类展示器未实现,记入本清单)。
- `rate`(星级)→ Slider(0-5);`switch` 状态值由 on/off 字符串改为布尔 true/false。
- `editable`(行内编辑)→ TextInputColumn(提交交互为 Livewire,原版为 AJAX);`select` 行内编辑 → SelectColumn。
- `qrcode/carousel/secret 二维码类` → 未实现(差异清单;Carousel 组件未移植)。
- `map`(百度/高德/腾讯/谷歌 JS SDK)→ 未实现(配置键 `admin.map_provider` 保留;缺省值已改为 `leaflet` 以备 Leaflet 实现)。
- `captcha/valuePicker/listField/dateRange 系列的 UI 交互` → dateRange/datetimeRange/timeRange 以 Fieldset 双控件实现(存储为数组)。
- `FixColumns/FixHeader/热键/双击排序/右键菜单/quickCreate/selectable/simple grid` → 未实现(等效实现难度高,页面功能不受影响)。
- `ExcelExporter(xlsx)` → 仅 CSV 导出(流式;不引入 openspout 依赖)。
- 扩展系统(`admin:extend`/Extension.php/extension 目录)退役(生态已停);`config('admin.extensions')` 保留为兼容键。
- 控制台命令保留 `admin:install(适配)/admin:create-user/admin:reset-password/admin:menu(打印树)`;生成器类命令由 `make:filament-*` 替代;`uninstall/publish/minify/import/config/generate-menu` 等退役。
- `minify_assets/skin/layout/login_background_image/top_alert` 等配置键保留,AdminLTE 专属语义失效(skin→panel 主色已部分映射)。

## 数据库

- 业务库严格 9 张 `admin_*` 表,与原 migration 逐列一致(测试断言)。
- 框架仅保留 `migrations` 簿记表(任何 Laravel 应用必需,原版宿主应用同样存在)。
- session/cache 使用 file 驱动、queue=sync,故无 sessions/cache/jobs 等框架表;未启用数据库通知/导出队列。
