<?php

namespace Encore\Admin\Widgets;

/**
 * Class Form.
 *
 * A standalone form widget (original usage: the right-hand create form on
 * the menu page). In the port its fields can be converted into Filament
 * schema components for the containing page via toFilamentComponents().
 */
class Form extends Widget
{
    /**
     * @var string
     */
    protected string $action;

    /**
     * @var string
     */
    protected string $method = 'POST';

    /**
     * @var array
     */
    protected array $fields = [];

    /**
     * Form constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function action(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Add a field definition.
     *
     * @param string $type
     * @param string $name
     * @param string|null $label
     *
     * @return array{type: string, name: string, label: ?string, field: \Encore\Admin\Form\Field}
     */
    public function addField(string $type, string $name, ?string $label = null): array
    {
        $field = new \Encore\Admin\Form\Field($type, $name, $label);

        $this->fields[] = ['type' => $type, 'name' => $name, 'label' => $label, 'field' => $field];

        return end($this->fields);
    }

    /**
     * @param string $name
     * @param string|null $label
     *
     * @return array
     */
    public function select(string $name, ?string $label = null): array
    {
        return $this->addField(\Encore\Admin\Form\Field::TYPE_SELECT, $name, $label);
    }

    /**
     * @param string $name
     * @param string|null $label
     *
     * @return array
     */
    public function text(string $name, ?string $label = null): array
    {
        return $this->addField(\Encore\Admin\Form\Field::TYPE_TEXT, $name, $label);
    }

    /**
     * @param string $name
     * @param string|null $label
     *
     * @return array
     */
    public function icon(string $name, ?string $label = null): array
    {
        return $this->addField(\Encore\Admin\Form\Field::TYPE_ICON, $name, $label);
    }

    /**
     * @param string $name
     * @param string|null $label
     *
     * @return array
     */
    public function multipleSelect(string $name, ?string $label = null): array
    {
        return $this->addField(\Encore\Admin\Form\Field::TYPE_MULTIPLE_SELECT, $name, $label);
    }

    /**
     * @param string $name
     * @param string|null $label
     *
     * @return array
     */
    public function hidden(string $name, ?string $label = null): array
    {
        return $this->addField(\Encore\Admin\Form\Field::TYPE_HIDDEN, $name, $label);
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.form', ['form' => $this])->render();
    }
}
