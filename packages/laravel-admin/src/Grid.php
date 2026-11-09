<?php

namespace Encore\Admin;

use Closure;
use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Displayers\Actions;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Model as GridModel;
use Encore\Admin\Grid\Tools;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter as FilamentFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class Grid.
 *
 * The laravel-admin grid DSL, bridged onto a Filament table by
 * {@see Grid::applyToTable()}.
 */
class Grid
{
    /**
     * Model instance.
     *
     * @var EloquentModel
     */
    protected EloquentModel $model;

    /**
     * @var Closure|null
     */
    protected ?Closure $builderCallback = null;

    /**
     * Grid columns.
     *
     * @var Column[]
     */
    protected array $columns = [];

    /**
     * Row actions callback: function (Actions $actions) {}.
     *
     * @var Closure|null
     */
    protected ?Closure $actionsCallback = null;

    /**
     * Tools callback: function (Tools $tools) {}.
     *
     * @var Closure|null
     */
    protected ?Closure $toolsCallback = null;

    /**
     * Filter callback: function (Filter $filter) {}.
     *
     * @var Closure|null
     */
    protected ?Closure $filterCallback = null;

    /**
     * Processed tools config.
     *
     * @var Tools|null
     */
    protected ?Tools $toolsConfig = null;

    /**
     * @var bool
     */
    protected bool $createDisabled = false;

    /**
     * @var bool
     */
    protected bool $filterDisabled = false;

    /**
     * @var bool
     */
    protected bool $exportEnabled = true;

    /**
     * Rows per page (original default pagination size).
     *
     * @var int
     */
    protected int $perPage = 20;

    /**
     * The Filament resource class serving this grid (set by AdminController).
     *
     * @var string|null
     */
    protected ?string $resource = null;

    /**
     * Order column captured from the orderable displayer (table-level
     * drag reorder).
     *
     * @var string|null
     */
    protected ?string $orderColumn = null;

    /**
     * Grid constructor.
     *
     * Port note: the Eloquent model import is aliased (EloquentModel) — a
     * bare `use ...Eloquent\Model` collides with `Grid\Model` under the
     * PHP 8.5 import-resolution rules.
     *
     * @param EloquentModel|string $model
     * @param Closure|null         $builderCallback
     */
    public function __construct(EloquentModel|string $model, ?Closure $builderCallback = null)
    {
        if (is_string($model)) {
            $model = new $model();
        }

        if (!$model instanceof EloquentModel) {
            throw new InvalidArgumentException('$model must be an Eloquent model');
        }

        $this->model = $model;
        $this->builderCallback = $builderCallback;
    }

    /**
     * Underlying model instance.
     *
     * @return EloquentModel
     */
    public function model(): GridModel
    {
        return new GridModel($this->model);
    }

    /**
     * Get the model instance (for pages/forms).
     *
     * @return EloquentModel
     */
    public function getModel(): EloquentModel
    {
        return $this->model;
    }

    /**
     * Add a grid column.
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Column
     */
    public function column(string $name, ?string $label = null): Column
    {
        return $this->columns[] = new Column($name, $label);
    }

    /**
     * Configure row actions.
     *
     * @param Closure $callback function (Actions $actions) {}
     *
     * @return $this
     */
    public function actions(Closure $callback): static
    {
        $this->actionsCallback = $callback;

        return $this;
    }

    /**
     * Configure grid tools.
     *
     * @param Closure $callback function (Tools $tools) {}
     *
     * @return $this
     */
    public function tools(Closure $callback): static
    {
        $this->toolsCallback = $callback;

        $this->toolsConfig = new Tools();

        $callback($this->toolsConfig);

        return $this;
    }

    /**
     * Configure grid filters.
     *
     * @param Closure $callback function (Filter $filter) {}
     *
     * @return $this
     */
    public function filter(Closure $callback): static
    {
        $this->filterCallback = $callback;

        return $this;
    }

    /**
     * Disable the "New" (create) button.
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableCreateButton(bool $disabled = true): static
    {
        $this->createDisabled = $disabled;

        return $this;
    }

    /**
     * Disable grid filters.
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableFilter(bool $disabled = true): static
    {
        $this->filterDisabled = $disabled;

        return $this;
    }

    /**
     * Disable the CSV export tool.
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableExport(bool $disabled = true): static
    {
        $this->exportEnabled = !$disabled;

        return $this;
    }

    /**
     * Set rows per page.
     *
     * @param int $perPage
     *
     * @return $this
     */
    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Set the Filament resource class serving this grid.
     *
     * @param string $resource
     *
     * @return $this
     */
    public function setResource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return bool
     */
    public function isCreateDisabled(): bool
    {
        return $this->createDisabled;
    }

