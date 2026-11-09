<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Clusters\Auth;
use Encore\Admin\Form;
use Encore\Admin\Support\IconResolver;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\FilamentTree\Pages\TreePage;

class MenuController extends TreePage
{
    protected static ?int $navigationSort = 4;

    /**
     * Sidebar entries come from the admin_menu table (MenuProvider).
     *
     * {@inheritdoc}
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected static ?string $cluster = Auth::class;

    /**
     * Original URI: /admin/auth/menu.
     *
     * {@inheritdoc}
     */
    protected static ?string $slug = 'menu';

    /**
     * {@inheritdoc}
     */
    protected static int $maxDepth = 3;

    /**
     * {@inheritdoc}
     */
    public static function canAccess(): bool
    {
        if (config('admin.check_route_permission') === false) {
            return true;
        }

        $user = \Encore\Admin\Facades\Admin::user();

        if (!$user) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        $request = \Illuminate\Http\Request::create(admin_base_path('auth/menu'), 'GET');

        return (bool) $user->allPermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getNavigationLabel(): string
    {
        return trans('admin.menu');
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return trans('admin.menu');
    }

    /**
     * The menu model (config-driven, same as the original).
     *
     * {@inheritdoc}
     */
    public function getModel(): string
    {
        return config('admin.database.menu_model');
    }

    /**
     * Rows ordered by parent/order (the original tree ordering).
     *
     * {@inheritdoc}
     */
    protected function getTreeQuery(): Builder
    {
        $model = $this->getModel();

        return $model::query()
            ->orderByRaw('parent_id = 0 desc, parent_id asc')
            ->orderBy('order');
    }

    /**
     * Original tree branch payload: icon + bold title, plus the URI link.
     *
     * {@inheritdoc}
     */
    public function getTreeRecordTitle(?\Illuminate\Database\Eloquent\Model $record = null): string
    {
        if (!$record) {
            return '';
        }

        return $record->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeRecordDescription(?\Illuminate\Database\Eloquent\Model $record = null): string|\Illuminate\Support\HtmlString|null
    {
        if (!$record || !filled($record->uri)) {
            return null;
        }

        $url = url()->isValidUrl($record->uri) ? $record->uri : admin_url($record->uri);

        // Compact single-line path (the tree row keeps the item on one line;
        // the original printed the full URL inline).
        $label = url()->isValidUrl($record->uri)
            ? $record->uri
            : '/'.trim(config('admin.route.prefix'), '/').'/'.ltrim($record->uri, '/');

        return new \Illuminate\Support\HtmlString(
            '<a href="'.$url.'" class="la-branch-link" onclick="event.stopPropagation()">'.e($label).'</a>',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    {
        if (!$record) {
            return null;
        }

        return IconResolver::resolve($record->icon);
    }

    /**
     * The original edit form builder (also used for create, display fields
     * excluded there).
     *
     * @param bool $withDisplay include the read-only display fields
     *
     * @return Form
     */
    public function form($withDisplay = true)
    {
        $menuModel = config('admin.database.menu_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $menuModel());

        if ($withDisplay) {
            $form->display('id', 'ID');
        }

        $form->select('parent_id', trans('admin.parent_id'))->options($menuModel::selectOptions());
        $form->text('title', trans('admin.title'))->rules('required');
        $form->icon('icon', trans('admin.icon'))->default('fa-bars')->rules('required')->help(
            'For more icons please see <a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a>',
        );
        $form->text('uri', trans('admin.uri'));
        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        if ((new $menuModel())->withPermission()) {
            $form->select('permission', trans('admin.permission'))->options($permissionModel::pluck('name', 'slug'));
        }

        if ($withDisplay) {
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));
        }

        return $form;
    }

    /**
     * Tree create form (original: the Widgets\Form box next to the tree).
     *
     * {@inheritdoc}
     */
    protected function getCreateFormSchema(): array
    {
        return $this->form(false)->applyToSchema(Schema::make(), 'create')->getComponents();
    }

    /**
     * Tree edit form.
     *
     * {@inheritdoc}
     */
    protected function getFormSchema(): array
    {
        return $this->form()->applyToSchema(Schema::make(), 'edit')->getComponents();
    }

    /**
     * {@inheritdoc}
     */
    protected function hasDeleteAction(): bool
    {
        return true;
    }
}
