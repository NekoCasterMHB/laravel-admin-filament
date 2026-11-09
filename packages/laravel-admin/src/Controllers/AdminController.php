<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    use HasResourceActions;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Title';

    /**
     * Set description for following 4 action pages.
     *
     * @var array
     */
    protected $description = [
        //        'index'  => 'Index',
        //        'show'   => 'Show',
        //        'edit'   => 'Edit',
        //        'create' => 'Create',
    ];

    /**
     * Get content title.
     *
     * @return string
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Page description (the original Content::description()).
     *
     * @param string $action index|show|edit|create
     *
     * @return string|null
     */
    public function description(string $action): ?string
    {
        return $this->description[$action] ?? null;
    }

    /**
     * Make a grid builder.
     *
     * Port note: public visibility (original was protected) so the Filament
     * bridge (Encore\Admin\Filament\Resource) can consume the builders.
     * Custom controllers must declare grid()/detail()/form() public.
     *
     * @return Grid|null
     */
    public function grid()
    {
        return null;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show|null
     */
    public function detail($id)
    {
        return null;
    }

    /**
     * Make a form builder.
     *
     * @return \Encore\Admin\Form|null
     */
    public function form()
    {
        return null;
    }

    /**
     * Index interface.
     *
     * Port note: kept for API compatibility. Rendering now goes through the
     * Filament bridge (Encore\Admin\Filament\Resource subclasses) which
     * consumes grid()/form()/detail() directly; the Content-based page
     * bodies remain usable by custom controllers served through regular
     * routes.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? trans('admin.show'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form());
    }
}