    /**
     * @return bool
     */
    public function isBatchDeleteDisabled(): bool
    {
        return $this->toolsConfig?->isBatchDeleteDisabled() ?? false;
    }

    /**
     * Run the row-actions callback for a record.
     *
     * @param EloquentModel $record
     *
     * @return Actions
     */
    public function runActionsCallback(EloquentModel $record): Actions
    {
        $displayer = new Actions($record, $this);

        if ($this->actionsCallback) {
            ($this->actionsCallback)($displayer);
        }

        return $displayer;
    }

    /**
     * Whether a row-actions callback is configured.
     *
     * @return bool
     */
    public function hasActionsCallback(): bool
    {
        return $this->actionsCallback !== null;
    }

    /**
     * Build the Filament table from this grid.
     *
     * @param Table $table
     *
     * @return Table
     */
    public function applyToTable(Table $table): Table
    {
        $grid = $this;

        $table = $table
            ->columns($this->buildColumns())
            ->paginated([10, 20, 50, 100])
            ->defaultPaginationPageOption($this->perPage)
            ->modifyQueryUsing(function ($query) use ($grid) {
                // Replay $grid->model()->orderBy(...) customizations.
                return $grid->model()->apply($query);
            })
            ->recordActions($this->buildRecordActions());

        // Orderable displayer → table-level drag reorder on that column.
        if ($this->orderColumn !== null) {
            $table = $table->reorderable($this->orderColumn);
        }

        if ($filters = $this->buildFilters()) {
            $table = $table->filters($filters);
        }

        $headerActions = [];

        if ($this->exportEnabled) {
            $headerActions[] = $this->buildExportAction();
        }

        if ($this->toolsConfig !== null || $this->hasActionsCallback() || $this->isBatchDeleteDisabled() === false) {
            // Bulk delete is available unless disabled via tools()->batch().
            $headerActionsBulk = $this->buildBulkActions();

            if ($headerActionsBulk !== []) {
                $table = $table->toolbarActions($headerActionsBulk);
            }
        }

        if ($headerActions !== []) {
            $table = $table->headerActions($headerActions);
        }

        return $table;
    }

    /**
     * Translate the DSL columns into Filament columns.
     *
     * @return array
     */
    protected function buildColumns(): array
    {
        $columns = [];

        foreach ($this->columns as $column) {
            $name = $column->getName();

            $label = $column->getLabel() ?? Str::title(str_replace(['_', '.'], ' ', $name));
            $sortable = $column->isSortable() && !str_contains($name, '.');

            // Component-level displayers replace the base TextColumn.
            if ($image = $column->getDisplayer('image')) {
                $columns[] = \Filament\Tables\Columns\ImageColumn::make($name)
                    ->label($label)
                    ->when($image['width'] ?? null, fn ($c, $w) => $c->width((int) $w))
                    ->when($image['height'] ?? null, fn ($c, $h) => $c->height((int) $h))
                    ->disk(config('admin.upload.disk'));
                continue;
            }

            if ($column->getDisplayer('switch') !== null) {
                $columns[] = \Filament\Tables\Columns\ToggleColumn::make($name)
                    ->label($label)
                    ->sortable($sortable);
                continue;
            }

            if ($column->getDisplayer('editable')) {
                $columns[] = \Filament\Tables\Columns\TextInputColumn::make($name)
                    ->label($label)
                    ->sortable($sortable);
                continue;
            }

            if ($options = $column->getDisplayer('selectEditable')) {
                $columns[] = \Filament\Tables\Columns\SelectColumn::make($name)
                    ->label($label)
                    ->options($options)
                    ->sortable($sortable);
                continue;
            }

            if ($column->getDisplayer('checkbox')) {
                $columns[] = \Filament\Tables\Columns\IconColumn::make($name)
                    ->label($label)
                    ->boolean()
                    ->sortable($sortable);
                continue;
            }

            if ($column->getDisplayer('progressbar')) {
                $columns[] = \Filament\Tables\Columns\TextColumn::make($name)
                    ->label($label)
                    ->formatStateUsing(fn ($state) => (string) min(100, max(0, (int) $state)).'%')
                    ->badge()
                    ->color('info');
                continue;
            }

            // TextColumn-based displayers (composable).
            $filamentColumn = TextColumn::make($name)
                ->label($label)
                ->sortable($sortable);

            if ($pluck = $column->getPluckColumn()) {
                // Relation column displayed as a list of badges.
                $filamentColumn
                    ->state(fn ($record) => $record->{$name}?->pluck($pluck)->all())
                    ->badge();
            } elseif ($callback = $column->getDisplayCallback()) {
                // Custom display callback, bound to the row model, may
                // return HTML (same contract as the original).
                $filamentColumn
                    ->state(function ($record) use ($callback, $column) {
                        $bound = Closure::bind($callback, $record, $record::class);

                        return $bound($record->{$column->getName()});
                    })
                    ->html();
            } elseif ($style = $column->getLabelStyle()) {
                $filamentColumn
                    ->formatStateUsing(fn ($state) => $state === null ? '' : (string) $state)
                    ->badge()
                    ->color($this->labelColor($style));
            }

            if (($badgeStyle = $column->getDisplayer('badge')) !== null) {
                $filamentColumn->badge();
                $filamentColumn->color($badgeStyle ? $this->labelColor($badgeStyle) : 'primary');
            }

            if ($limit = $column->getDisplayer('limit')) {
                $filamentColumn->limit($limit['limit'], $limit['end']);
            }

            if ($mask = $column->getDisplayer('secret')) {
                $filamentColumn->formatStateUsing(fn ($state) => $mask);
            }

            if ($prefix = $column->getDisplayer('prefix')) {
                $filamentColumn->prefix($prefix);
            }

            if ($suffix = $column->getDisplayer('suffix')) {
                $filamentColumn->suffix($suffix);
            }

            if ($column->getDisplayer('copyable')) {
                $filamentColumn->copyable();
            }

            if ($column->getDisplayer('link')) {
                $link = $column->getDisplayer('link');
                $filamentColumn->link(
                    is_string($link['href']) ? $link['href'] : ($link['href'] ?? fn ($state) => $state),
                );
            }

            if ($column->getDisplayer('downloadable')) {
                $filamentColumn->formatStateUsing(fn ($state) => $state)
                    ->link(fn ($state) => Storage::disk(config('admin.upload.disk'))->url($state));
            }

            $columns[] = $filamentColumn;

            // The orderable displayer applies at table level; remember it.
            if ($orderColumn = $column->getDisplayer('orderable')) {
                $this->orderColumn = $orderColumn;
            }
        }

        return $columns;
    }

