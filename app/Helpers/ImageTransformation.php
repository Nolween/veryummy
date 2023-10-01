<?php

namespace App\Helpers;

use Exception;
use GdImage;

class ImageTransformation
{
    /**
     * Resizing the full recipe image into thumbnail.
     *
     * @param  GdImage  $source
     */
    public static function image_resize($source, int $width, int $height): GdImage
    {
        $new_width  = $width   > $height ? 240 : 240 * ($width / $height);
        $new_height = $height > $width ? 240 : 240   * ($height / $width);
        $thumbImg   = imagecreatetruecolor($new_width, $new_height);
        if ($thumbImg === false) {
            throw new Exception('Failed to create image');
        }
        imagecopyresampled($thumbImg, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        return $thumbImg;
    }
}
