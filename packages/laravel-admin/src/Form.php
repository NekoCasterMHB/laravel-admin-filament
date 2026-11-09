<?php

namespace Encore\Admin;

use Closure;
use Encore\Admin\Form\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Class Form.
 *
 * The laravel-admin form DSL, bridged onto a Filament schema by
 * {@see Form::applyToSchema()} and onto the save pipeline by
 * {@see Form::process()} / {@see Form::store()} / {@see Form::update()}.
 */
class Form
{
    /**
     * Model instance.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Fields.
     *
     * @var Field[]
     */
    protected array $fields = [];

    /**
     * Columns removed before saving.
     *
     * @var array
     */
    protected array $ignored = [];

    /**
     * Tab groups (original `$form->tab()`).
     *
     * @var array
     */
    protected array $tabs = [];

    /**
     * Current tab index (-1 = no tabs).
     *
     * @var int
     */
    protected int $currentTab = -1;

    /**
     * saving callbacks.
     *
     * @var Closure[]
     */
    protected array $savingCallbacks = [];

    /**
     * saved callbacks.
     *
     * @var Closure[]
     */
    protected array $savedCallbacks = [];

    /**
     * Current data being processed (accessible from hooks as properties).
     *
     * @var array
     */
    protected array $data = [];

    /**
     * The record being saved (accessible from hooks via model()).
     *
     * @var Model|null
     */
    protected ?Model $currentModel = null;

    /**
     * Field names removed via forget() (original bootstrap API).
     *
     * @var array
     */
    public static array $forgotted = [];

    /**
     * Remove builtin fields (original `Form::forget(['map', 'editor'])`).
     *
     * @param string|array $field
     *
     * @return void
     */
    public static function forget(string|array $field): void
    {
        static::$forgotted = array_merge(static::$forgotted, (array) $field);
    }

    /**
     * Extend a custom field (original `Form::extend('name', FieldClass)`);
     * full custom-field resolution lands with the M3 breadth pass.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend(string $name, string $class): void
    {
        static::$customFields[$name] = $class;
    }

    /**
     * @var array
     */
    public static array $customFields = [];

    /**
     * Form constructor.
     *
     * @param Model|string $model
     * @param Closure|null $callback
     */
    public function __construct(Model|string $model, ?Closure $callback = null)
    {
        if (is_string($model)) {
            $model = new $model();
        }

        if (!$model instanceof Model) {
            throw new InvalidArgumentException('$model must be an Eloquent model');
        }

        $this->model = $model;
    }

    /**
     * The model being edited (usable inside saving hooks).
     *
     * @return Model
     */
    public function model(): Model
    {
        return $this->currentModel ?? $this->model;
    }

    /**
     * Get/set data from saving hooks (original `$form->password` access).
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a key from the input (original `$form->deleteInput()`).
     *
     * @param string|array $key
     */
    public function deleteInput(string|array $key): void
    {
        Arr::forget($this->data, $key);
    }

    /**
     * Columns removed before saving.
     *
     * @param string|array $columns
     *
     * @return $this
     */
    public function ignore(string|array $columns): static
    {
        $this->ignored = array_merge($this->ignored, (array) $columns);

        return $this;
    }