    /**
     * Map the original label style names to Filament badge colors.
     *
     * @param string $style
     *
     * @return string
     */
    protected function labelColor(string $style): string
    {
        return match ($style) {
            'success' => 'success',
            'info' => 'info',
            'primary' => 'primary',
            'warning', 'yellow' => 'warning',
            'danger', 'red' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Build the View / Edit / Delete row actions, honoring the flags set by
     * the original `$grid->actions()` callback.
     *
     * @return array
     */
    protected function buildRecordActions(): array
    {
        $grid = $this;

        $resource = $this->resource;

        $actions = [];

        if (class_exists($resource) && method_exists($resource, 'hasPage') && $resource::hasPage('view')) {
            $actions[] = ViewAction::make()
                ->visible(fn ($record) => !$grid->runActionsCallback($record)->isViewDisabled());
        }

        if (class_exists($resource) && method_exists($resource, 'hasPage') && $resource::hasPage('edit')) {
            $actions[] = EditAction::make()
                ->visible(fn ($record) => !$grid->runActionsCallback($record)->isEditDisabled());
        }

        $actions[] = DeleteAction::make()
            ->visible(fn ($record) => !$grid->runActionsCallback($record)->isDeleteDisabled());

        return $actions;
    }

    /**
     * Build the Filament filters from the original `$grid->filter()` config.
     *
     * @return array|null
     */
    protected function buildFilters(): ?array
    {
        if ($this->filterDisabled || $this->filterCallback === null) {
            return null;
        }

        $filterContainer = new Filter();

        ($this->filterCallback)($filterContainer);

        $filters = [];

        foreach ($filterContainer->filters() as $filter) {
            $label = $filter->getLabel();
            $column = $filter->getColumn();
            $grid = $this;

            if ($filter->getPresenter() === 'select' && $filter->getType() === 'equal') {
                $filters[] = SelectFilter::make($column, $label)
                    ->options($filter->getOptions() ?? []);
                continue;
            }

            if ($filter->getPresenter() === 'multipleSelect' && $filter->getType() === 'equal') {
                $filters[] = SelectFilter::make($column, $label)
                    ->multiple()
                    ->options($filter->getOptions() ?? []);
                continue;
            }

            if ($filter->getType() === 'in' || $filter->getType() === 'notIn') {
                $select = SelectFilter::make($column, $label)
                    ->multiple()
                    ->options($filter->getOptions() ?? []);

                $filters[] = $filter->getType() === 'notIn'
                    ? $select->modifyQueryUsing(fn ($query, $data) => $query->when(
                        filled($data[$column] ?? null),
                        fn ($query) => $query->whereNotIn($column, $data[$column]),
                    ))
                    : $select;
                continue;
            }

            // between: two inputs (optionally datetime pickers).
            if ($filter->getType() === 'between') {
                $isDate = in_array($filter->getPresenter(), ['datetime', 'date'], true);

                $from = $isDate
                    ? \Filament\Forms\Components\DateTimePicker::make($column.'_from')->label($label.' (from)')
                    : \Filament\Forms\Components\TextInput::make($column.'_from')->label($label.' (from)');
                $to = $isDate
                    ? \Filament\Forms\Components\DateTimePicker::make($column.'_to')->label($label.' (to)')
                    : \Filament\Forms\Components\TextInput::make($column.'_to')->label($label.' (to)');

                $filters[] = FilamentFilter::make($column.'-between-'.Str::random(4))
                    ->form([$from, $to])
                    ->query(function ($query, array $data) use ($column) {
                        return $query
                            ->when(filled($data[$column.'_from'] ?? null), fn ($query) => $query->where($column, '>=', $data[$column.'_from']))
                            ->when(filled($data[$column.'_to'] ?? null), fn ($query) => $query->where($column, '<=', $data[$column.'_to']));
                    });
                continue;
            }

            $formField = match ($filter->getPresenter()) {
                'datetime' => \Filament\Forms\Components\DateTimePicker::make($column)->label($label),
                'radio' => \Filament\Forms\Components\Radio::make($column)->label($label)->options($filter->getOptions() ?? []),
                'checkbox' => \Filament\Forms\Components\CheckboxList::make($column)->label($label)->options($filter->getOptions() ?? []),
                default => \Filament\Forms\Components\TextInput::make($column)->label($label),
            };

            $formField->default($filter->getDefault());

            $filters[] = FilamentFilter::make($column.'-'.Str::random(4))
                ->form([$formField])
                ->indicateUsing(function (array $data) use ($label, $column) {
                    return filled($data[$column] ?? null) ? ($label.': '.$data[$column]) : null;
                })
                ->query(function ($query, array $data) use ($filter, $grid) {
                    $value = $data[$filter->getColumn()] ?? null;

                    if ($callback = $filter->getQueryCallback()) {
                        return $query->when(filled($value), fn ($query) => $callback($query, $value));
                    }

                    return $query->when(filled($value), function ($query) use ($filter, $value) {
                        match ($filter->getType()) {
                            'equal', 'date', 'datetime' => $query->where($filter->getColumn(), $value),
                            'notEqual' => $query->where($filter->getColumn(), '!=', $value),
                            'like', 'ilike' => $query->where($filter->getColumn(), 'like', "%{$value}%"),
                            'startsWith' => $query->where($filter->getColumn(), 'like', "{$value}%"),
                            'endsWith' => $query->where($filter->getColumn(), 'like', "%{$value}"),
                            'gt' => $query->where($filter->getColumn(), '>', $value),
                            'lt' => $query->where($filter->getColumn(), '<', $value),
                            default => $query->where($filter->getColumn(), $value),
                        };
                    });
                });
        }

        return $filters;
    }

    /**
     * Streamed CSV export of the filtered rows (the original grid export
     * tool, without a queue or extra database tables).
     *
     * @return Action
     */
    protected function buildExportAction(): Action
    {
        $grid = $this;

        return Action::make('export')
            ->label(trans('admin.export'))
            ->color('gray')
            ->action(function ($livewire) use ($grid) {
                $query = null;

                if (is_object($livewire) && method_exists($livewire, 'getFilteredTableQuery')) {
                    $query = $livewire->getFilteredTableQuery();
                }

                $query ??= $grid->getModel()->newQuery();

                $grid->model()->apply($query);

                $columns = $grid->getColumns();

                $headers = [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="'.Str::snake(class_basename($grid->getModel())).'-'.date('Ymd-His').'.csv"',
                ];

                return response()->streamDownload(function () use ($query, $columns) {
                    $out = fopen('php://output', 'w');
                    fwrite($out, "\xEF\xBB\xBF");
                    fputcsv($out, $columns->map(fn ($c) => $c->getLabel() ?? $c->getName())->all());

                    $query->chunk(500, function ($rows) use ($out, $columns) {
                        foreach ($rows as $row) {
                            fputcsv($out, $columns->map(function ($column) use ($row) {
                                $value = data_get($row, $column->getName());

                                return is_object($value) && method_exists($value, 'toJson')
                                    ? $value->toJson()
                                    : (string) $value;
                            })->all());
                        }
                    });

                    fclose($out);
                }, 'export.csv', $headers);
            });
    }

    /**
     * Bulk actions (batch delete), honoring the tools()->batch() flags.
     *
     * @return array
     */
    protected function buildBulkActions(): array
    {
        if ($this->isBatchDeleteDisabled()) {
            return [];
        }

        return [
            DeleteBulkAction::make(),
        ];
    }
}
