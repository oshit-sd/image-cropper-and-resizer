<?php

namespace RiseUpLabs\ImageCropper;

use Exception;
use RiseUpLabs\ImageCropper\Traits\ResizeTrait;

class ImageCrop
{
    use ResizeTrait;

    public $resize_arr = [];
    public $resize_type = [];

    /**
     * original image size
     * @param $file
     * @param $folder_path
     *
     * @return original_image_url
     */
    public function original($file = null, $folder_path = null)
    {
        throw_unless($file, Exception::class, "File is required", 400);
        throw_unless($folder_path, Exception::class, "Folder path is required", 400);

        $images = $this->resizer($file, $folder_path, true);
        return $images['original'] ?? null;
    }

    public function compress($file = null, $folder_path = null, $quality = null)
    {
        throw_unless($quality, Exception::class, "Quality is required", 400);
        throw_unless($file, Exception::class, "File is required", 400);
        throw_unless($folder_path, Exception::class, "Folder path is required", 400);

        $this->resize_type = 'original_compress';
        $images =  $this->resizer($file, $folder_path, false, false, $quality);
        return $images['original_compress'] ?? null;
    }

    /**
     * perfect image sizes
     * @param $file
     * @param $folder_path
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    public function perfect($file = null, $folder_path = null, $resize_arr = [])
    {
        $this->throw_message($file, $folder_path, $resize_arr);

        $this->resize_type = 'perfect';
        return $this->resizer($file, $folder_path, false, true);
    }

    public function perfectWithOriginal($file = null, $folder_path = null, $resize_arr = [])
    {
        $this->throw_message($file, $folder_path, $resize_arr);

        $this->resize_type = 'perfect';
        return $this->resizer($file, $folder_path, true, true);
    }

    /**
     * force image sizes
     * @param $file
     * @param $folder_path
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    public function force($file = null, $folder_path = null, $resize_arr = [])
    {
        $this->throw_message($file, $folder_path, $resize_arr);

        $this->resize_type = 'force';
        return $this->resizer($file, $folder_path, false, true);
    }

    public function forceWithOriginal($file = null, $folder_path = null, $resize_arr = [])
    {
        $this->throw_message($file, $folder_path, $resize_arr);

        $this->resize_type = 'force';
        return $this->resizer($file, $folder_path, true, true);
    }

    /**
     * crop image sizes
     * @param $file
     * @param $folder_path
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    public function crop($file = null, $folder_path = null, $resize_arr = [])
    {
        $this->throw_message($file, $folder_path, $resize_arr);

        $this->resize_type = 'crop';
        return $this->resizer($file, $folder_path, false, true);
    }

    public function cropWithOriginal($file = null, $folder_path = null, $resize_arr = [])
    {
        $this->throw_message($file, $folder_path, $resize_arr);

        $this->resize_type = 'crop';
        return $this->resizer($file, $folder_path, true, true);
    }

    /**
     * throw_message
     * @param $file
     * @param $folder_path
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    private function throw_message($file = null, $folder_path = null, $resize_arr = [])
    {
        throw_unless($file, Exception::class, "File is required", 400);
        throw_unless($folder_path, Exception::class, "Folder path is required", 400);
        throw_if(empty($resize_arr), Exception::class, "Resize array is required", 400);

        $this->resize_arr = $resize_arr;
    }
}
