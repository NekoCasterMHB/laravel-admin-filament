<?php

namespace Encore\Admin\Auth\Database;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * Class Menu.
 *
 * @property int $id
 *
 * @method where($parent_id, $id)
 */
class Menu extends Model
{
    use DefaultDatetimeFormat;
    use ModelTree {
        ModelTree::boot as treeBoot;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri', 'permission'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.menu_table'));

        parent::__construct($attributes);
    }

    /**
     * A Menu belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_menu_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'menu_id', 'role_id');
    }

    /**
     * @return array
     */
    public function allNodes(): array
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $orderColumn = DB::connection($connection)->getQueryGrammar()->wrap($this->orderColumn);

        $byOrder = 'ROOT ASC,'.$orderColumn;

        $query = static::query();

        if (config('admin.check_menu_roles') !== false) {
            $query->with('roles');
        }

        return $query->selectRaw('*, '.$orderColumn.' ROOT')->orderByRaw($byOrder)->get()->toArray();
    }

    /**
     * determine if enable menu bind permission.
     *
     * @return bool
     */
    public function withPermission()
    {
        return (bool) config('admin.menu_bind_permission');
    }

    /**
     * Children ordered by the menu `order` column (the tree page renders
     * each level in this order).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, $this->parentColumn)
            ->with('children')
            ->orderBy($this->orderColumn);
    }

    /*
    |--------------------------------------------------------------------------
    | filament-tree contract (menu tree page)
    |--------------------------------------------------------------------------
    |
    | The port keeps the original ModelTree trait (used by saveOrder and the
    | DSL tree renderer). The tree PAGE component additionally expects the
    | small contract below — these delegate to the same columns/configuration,
    | with no schema or behaviour change.
    |
    */

    /**
     * @return string
     */
    public function determineParentColumnName(): string
    {
        return $this->parentColumn;
    }

    /**
     * @return string
     */
    public function determineOrderColumnName(): string
    {
        return $this->orderColumn;
    }

    /**
     * @return string
     */
    public function determineTitleColumnName(): string
    {
        return $this->titleColumn;
    }

    /**
     * @return string
     */
    public function determineIconColumnName(): string
    {
        return 'icon';
    }

    /**
     * Root nodes (parent_id = 0, per the original seed data).
     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return (int) $this->getAttributeValue($this->parentColumn) === static::defaultParentKey();
    }

    /**
     * The root parent key (`admin_menu.parent_id` default is 0).
     *
     * @return int
     */
    public static function defaultParentKey(): int
    {
        return (int) config('filament-tree.default_parent_id', 0);
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function boot()
    {
        static::treeBoot();

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }
}
