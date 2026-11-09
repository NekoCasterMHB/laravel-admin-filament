<?php

namespace Encore\Admin\Support;

/**
 * Curated Font Awesome icon names available to the icon picker.
 *
 * Names are verified against the installed blade-fontawesome SVG set, so the
 * picker never offers an icon that cannot render.
 */
class IconCatalog
{
    /**
     * Common admin icons first (shown by default), then a wider selection.
     *
     * @var array<int, string>
     */
    protected const CURATED = [
        // navigation / layout
        'house', 'bars', 'list', 'table', 'table-cells-large', 'table-list',
        'chart-column', 'chart-line', 'chart-area', 'chart-pie', 'gauge-high',
        'sitemap', 'diagram-project', 'layer-group', 'cubes', 'cube', 'box',
        // people / access
        'user', 'users', 'user-group', 'user-gear', 'users-gear', 'user-tie',
        'id-card', 'address-book', 'key', 'lock', 'unlock', 'shield',
        'shield-halved', 'fingerprint', 'ban', 'circle-check', 'circle-xmark',
        // content
        'file', 'file-lines', 'file-invoice', 'folder', 'folder-open',
        'archive', 'inbox', 'image', 'camera', 'video', 'film', 'music',
        'book', 'book-open', 'newspaper', 'pen', 'pen-to-square', 'edit' ,
        'trash', 'trash-can', 'copy', 'clipboard', 'clipboard-list',
        'paperclip', 'thumbtack', 'tags', 'tag', 'bookmark', 'link',
        'share', 'download', 'upload', 'print', 'eye', 'magnifying-glass',
        // commerce
        'cart-shopping', 'shop', 'store', 'money-bill', 'money-bill-wave',
        'coins', 'credit-card', 'receipt', 'wallet', 'gift', 'ticket',
        'tags', 'percent', 'truck', 'box-open', 'warehouse', 'barcode',
        // communication
        'envelope', 'envelope-open', 'bell', 'comment', 'comments',
        'paper-plane', 'phone', 'mobile', 'headset', 'rss', 'podcast',
        // system
        'gear', 'gears', 'sliders', 'toggle-on', 'toggle-off', 'wrench',
        'screwdriver-wrench', 'screwdriver', 'hammer', 'code', 'terminal',
        'database', 'server', 'memory', 'microchip', 'hard-drive', 'plug',
        'network-wired', 'wifi', 'signal', 'cloud', 'download', 'upload',
        'bug', 'flask', 'vial', 'microscope', 'radiation', 'biohazard',
        'recycle', 'shield-virus', 'virus', 'bacteria',
        // time / calendar
        'calendar', 'calendar-days', 'calendar-check', 'clock',
        'clock-rotate-left', 'hourglass-half', 'stopwatch', 'timer',
        // places / transport
        'globe', 'map', 'map-location-dot', 'location-dot', 'compass',
        'city', 'building', 'hospital', 'school', 'graduation-cap',
        'plane', 'train', 'ship', 'car', 'bus', 'bike', 'taxi', 'rocket',
        // status / misc
        'circle-info', 'circle-question', 'circle-exclamation', 'triangle-exclamation',
        'flag', 'star', 'heart', 'thumbs-up', 'hand', 'handshake',
        'award', 'certificate', 'medal', 'trophy', 'crown', 'fire',
        'bolt', 'sun', 'moon', 'snowflake', 'leaf', 'seedling', 'tree',
        'palette', 'paintbrush', 'wand-magic-sparkles', 'puzzle-piece',
        'gamepad', 'dice', 'dice-d6', 'chess', 'font', 'heading',
        'paragraph', 'quote-left', 'align-left', 'indent', 'outdent',
        'arrows-rotate', 'rotate', 'right-left', 'shuffle', 'sort',
        'arrow-right', 'arrow-up', 'arrow-down', 'angle-right',
        'chevron-right', 'caret-right', 'plus', 'minus', 'xmark', 'check',
    ];

    /**
     * Valid icon names (without the `fas-` prefix), cached per request.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        static $icons = null;

        if ($icons !== null) {
            return $icons;
        }

        $directory = base_path('vendor/owenvoke/blade-fontawesome/resources/svg/solid');

        $available = is_dir($directory)
            ? array_map(fn ($file) => basename($file, '.svg'), glob($directory.'/*.svg') ?: [])
            : [];

        $icons = array_values(array_unique(array_intersect(static::CURATED, $available)));

        return $icons;
    }
}
