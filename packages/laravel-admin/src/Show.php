<?php

namespace Encore\Admin;

use Closure;
use Encore\Admin\Show\Field;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Class Show.
 *
 * The laravel-admin show (detail) DSL, bridged onto a Filament infolist
 * schema by {@see Show::applyToSchema()}.
 */
class Show
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
     * Panel title.
     *
     * @var string|null
     */
    protected ?string $panelTitle = null;

    /**
     * Show constructor.
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

        if ($callback instanceof Closure) {
            $callback($this);
        }
    }

    /**
     * The record being displayed.
     *
     * @return Model
     */
    public function model(): Model
    {
        return $this->model;
    }

    /**
     * Add a field.
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return Field
     */
    public function field(string $name, ?string $label = null): Field
    {
        return $this->fields[] = new Field($name, $label);
    }

    /**
     * Set the panel title.
     *
     * @param string|null $title
     *
     * @return $this
     */
    public function title(?string $title = null): static
    {
        $this->panelTitle = $title;

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Build the Filament infolist schema from this show.
     *
     * @param Schema $schema
     *
     * @return Schema
     */
    public function applyToSchema(Schema $schema): Schema
    {
        $record = $schema->getRecord();

        $entries = [];

        foreach ($this->fields as $field) {
            $entry = TextEntry::make($field->getName())
                ->label($field->getLabel() ?? $field->getName())
                ->state($record?->{$field->getName()})
                ->placeholder('-');

            if ($callback = $field->getCallback()) {
                $bound = $record
                    ? \Closure::bind($callback, $record, $record::class)
                    : $callback;

                $entry->formatStateUsing(fn ($state) => $bound($state));
            }

            if ($field->isLabel()) {
                $entry->badge();
            }

            if ($field->isUnescape()) {
                $entry->html();
            }

            $entries[] = $entry;
        }

        return $schema->components($entries);
    }
}