    /**
     * Add a display (read-only) field.
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function display(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_DISPLAY, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function text(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TEXT, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function email(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_EMAIL, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function password(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_PASSWORD, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function image(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_IMAGE, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function file(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_FILE, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function select(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_SELECT, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function multipleSelect(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_MULTIPLE_SELECT, $name, $label);
    }

    /**
     * Port note: the original dual-listbox renders as a searchable multiple
     * select (same selection semantics).
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function listbox(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_LISTBOX, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function textarea(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TEXTAREA, $name, $label);
    }

    /**
     * FontAwesome icon picker.
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function icon(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_ICON, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function hidden(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_HIDDEN, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function tags(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TAGS, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function markdown(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_MARKDOWN, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function url(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_URL, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function ip(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_IP, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function mobile(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_MOBILE, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function number(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_NUMBER, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function currency(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_CURRENCY, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function decimal(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_DECIMAL, $name, $label);
    }

    /**
     * Switch field (Toggle).
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function switchField(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_SWITCH, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function radio(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_RADIO, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function checkbox(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_CHECKBOX, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function color(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_COLOR, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function time(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TIME, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function year(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_YEAR, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function month(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_MONTH, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function timezone(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TIMEZONE, $name, $label);
    }

    /**
     * Star rating — bridged to a Slider (min 0 / max 5 / step 1).
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function rate(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_RATE, $name, $label)
            ->min(0)->max(5)->step(1);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function slider(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_SLIDER, $name, $label);
    }

    /**
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function keyValue(string $name, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_KEYVALUE, $name, $label);
    }

    /**
     * Has-many nested rows (bridged to a Filament Repeater; relation-aware).
     *
     * @param string  $name
     * @param string|null $label
     * @param Closure $callback function (Nested $form) {}
     *
     * @return Field
     */
    public function hasmany(string $name, ?string $label, Closure $callback): Field
    {
        $nested = new Form\Nested();

        $callback($nested);

        return $this->addField(Field::TYPE_HASMANY, $name, $label)
            ->nestedFields($nested->fields());
    }

    /**
     * Alias of hasmany() with a table-style grid (original `table` field).
     *
     * @param string  $name
     * @param string|null $label
     * @param Closure $callback
     *
     * @return Field
     */
    public function table(string $name, ?string $label, Closure $callback): Field
    {
        return $this->hasmany($name, $label, $callback);
    }

    /**
     * Embedded fields group (original `embeds`).
     *
     * @param string  $column
     * @param string|null $label
     * @param Closure $callback
     *
     * @return Field
     */
    public function embeds(string $column, ?string $label, Closure $callback): Field
    {
        $nested = new Form\Nested();

        $callback($nested);

        return $this->addField(Field::TYPE_EMBEDS, $column, $label)
            ->nestedFields($nested->fields());
    }

