<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $slug
 * @property string $icon_name
 * @property int $flag
 * @property mixed children
 * @property mixed parent
 *
 * @mixin Builder
 */
class Category extends Model
{
    use HasFactory, Sluggable;

    const
        FLAG_OFFLINE = 1,
        FLAG_ONLINE = 2,
        FLAG_OFFLINE_ONLINE = 3;

    protected $fillable = ['name', 'category_id', 'icon_name', 'flag'];


    /**
     * Получение всего дерева категорий
     *
     * @return array
     */
    public static function getAllCategoriesAsArray(): array
    {
        $categories = [];
        $models = self::where('category_id', 0)
            ->with(['children'])
            ->get();

        foreach ($models as $model) {
            $category = [
                'id' => $model->id,
                'name' => $model->name,
                'icon_name' => $model->icon_name
            ];
            $subcategories = [];
            foreach ($model->children as $child) {
                $subcategories[] = [
                    'id' => $child->id,
                    'name' => $child->name
                ];
            }
            $category['categories'] = $subcategories;
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Получение категорий сгруппированных по флагу Online/Offline
     *
     * @return array
     */
    public static function getAllCategoriesAsArrayByFlag($flag): array
    {
        $categories = [];
        $flag_constants = self::getFlagsConstants($flag);
        $models = self::where('category_id', 0)
            ->with(['children' => function ($query) use ($flag_constants) {
                $query->whereIn('flag', $flag_constants);
            }])
            ->whereHas('children', function (Builder $query) use ($flag_constants) {
                $query->whereIn('flag', $flag_constants);
            })
            ->get();

        foreach ($models as $model) {
            $category = [
                'id' => $model->id,
                'name' => $model->name,
                'icon_name' => $model->icon_name
            ];
            $subcategories = [];
            foreach ($model->children as $child) {
                $subcategories[] = [
                    'id' => $child->id,
                    'name' => $child->name
                ];
            }
            $category['categories'] = $subcategories;
            $categories[] = $category;
        }

        return $categories;
    }

    protected static function getFlagsConstants($flag)
    {
        $flags = [
            'online' => [self::FLAG_ONLINE, self::FLAG_OFFLINE_ONLINE],
            'offline' => [self::FLAG_OFFLINE, self::FLAG_OFFLINE_ONLINE]
        ];

        return $flags[$flag];
    }

    public function getFlagLabel(): string
    {
        $flags = self::getFlags();
        return $flags[$this->flag];
    }

    public static function getFlags(): array
    {
        $flags = [
            self::FLAG_OFFLINE => 'Offline',
            self::FLAG_ONLINE => 'Online',
            self::FLAG_OFFLINE_ONLINE => 'Offline and Online',
        ];
        return $flags;
    }

    public function getFullPathName(): string
    {
        $name = $this->name;
        if ($this->parent) {
            $name = $this->parent->name . ', ' . $name;
        }

        return $name;
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function children()
    {
        return $this->hasMany(Category::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
