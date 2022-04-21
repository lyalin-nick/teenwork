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
 * @property int $pos
 *
 * @property Task[] $tasks
 * @property Category $parent
 * @property Category[] $children
 * @property Profile[] $profiles
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


    const
        ICON_1 = 'icon-1',
        ICON_2 = 'icon-2',
        ICON_3 = 'icon-3',
        ICON_4 = 'icon-4';

    protected $fillable = ['name', 'category_id', 'icon_name', 'flag', 'pos'];


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
        $models = self::where('category_id', '=',0)
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

    /**
     * Получить массив ID флагов
     *
     * @param $flag
     * @return int[]
     */
    protected static function getFlagsConstants($flag)
    {
        $flags = [
            'online' => [self::FLAG_ONLINE, self::FLAG_OFFLINE_ONLINE],
            'offline' => [self::FLAG_OFFLINE, self::FLAG_OFFLINE_ONLINE]
        ];

        return $flags[$flag];
    }

    protected static function getIcons()
    {
        return [
            self::ICON_1 => 'Category 1',
            self::ICON_2 => 'Category 2',
            self::ICON_3 => 'Category 3',
            self::ICON_4 => 'Category 4',
        ];
    }

    protected static function booted()
    {
        static::updated(function (self $category) {
            if ($category->children) {
                foreach ($category->children as $child)
                    $child->update(['icon_name' => $category->icon_name]);
            } elseif ($category->parent) {
                $category->update(['icon_name' => $category->parent->icon_name]);
            }
        });
        static::created(function (self $category) {
            if ($category->parent) {
                $category->update(['icon_name' => $category->parent->icon_name]);
            }
        });

        static::deleted(function (self $category) {
            $category->profiles()->detach();//убираем прилинкованные категории к профилю

            if ($category->children)
                foreach ($category->children as $child)
                    $child->delete();

        });
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class);
    }

    /**
     * Получить название флага
     *
     * @return string
     */
    public function getFlagLabel(): string
    {
        $flags = self::getFlags();
        return $flags[$this->flag];
    }

    /**
     * Получение флагов категорий
     *
     * @return string[]
     */
    public static function getFlags(): array
    {
        return [
            self::FLAG_OFFLINE => 'Offline',
            self::FLAG_ONLINE => 'Online',
            self::FLAG_OFFLINE_ONLINE => 'Offline and Online',
        ];
    }

    /**
     * Получение пути одной категории (Категория, Подкатегория)
     *
     * @return string
     */
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

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

}
