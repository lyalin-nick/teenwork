<?php

namespace App\Models\ImageFilters;

use Intervention\Image\Filters\FilterInterface;

class MiniatureFilter implements FilterInterface
{
    /**
     * Size of filter effects
     *
     * @var integer
     */
    private $width;
    private $height;

    /**
     * Creates new instance of filter
     *
     * @param integer $size
     */
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Applies filter effects to given image
     *
     * @param Intervention\Image\Image $img
     * @return Intervention\Image\Image
     */
    public function applyFilter(\Intervention\Image\Image $img)
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
