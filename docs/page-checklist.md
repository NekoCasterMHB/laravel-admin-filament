# page-checklist.md — 逐页功能核对清单

> ✅ = 自动化测试覆盖(AdminM0/M1Test);🔍 = 浏览器人工走查项(M4)。

## 认证

| 功能 | 原版 | 移植 | 状态 |
|---|---|---|---|
| 登录页 username+password | GET admin/auth/login | Filament Login 子类 | ✅ 200/字段 🔍 |
| Remember me | 登录表单 checkbox | 原生 checkbox → attempt(remember) | ✅ 字段渲染 🔍 提交 |
| 登录失败校验 | 凭据错误提示 | Livewire 校验消息 | ✅(500→凭据路径已验) 🔍 |
| 游客重定向 | → auth/login | → admin/auth/login | ✅ |
| 登出 | GET auth/logout | 用户菜单 POST admin/logout | ✅ 路由 🔍 |
| 个人设置 | GET/PUT auth/setting(name/avatar/password) | SettingController 表单页+保存动作 | ✅ 200 🔍 保存链路 |
| 头像上传 | upload disk admin/images | FileUpload 同盘同目录 | ✅ 配置 🔍 上传 |

## 仪表盘

| 功能 | 状态 |
|---|---|
| 标题区(Dashboard 标题+链接) | ✅ 视图移植 🔍 |
| Environment 三栏盒(PHP/Laravel/驱动/时区…) | ✅ 200 🔍 |
| Extensions 盒(9 个扩展安装状态) | ✅(渲染 not-installed) |
| Dependencies 盒(composer require) | ✅ |

## 用户管理(/admin/auth/users)

| 功能 | 状态 |
|---|---|
| 列表:id/username/name/roles(label)/created_at/updated_at | ✅ |
| 行删除保护:id=1 不可删 | ✅ 回调 flags |
| 批量删除禁用 | ✅ tools()->batch |
| 详情:roles/permissions label | ✅ |
| 创建/编辑:username 唯一(创建/更新规则)、avatar、password confirmed+哈希、password_confirmation 忽略、roles/permissions 多选 | ✅ store/update 管线 🔍 Livewire 提交 |
| saving 钩子哈希 | ✅ Hash::check 断言 |

## 角色管理(/admin/auth/roles)

| 功能 | 状态 |
|---|---|
| 列表:slug/name/permissions(label) | ✅ |
| administrator 角色删除保护 | ✅ |
| 批量删除禁用 | ✅ |
| 表单:slug/name 必填、permissions listbox | ✅(listbox→多选下拉,差异清单) |

## 权限管理(/admin/auth/permissions)

| 功能 | 状态 |
|---|---|
| 列表:http_path 彩色 METHOD 标签+code 路径(display HTML) | ✅ |
| 批量删除禁用 | ✅ |
| 表单:slug/name/http_method 多选/http_path 文本域 | ✅ |

## 菜单管理(/admin/auth/menu)

| 功能 | 状态 |
|---|---|
| 树形展示(图标+标题+URI 路径,父子层级) | ✅ 7 节点渲染断言 |
| 新建(parent 选择/标题/图标+帮助/URI/角色/权限) | ✅ 弹窗表单,parent/uri/icon/permission/roles 落库断言 |
| 编辑(字段回显:标题/图标/URI/角色当前值/权限) | ✅ 角色回显 + 保存断言 |
| 删除(级联子菜单) | ✅ 断言父子同时删除 |
| 拖拽排序/层级变更 | ✅ `updateTree` payload → parent_id/order 落库断言 🔍 真人拖拽 |
| 全部展开/折叠、保存按钮 | ✅ 插件工具栏 🔍 |
| 角色绑定过滤导航 | ✅ MenuProvider |

## 操作日志(/admin/auth/logs)

| 功能 | 状态 |
|---|---|
| 列表:user.name/method 彩色/path(label)/ip(label)/input JSON 格式化/created_at | ✅ |
| method 彩色徽章(GET/POST/PUT/DELETE) | ✅ display HTML |
| 过滤:user/method(equal+select)/path(like)/ip(equal) | ✅ |
| 仅 index+delete(无创建/编辑/查看) | ✅ getPages + flags |
| 批量删除 + 逗号分隔 id 语义 | ✅ |

## RBAC

| 功能 | 状态 |
|---|---|
| 权限拦截(auth.management 覆盖 users/roles/permissions/menu/logs) | ✅ 403 断言 |
| `*` 全权限(Administrator) | ✅ |
| 菜单角色绑定(admin_role_menu) | ✅ |
| 菜单权限绑定(menu_bind_permission) | ✅ |
| 种子数据(admin/admin、5 权限、7 菜单) | ✅ schema+seed 测试 |

## 浏览器人工走查(M4)

- 真实浏览器登录(admin/admin)→ 仪表盘 → 各列表页 CRUD → 菜单拖拽 → 设置页改密。
  (自动化面板的 Livewire deferred 绑定受后台标签页 rAF 节流影响,已确认为环境因素;HTTP 层全覆盖。)