    /**
     * Static HTML block.
     *
     * @param string $html
     * @param string|null $label
     *
     * @return Field
     */
    public function html(string $html, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_HTML, 'html'.uniqid(), $label)
            ->content($html);
    }

    /**
     * Horizontal divider.
     *
     * @return Field
     */
    public function divider(): Field
    {
        return $this->addField(Field::TYPE_DIVIDER, 'divider'.uniqid());
    }

    /**
     * Start a tabbed group (original `$form->tab('Title', function ($form) {...})`).
     * Subsequent fields attach to the new tab until the next tab() call.
     *
     * @param string  $title
     * @param Closure|null $callback
     *
     * @return $this
     */
    public function tab(string $title, ?Closure $callback = null): static
    {
        $this->tabs[] = ['title' => $title, 'fields' => []];

        $this->currentTab = count($this->tabs) - 1;

        if ($callback instanceof Closure) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Register a saving callback.
     *
     * @param Closure $callback function (Form $form) {}
     *
     * @return $this
     */
    public function saving(Closure $callback): static
    {
        $this->savingCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a saved callback.
     *
     * @param Closure $callback function (Form $form) {}
     *
     * @return $this
     */
    public function saved(Closure $callback): static
    {
        $this->savedCallbacks[] = $callback;

        return $this;
    }

    /**
     * Add a field.
     *
     * @param string      $type
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    protected function addField(string $type, string $column, ?string $label = null): Field
    {
        $field = new Field($type, $column, $label);

        $this->fields[] = $field;

        // Inside a tab() group the field also joins that tab's field list.
        if ($this->currentTab >= 0 && isset($this->tabs[$this->currentTab])) {
            $this->tabs[$this->currentTab]['fields'][] = $field;
        }

        return $field;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    /**
     * @return array
     */
    public function getIgnored(): array
    {
        return $this->ignored;
    }

    /**
     * Build the Filament schema from this form.
     *
     * @param Schema     $schema
     * @param string     $operation create|edit
     * @param Model|null $record
     *
     * @return Schema
     */
    public function applyToSchema(Schema $schema, string $operation, ?Model $record = null): Schema
    {
        // Tabbed layout (original `$form->tab(...)` groups).
        if ($this->tabs !== []) {
            $tabs = [];

            foreach ($this->tabs as $tab) {
                $tabs[] = \Filament\Forms\Components\Tabs\Tab::make($tab['title'])
                    ->schema($this->buildComponents($tab['fields'], $operation, $record));
            }

            return $schema->components([
                \Filament\Forms\Components\Tabs::make()->tabs($tabs)->persistTab(),
            ]);
        }

        return $schema->components($this->buildComponents($this->fields, $operation, $record));
    }

    /**
     * Build Filament components for a list of DSL fields.
     *
     * @param iterable   $fields
     * @param string     $operation
     * @param Model|null $record
     *
     * @return array
     */
    protected function buildComponents(iterable $fields, string $operation, ?Model $record): array
    {
        $components = [];

        foreach ($fields as $field) {
            $components[] = $this->buildComponent($field, $operation, $record);
        }

        return $components;
    }

    /**
     * Translate a DSL field into a Filament component.
     *
     * @param Field      $field
     * @param string     $operation
     * @param Model|null $record
     *
     * @return mixed
     */
    protected function buildComponent(Field $field, string $operation, ?Model $record): mixed
    {
        $column = $field->getColumn();

        if ($field->getType() === Field::TYPE_DISPLAY) {
            // No eager state(): the entry reads the record supplied by the
            // page or action, so it also works inside modals where the
            // record is only known at render time.
            return TextEntry::make($column)
                ->label($field->getLabel() ?? $column)
                ->placeholder('-')
                ->copyable($column === 'id');
        }

        $component = match ($field->getType()) {
            Field::TYPE_PASSWORD => TextInput::make($column)->password()->revealable(),
            Field::TYPE_EMAIL => TextInput::make($column)->email(),
            Field::TYPE_URL => TextInput::make($column)->url(),
            Field::TYPE_IP => TextInput::make($column)->rules(['ip']),
            Field::TYPE_MOBILE => TextInput::make($column)->tel(),
            Field::TYPE_IMAGE => $this->buildUploadField($field, true),
            Field::TYPE_FILE => $this->buildUploadField($field, false),
            Field::TYPE_SELECT => $this->buildSelectField($field, false),
            Field::TYPE_MULTIPLE_SELECT, Field::TYPE_LISTBOX => $this->buildSelectField($field, true),
            Field::TYPE_TEXTAREA => Textarea::make($column)->rows($field->getRows()),
            Field::TYPE_ICON => TextInput::make($column)->suffixIcon('fas-bars'),
            Field::TYPE_HIDDEN => Hidden::make($column),
            Field::TYPE_TAGS => \Filament\Forms\Components\TagsInput::make($column),
            Field::TYPE_EDITOR => \Filament\Forms\Components\RichEditor::make($column),
            Field::TYPE_MARKDOWN => \Filament\Forms\Components\MarkdownEditor::make($column),
            Field::TYPE_NUMBER => TextInput::make($column)->numeric()
                ->when($field->getMin() !== null, fn ($c) => $c->minValue($field->getMin()))
                ->when($field->getMax() !== null, fn ($c) => $c->maxValue($field->getMax())),
            Field::TYPE_DECIMAL, Field::TYPE_CURRENCY => TextInput::make($column)->numeric()
                ->step($field->getStep() ?? 0.01)
                ->prefix(Field::TYPE_CURRENCY === $field->getType() ? '$' : null),
            Field::TYPE_SWITCH => \Filament\Forms\Components\Toggle::make($column),
            Field::TYPE_RADIO => \Filament\Forms\Components\Radio::make($column)->options($this->decodeOptions($field->getOptions())),
            Field::TYPE_CHECKBOX => \Filament\Forms\Components\CheckboxList::make($column)->options($this->decodeOptions($field->getOptions())),
            Field::TYPE_COLOR => \Filament\Forms\Components\ColorPicker::make($column),
            Field::TYPE_DATE => \Filament\Forms\Components\DatePicker::make($column),
            Field::TYPE_DATETIME => \Filament\Forms\Components\DateTimePicker::make($column),
            Field::TYPE_TIME => \Filament\Forms\Components\TimePicker::make($column),
            Field::TYPE_YEAR => Select::make($column)->options($this->yearOptions()),
            Field::TYPE_MONTH => Select::make($column)->options(array_combine(range(1, 12), [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
            ])),
            Field::TYPE_TIMEZONE => Select::make($column)->options(timezone_identifiers_list()),
            Field::TYPE_RATE => \Filament\Forms\Components\Slider::make($column)
                ->minValue($field->getMin() ?? 0)
                ->maxValue($field->getMax() ?? 5)
                ->when($field->getStep() !== null, fn ($c) => $c->step($field->getStep())),
            Field::TYPE_SLIDER => \Filament\Forms\Components\Slider::make($column)
                ->when($field->getMin() !== null, fn ($c) => $c->minValue($field->getMin()))
                ->when($field->getMax() !== null, fn ($c) => $c->maxValue($field->getMax()))
                ->when($field->getStep() !== null, fn ($c) => $c->step($field->getStep())),
            Field::TYPE_KEYVALUE => \Filament\Forms\Components\KeyValue::make($column),
            Field::TYPE_HASMANY, Field::TYPE_TABLE => $this->buildHasManyField($field),
            Field::TYPE_EMBEDS => \Filament\Schemas\Components\Group::make()
                ->schema($this->buildComponents($field->getNestedFields(), $operation, $record)),
            Field::TYPE_HTML => \Filament\Schemas\Components\Html::make(
                new \Illuminate\Support\HtmlString($field->getHtmlContent() ?? ''),
            ),
            Field::TYPE_DIVIDER => \Filament\Forms\Components\Divider::make($field->getLabel()),
            default => TextInput::make($column),
        };

        $component->label($field->getLabel() ?? $column);

        if ($placeholder = $field->getPlaceholder()) {
            if (method_exists($component, 'placeholder')) {
                $component->placeholder($placeholder);
            }
        }

        if ($help = $field->getHelp()) {
            $component->helperText(new \Illuminate\Support\HtmlString($help));
        }

        if ($field->getDefaultCallback() !== null) {
            $component->default(fn () => ($field->getDefaultCallback())($this->forRecord($record)));
        } elseif ($field->getDefault() !== null) {
            $component->default($field->getDefault());
        }

        $rules = $this->rulesForOperation($field, $operation, $record);

        if ($rules !== []) {
            $component->rules($rules);
        }

        return $component;
    }

    /**
     * @param Field $field
     * @param bool  $image
     *
     * @return FileUpload
     */
    protected function buildUploadField(Field $field, bool $image): FileUpload
    {
        $upload = FileUpload::make($field->getColumn())
            ->disk(config('admin.upload.disk'))
            ->directory(config('admin.upload.directory.'.($image ? 'image' : 'file')));

        if ($image) {
            $upload->image();
        }

        return $upload;
    }

    /**
     * Has-many rows (bridged to a Filament Repeater, relation-aware).
     *
     * @param Field $field
     *
     * @return \Filament\Forms\Components\Repeater
     */
    protected function buildHasManyField(Field $field): \Filament\Forms\Components\Repeater
    {
        $column = $field->getColumn();
        $isRelation = $this->model->isRelation($column);

        $repeater = \Filament\Forms\Components\Repeater::make($column)
            ->label($field->getLabel() ?? $column)
            ->schema($this->buildComponents($field->getNestedFields(), 'create', null));

        if ($isRelation) {
            $repeater->relationship($column);
        }

        return $repeater;
    }

    /**
     * Option labels may carry HTML entities from the original DSL
     * (e.g. ModelTree::selectOptions() indents with &nbsp; and ┝). Filament
     * renders labels as plain text, so decode them before handing over.
     *
     * @param array $options
     *
     * @return array
     */
    protected function decodeOptions(array $options): array
    {
        return collect($options)
            ->map(fn ($label) => is_string($label)
                ? html_entity_decode($label, ENT_QUOTES | ENT_HTML5, "UTF-8")
                : $label)
            ->all();
    }

    /**
     * Year options (original Year field select list).
     *
     * @return array
     */
    protected function yearOptions(): array
    {
        $start = (int) date('Y') - 20;

        return array_combine(range($start, $start + 25), range($start, $start + 25));
    }

    /**
     * @param Field $field
     * @param bool  $multiple
     *
     * @return Select
     */
    protected function buildSelectField(Field $field, bool $multiple): Select
    {
        $column = $field->getColumn();
        $isRelation = $this->model->isRelation($column);

        $select = Select::make($column);

        if ($multiple) {
            $select->multiple();

            if ($field->getType() === Field::TYPE_LISTBOX) {
                $select->searchable();
            }
        }

        if ($isRelation) {
            // Filament handles hydration & syncing for relations. A
            // relationship select is "search-only" on the front end (its
            // dropdown starts empty until you type), so the full option list
            // is also supplied explicitly — the original select2 listed every
            // option on open and typed input filtered it.
            $related = $this->model->{$column}()->getRelated();

            $select->relationship($column, titleAttribute: 'name')
                ->options(function () use ($related): array {
                    return $related->newQuery()
                        ->pluck('name', $related->getKeyName())
                        ->all();
                })
                ->optionsLimit(1000);

            if ($multiple) {
                // Actions render their schema outside a resource form, so the
                // pivot state is not hydrated automatically — seed it from
                // the current record (the original form always showed the
                // checked roles).
                $select->default(fn ($record = null) => $record
                    ? $record->{$column}()->pluck($related->getKeyName())->all()
                    : []);
            }
        } else {
            $select->options($this->decodeOptions($field->getOptions()));
        }

        return $select;
    }

    /**
     * Operation-aware validation rules with the original `{{id}}`
     * placeholder support.
     *
     * @param Field      $field
     * @param string     $operation
     * @param Model|null $record
     *
     * @return array
     */
    protected function rulesForOperation(Field $field, string $operation, ?Model $record): array
    {
        $rules = $operation === 'create'
            ? [...$field->getRules(), ...$field->getCreationRules()]
            : [...$field->getRules(), ...$field->getUpdateRules()];

        return array_map(function ($rule) use ($record) {
            if (is_string($rule) && $record) {
                return str_replace('{{id}}', $record->getKey(), $rule);
            }

            return $rule;
        }, $rules);
    }

    /**
     * Provide a Form-like object for default callbacks.
     *
     * @param Model|null $record
     *
     * @return Form
     */
    protected function forRecord(?Model $record): Form
    {
        $this->currentModel = $record ?? $this->model;

        return $this;
    }

    /**
     * Process validated data through the original pipeline: defaults,
     * ignored columns, saving hooks.
     *
     * @param array      $data
     * @param Model|null $record
     *
     * @return array
     */
    public function process(array $data, ?Model $record = null): array
    {
        $this->data = $data;
        $this->currentModel = $record ?? $this->model;

        foreach ($this->fields as $field) {
            $column = $field->getColumn();

            if (!isset($this->data[$column]) && $field->getDefaultCallback() !== null) {
                $this->data[$column] = ($field->getDefaultCallback())($this);
            }
        }

        foreach ($this->ignored as $ignored) {
            $this->deleteInput($ignored);
        }

        foreach ($this->savingCallbacks as $callback) {
            $callback($this);
        }

        return $this->data;
    }

    /**
     * Create a record.
     *
     * @param array $data
     *
     * @return Model
     */
    public function store(array $data): Model
    {
        $data = $this->process($data);

        $model = $this->model;
        $model->fill($data)->save();

        $this->currentModel = $model;

        foreach ($this->savedCallbacks as $callback) {
            $callback($this);
        }

        return $model;
    }

    /**
     * Update a record.
     *
     * @param array $data
     * @param mixed $id
     *
     * @return Model
     */
    public function update(array $data, mixed $id): Model
    {
        $record = $this->model->newQuery()->findOrFail($id);

        $data = $this->process($data, $record);

        $record->fill($data)->save();

        $this->currentModel = $record;

        foreach ($this->savedCallbacks as $callback) {
            $callback($this);
        }

        return $record;
    }

    /**
     * Delete a record (accepts an id or comma-separated ids, original
     * contract). The models' deleting hooks fire as usual.
     *
     * @param mixed $id
     *
     * @return bool
     */
    public function destroy(mixed $id): bool
    {
        $ids = explode(',', (string) $id);

        return (bool) $this->model->newQuery()->whereIn(
            $this->model->getKeyName(),
            array_filter($ids),
        )->get()->each->delete();
    }
}
