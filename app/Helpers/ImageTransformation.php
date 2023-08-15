<?php

namespace App\Helpers;

use GdImage;

class ImageTransformation
{
    /**
     * Resizing the full recipe image into thumbnail.
     * @param GdImage $source
     * @param int $width
     * @param int $height
     * @return GdImage
     */
    public static function image_resize($source, int $width, int $height): GdImage
    {
        $new_width = $width > $height ? 240 : 240 * ($width / $height);
        $new_height = $height > $width ? 240 : 240 * ($height / $width);
        $thumbImg = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($thumbImg, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        return $thumbImg;
    }
}
