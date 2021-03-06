<?php

/*
 * This file is part of Imaging package.
 */

/*
|--------------------------------------------------------------------------
| Auto Upload, Delete and Resizing of Images
|--------------------------------------------------------------------------
|
|
*/

namespace App\Traits;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

trait Imaging
{
    /**
     * Get storage path for images, photos or pictures.
     *
     * @return string
     */
    public static function storagePath()
    {
        return 'images';
    }

    /**
     * Get compression rate.
     *
     * @return int
     */
    public static function compressionRate()
    {
        return 70;
    }

    /**
     * Check whether to auto resize the image, photo or picture.
     *
     * @return true
     */
    public static function auteResize()
    {
        return true;
    }

    /**
     * Default resolution.
     *
     * @return string
     */
    public static function defaultResolution()
    {
        return '766';
    }

    /**
     * default 16:9 ratio resolutions.
     *
     * @return array
     */
    public static function resolutions()
    {
        return [
            '766' => [
                'width'  => 1366,
                'height' => 766
            ],
            '900' => [
                'width'  => 1600,
                'height' => 900
            ],
            '1080' => [
                'width'  => 1920,
                'height' => 1080
            ],
            '1440' => [
                'width'  => 2560,
                'height' => 1440
            ],
            '2160' => [
                'width'  => 3840,
                'height' => 2160
            ]
        ];
    }

    /**
     * If directory doesn't exist create directory.
     *
     * @return void
     */
    public static function createDirectory()
    {
        Storage::makeDirectory('public/' . self::storagePath(), 0775, true);
    }

    /**
     * Create sizes and store image, photo or picture automatically.
     *
     * @param  Illuminate\Database\Eloquent\Model $model
     * @param  array $sizes
     * @return void
     */
    public static function storeImage($model, $fieldName = null, $isResize = false, $sizes = null)
    {
        self::createDirectory();

        $storagePath       = self::storagePath();
        $resolutions       = self::resolutions();
        $defaultResolution = self::defaultResolution();
        $compressionRate   = self::compressionRate();

        $imageName    = uniqid() . '.jpg';

        switch ($fieldName) {
          case "header_image":
              if (!request()->hasFile('header_image')) {
                  return ;
              }
              $image = request()->header_image;
              $model->header_image = $imageName;
              break;
          case "intro_image":
              if (!request()->hasFile('intro_image')) {
                  return ;
              }
              $image = request()->intro_image;
              $model->intro_image = $imageName;
              break;
          case "screen_image":
              if (!request()->hasFile('screen_image')) {
                  return ;
              }
              $image = request()->screen_image;
              $model->screen_image = $imageName;
              break;
          default:
              if (!request()->hasFile('image')) {
                  return ;
              }
              $image = $model->image;
              $model->image = $imageName;
        }

        if ($sizes != null && is_array($sizes)) {
            foreach ($sizes as $size) {
                foreach ($resolutions as $key => $value) {
                    if ($defaultResolution != $size && $key == $size) {
                        Image::make($image)->resize($value['width'], $value['height'])
                            ->encode('jpg', $compressionRate)
                            ->save(
                                storage_path('app/public/' . $storagePath . '/' . $value['width'] . '-' . $value['height'] . '-' . $imageName),
                                $compressionRate
                            );
                    }
                }
            }
        }
            
        $image = Image::make($image);
            
        if ($isResize) {
            $image = Image::make($image)->resize(
                $resolutions[$defaultResolution]['width'],
                $resolutions[$defaultResolution]['height']
            );
        }
            
        $image->encode('jpg', $compressionRate)->save(
            storage_path('app/public/' . $storagePath . '/' . $imageName),
            $compressionRate
        );
    }

    /**
     * Delete image including sizes.
     *
     * @param  Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public static function deleteImage($model)
    {
        if (request()->hasFile('image') || request()->hasFile('picture') || request()->hasFile('photo') || request()->hasFile('intro_image') || request()->hasFile('screen_image')) {
            $storagePath = self::storagePath();
            $resolutions = self::resolutions();

            foreach ($resolutions as $key => $value) {
                $image = $value['width'] . '-' . $value['height'] . '-' . $model->image;
                Storage::delete('public/' . $storagePath . '/' . $image);
            }

            Storage::delete('public/' . $storagePath . '/' . $model->image);
        }
    }

    /**
     * Update image including sizes.
     *
     * @param  Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public static function updateImage($model, $fieldName = null, $isResize = false)
    {
        if (request()->hasFile('image') || request()->hasFile('picture') || request()->hasFile('photo') || request()->hasFile('intro_image') || request()->hasFile('screen_image') || request()->hasFile('header_image')) {
            self::deleteImage($model->findorFail($model->id));
            self::storeImage($model, $fieldName, $isResize);
        }
    }
}
