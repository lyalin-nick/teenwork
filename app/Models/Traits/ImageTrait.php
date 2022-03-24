<?php

namespace App\Models\Traits;

use App\Models\ImageFilters\MiniatureFilter;
use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ImageTrait
{

    /**
     * Копирование загруженной фотографии и сохранение данных в модель
     * @param $temp_path
     * @param $parent_id
     * @return bool
     */
    public function copyImage($temp_path, $parent_id)
    {
        $img_path = $this->createPath($parent_id);

        $created = $this->copyToNewPath($temp_path, $img_path);

        if ($created) {
            $path_info = pathinfo(Storage::disk('public')->path($created));

            $this->path = $img_path;
            $this->name = $path_info['filename'];
            $this->ext = $path_info['extension'];

            return $this->save() && $this->createMiniature($this->path, $this->name, $this->ext);
        }
        return false;
    }

    /**
     * Получение пути для сохранения фото
     * @param null $parent_id
     * @return string
     */
    public function createPath($parent_id = null): string
    {
        $file_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $file_path .= $parent_id . DIRECTORY_SEPARATOR;

        return $file_path;
    }

    /**
     * Копирование фото
     * @param $temp_img_path
     * @param $img_path
     * @return false|string
     */
    protected function copyToNewPath($temp_img_path, $img_path)
    {
        try {
            $new_path = Storage::disk('public')->putFile($img_path, new File(Storage::disk('public')->path($temp_img_path)));

            $this->checkExistImageAndDelete();

            return $new_path;
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    /**
     * Проверка и удаление фото и его миниатюр
     */
    public function checkExistImageAndDelete(): void
    {
        if ($this->hasImage()) {
            $this->deleteImage();
            $this->deleteResizedImages();
        }
    }

    /**
     * Проверка существования фото
     * @return bool
     */
    public function hasImage(): bool
    {
        $profile_photo_path = $this->getFullPath();

        return !empty($profile_photo_path) && is_file(Storage::disk('public')->path($profile_photo_path));
    }

    /**
     * Получение текущего пути
     * @param string $suffix
     * @return string
     */
    public function getFullPath($suffix = ''): string
    {
        return $this->path . $this->name . "{$suffix}." . $this->ext;
    }

    /**
     * Удаление фото из файловой системы
     * @return bool
     */
    public function deleteImage(): bool
    {
        return Storage::disk('public')->delete($this->getFullPath());
    }

    /**
     * Удаление миниатюр
     */
    public function deleteResizedImages(): void
    {
        if ($this->configImages) {
            foreach ($this->configImages as $suffix => $params) {
                $full_path = $this->getFullPath($suffix);
                if (is_file(Storage::disk('public')->path($full_path)))
                    Storage::disk('public')->delete($full_path);
            }
        }
    }

    /**
     * Создание миниатюр
     * @param $path
     * @param $name
     * @param $ext
     * @return bool
     */
    public function createMiniature($path, $name, $ext): bool
    {
        try {
            $image_path = $path . $name . '.' . $ext;

            foreach ($this->configImages as $suffix => $config) {
                $img = Image::make(Storage::disk('public')->path($image_path));

                $img->filter(new MiniatureFilter($config['width'], $config['height']));

                $img->save(Storage::disk('public')->path($path . $name . $suffix . '.' . $ext));
            }

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    /**
     * Загрузка фото и сохранение данных в модель
     * @param $image
     * @param $parent_id
     * @return bool
     */
    public function uploadImage($image, $parent_id)
    {
        $img_path = $this->createPath($parent_id);

        $created = $this->createImage($image, $img_path);

        if ($created) {
            $path_info = pathinfo(Storage::disk('public')->path($created));

            $this->path = $img_path;
            $this->name = $path_info['filename'];
            $this->ext = $path_info['extension'];

            return $this->save() && $this->createMiniature($this->path, $this->name, $this->ext);
        }
        return false;
    }

    /**
     * @param $image
     * @param $img_path
     * @return false|string
     */
    protected function createImage($image, $img_path)
    {
        try {
            $new_path = Storage::disk('public')->putFile($img_path, $image);

            $this->checkExistImageAndDelete();

            return $new_path;
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    /**
     * Получение ссылок на фото
     * @param null $suffix
     * @return string
     */
    public function getImageLink($suffix = null): string
    {
        return asset(Storage::url($this->getFullPath($suffix)));
    }
}
