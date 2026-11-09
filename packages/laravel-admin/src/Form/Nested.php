<?php

namespace Encore\Admin\Form;

use Encore\Admin\Form;

/**
 * Class Nested.
 *
 * Mini field builder passed to the hasmany()/table()/embeds() closures —
 * the same field API as Form, collecting Field[] for the nested schema.
 */
class Nested
{
    /**
     * @var Field[]
     */
    protected array $fields = [];

    /**
     * @param string      $type
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function addField(string $type, string $column, ?string $label = null): Field
    {
        return $this->fields[] = new Field($type, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function text(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TEXT, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function email(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_EMAIL, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function select(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_SELECT, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function multipleSelect(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_MULTIPLE_SELECT, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function textarea(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_TEXTAREA, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function image(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_IMAGE, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function file(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_FILE, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function date(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_DATE, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function datetime(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_DATETIME, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function switchField(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_SWITCH, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function number(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_NUMBER, $column, $label);
    }

    /**
     * @param string      $column
     * @param string|null $label
     *
     * @return Field
     */
    public function currency(string $column, ?string $label = null): Field
    {
        return $this->addField(Field::TYPE_CURRENCY, $column, $label);
    }

    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
