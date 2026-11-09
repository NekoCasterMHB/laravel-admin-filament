<?php

namespace Encore\Admin\Form;

use Closure;

/**
 * Class Field.
 *
 * A form field definition. The ported subset of the original 65 field
 * classes: one generic field object carrying the type and modifiers; the
 * Filament component is produced by Form::applyToSchema().
 */
class Field
{
    /**
     * Field types.
     */
    public const TYPE_DISPLAY = 'display';
    public const TYPE_TEXT = 'text';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
    public const TYPE_SELECT = 'select';
    public const TYPE_MULTIPLE_SELECT = 'multipleSelect';
    public const TYPE_LISTBOX = 'listbox';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_ICON = 'icon';
    public const TYPE_HIDDEN = 'hidden';
    public const TYPE_EMAIL = 'email';
    public const TYPE_URL = 'url';
    public const TYPE_IP = 'ip';
    public const TYPE_MOBILE = 'mobile';
    public const TYPE_NUMBER = 'number';
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_SWITCH = 'switch';
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_TAGS = 'tags';
    public const TYPE_EDITOR = 'editor';
    public const TYPE_MARKDOWN = 'markdown';
    public const TYPE_RADIO = 'radio';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_COLOR = 'color';
    public const TYPE_TIME = 'time';
    public const TYPE_YEAR = 'year';
    public const TYPE_MONTH = 'month';
    public const TYPE_TIMEZONE = 'timezone';
    public const TYPE_RATE = 'rate';
    public const TYPE_SLIDER = 'slider';
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_KEYVALUE = 'keyvalue';
    public const TYPE_HASMANY = 'hasmany';
    public const TYPE_TABLE = 'table';
    public const TYPE_EMBEDS = 'embeds';
    public const TYPE_HTML = 'html';
    public const TYPE_DIVIDER = 'divider';
    public const TYPE_DATE_RANGE = 'dateRange';
    public const TYPE_DATETIME_RANGE = 'datetimeRange';
    public const TYPE_TIME_RANGE = 'timeRange';

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $column;

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * Validation rules (applied on both create & update).
     *
     * @var array
     */
    protected array $rules = [];

    /**
     * @var array
     */
    protected array $creationRules = [];

    /**
     * @var array
     */
    protected array $updateRules = [];

    /**
     * @var mixed
     */
    protected mixed $default = null;

    /**
     * @var Closure|null
     */
    protected ?Closure $defaultCallback = null;

    /**
     * Options for select-like fields.
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Help text (rendered below the field).
     *
     * @var string|null
     */
    protected ?string $help = null;

    /**
     * Attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Numeric constraints (slider/number/rate).
     *
     * @var float|int|null
     */
    protected float|int|null $min = null;

    /**
     * @var float|int|null
     */
    protected float|int|null $max = null;

    /**
     * @var float|int|null
     */
    protected float|int|null $step = null;

    /**
     * Nested fields (hasmany/table/embeds).
     *
     * @var Field[]
     */
    protected array $nestedFields = [];

    /**
     * Static content (html field).
     *
     * @var string|null
     */
    protected ?string $htmlContent = null;

    /**
     * Placeholder.
     *
     * @var string|null
     */
    protected ?string $placeholder = null;

    /**
     * Rows (textarea).
     *
     * @var int
     */
    protected int $rows = 5;

    /**
     * Minimum/max constraints.
     *
     * @param float|int $min
     *
     * @return $this
     */
    public function min(float|int $min): static
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @param float|int $max
     *
     * @return $this
     */
    public function max(float|int $max): static
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @param float|int $step
     *
     * @return $this
     */
    public function step(float|int $step): static
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Placeholder.
     *
     * @param string $placeholder
     *
     * @return $this
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Rows for textarea.
     *
     * @param int $rows
     *
     * @return $this
     */
    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Static content for the html field.
     *
     * @param string $html
     *
     * @return $this
     */
    public function content(string $html): static
    {
        $this->htmlContent = $html;

        return $this;
    }

    /**
     * Nested fields for hasmany/table/embeds.
     *
     * @param Field[] $fields
     *
     * @return $this
     */
    public function nestedFields(array $fields): static
    {
        $this->nestedFields = $fields;

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getNestedFields(): array
    {
        return $this->nestedFields;
    }

    /**
     * @return float|int|null
     */
    public function getMin(): float|int|null
    {
        return $this->min;
    }

    /**
     * @return float|int|null
     */
    public function getMax(): float|int|null
    {
        return $this->max;
    }

    /**
     * @return float|int|null
     */
    public function getStep(): float|int|null
    {
        return $this->step;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * @return string|null
     */
    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }

    /**
     * Field constructor.
     *
     * @param string      $type
     * @param string      $column
     * @param string|null $label
     */
    public function __construct(string $type, string $column, ?string $label = null)
    {
        $this->type = $type;
        $this->column = $column;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Validation rules.
     *
     * @param array|string $rules
     *
     * @return $this
     */
    public function rules(array|string $rules): static
    {
        $this->rules = array_merge($this->rules, (array) $rules);

        return $this;
    }

    /**
     * @param array|string $rules
     *
     * @return $this
     */
    public function creationRules(array|string $rules): static
    {
        $this->creationRules = array_merge($this->creationRules, (array) $rules);

        return $this;
    }

    /**
     * @param array|string $rules
     *
     * @return $this
     */
    public function updateRules(array|string $rules): static
    {
        $this->updateRules = array_merge($this->updateRules, (array) $rules);

        return $this;
    }

    /**
     * Default value (static or callback receiving the Form).
     *
     * @param mixed $default
     *
     * @return $this
     */
    public function default(mixed $default): static
    {
        if ($default instanceof Closure) {
            $this->defaultCallback = $default;
        } else {
            $this->default = $default;
        }

        return $this;
    }

    /**
     * Options for select-like fields.
     *
     * @param array|\Illuminate\Support\Collection $options
     *
     * @return $this
     */
    public function options(array|\Illuminate\Support\Collection $options): static
    {
        $this->options = $options instanceof \Illuminate\Support\Collection
            ? $options->all()
            : $options;

        return $this;
    }

    /**
     * Help text below the field.
     *
     * @param string $help
     *
     * @return $this
     */
    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    /**
     * Arbitrary attribute for the bridge (e.g. readonly/required).
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return $this
     */
    public function attribute(string $attribute, mixed $value = true): static
    {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function getCreationRules(): array
    {
        return $this->creationRules;
    }

    /**
     * @return array
     */
    public function getUpdateRules(): array
    {
        return $this->updateRules;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @return Closure|null
     */
    public function getDefaultCallback(): ?Closure
    {
        return $this->defaultCallback;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }
}
