<?php

namespace Encore\Admin\Layout;

use Closure;

/**
 * Class Content.
 *
 * The original page layout builder. In the port the produced structure is
 * converted into Filament page schema components by
 * {@see Content::toFilamentComponents()} (Content/Row/Column semantics
 * preserved: title, description, rows of proportioned columns).
 */
class Content
{
    /**
     * Content title.
     *
     * @var string|null
     */
    protected ?string $title = null;

    /**
     * Content description (subheading).
     *
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * Page rows.
     *
     * @var Row[]
     */
    protected array $rows = [];

    /**
     * Content constructor.
     *
     * @param Closure|null $callback
     */
    public function __construct(?Closure $callback = null)
    {
        if ($callback instanceof Closure) {
            $callback($this);
        }
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Add a row: either fixed content or a callback configuring columns.
     *
     * @param mixed $content string|view|Closure(Row)
     *
     * @return $this
     */
    public function row($content): static
    {
        $this->rows[] = new Row($content);

        return $this;
    }

    /**
     * Alias of row() — body($grid) in the original single-column pages.
     *
     * @param mixed $content
     *
     * @return $this
     */
    public function body($content): static
    {
        return $this->row($content);
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Convert this layout into Filament schema components:
     * - rows become Grids (12-column, spans matching the original widths)
     * - column contents (strings/views/widgets) render to HTML
     *
     * @return array
     */
    public function toFilamentComponents(): array
    {
        $components = [];

        foreach ($this->rows as $row) {
            $columns = $row->getColumns();

            if (count($columns) === 1 && $columns[0]->getWidth() === 12) {
                // Single full-width column: append its contents directly.
                foreach ($columns[0]->getContents() as $content) {
                    $components[] = \Filament\Schemas\Components\Html::make(
                        static::renderContent($content),
                    );
                }

                continue;
            }

            $children = [];

            foreach ($columns as $column) {
                $columnComponents = [];

                foreach ($column->getContents() as $content) {
                    $columnComponents[] = \Filament\Schemas\Components\Html::make(
                        static::renderContent($content),
                    );
                }

                $children[] = \Filament\Schemas\Components\Group::make($columnComponents)
                    ->columnSpan((int) max(1, round($column->getWidth() / 12 * 12)));
            }

            $components[] = \Filament\Schemas\Components\Grid::make()
                ->columns(12)
                ->components($children);
        }

        return $components;
    }

    /**
     * Render a column content item (string|view|widget|Htmlable) to HTML.
     *
     * @param mixed $content
     *
     * @return string
     */
    public static function renderContent(mixed $content): string
    {
        if ($content instanceof \Encore\Admin\Widgets\Widget) {
            return $content->html();
        }

        if ($content instanceof \Illuminate\Contracts\Support\Htmlable) {
            return (string) $content->toHtml();
        }

        if ($content instanceof \Illuminate\Contracts\View\View) {
            return $content->render();
        }

        return (string) $content;
    }
}
