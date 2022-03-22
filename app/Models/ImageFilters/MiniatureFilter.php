<?php

namespace App\Models\ImageFilters;

use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class MiniatureFilter implements FilterInterface
{
    /**
     * Ширина картинки
     *
     * @var integer
     */
    private $width;

    /**
     * Высота картинки
     *
     * @var integer
     */
    private $height;

    /**
     * Конструктор задающий размеры фото
     *
     * @param integer $width
     * @param integer $height
     */
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Применение фильтра к фото
     *
     * @param Image $img Исходное фото
     * @return Image
     */
    public function applyFilter(Image $img): Image
    {
        $width = $img->width();
        $height = $img->height();

        $size = ($width > $height) ? $height : $width;

        $img->crop($size, $size);

        $img->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        return $img;
    }
}
