<?php

namespace App\Helpers;


class ImageTransformation
{

  /**
   * Resizing the full recipe image into thumbnail.
   */
  static function image_resize($source, int $width, int $height)
  {
    $new_width = $width > $height ? 240 : 240 * ($width / $height);
    $new_height = $height > $width ? 240 : 240 * ($height / $width);
    $thumbImg = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($thumbImg, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    return $thumbImg;
  }
}
