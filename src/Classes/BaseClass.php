<?php

namespace RiseUpLabs\ImageCropper\Classes;

use Exception;

class BaseClass
{
    protected $DISK_NAME = null;
    protected $DISK_ROOT = null;

    public function __construct()
    {
        $this->DISK_NAME = config('filesystems.default');
        $this->DISK_ROOT = config('filesystems.disks')[$this->DISK_NAME]['root'] ?? '';

        throw_unless($this->DISK_ROOT, Exception::class, "Set your disk root key", 400);
    }

    /**
     * folder create
     * @param $folder
     */
    protected function makeDir($folder)
    {
        $dir = "{$this->DISK_ROOT}/{$folder}";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
    }

    /**
     * image upload
     * @param $file
     * @param $upload_path
     * @param $quality = 100
     */
    protected function imageUpload($file, $image, $upload_path, $quality = 100)
    {
        $extension   = $file->getClientOriginalExtension();
        $file_name   = $file->hashName();
        $upload_dir  = "{$this->DISK_ROOT}/{$upload_path}/{$file_name}";

        $save_error = false;
        if (in_array(strtolower($extension), ['jpg', 'jpeg'])) {
            imagejpeg($image, $upload_dir, $quality) or ($save_error = true);
        } elseif (strtolower($extension) == 'png') {
            $quality = round((100 - $quality) * 0.09);
            imagepng($image, $upload_dir, $quality) or ($save_error = true);
        } elseif (strtolower($extension) == 'gif') {
            imagegif($image, $upload_dir) or ($save_error = true);
        }

        if ($save_error) {
            throw_if(true, Exception::class, "Something went wrong! image could not be saved!", 400);
        }

        imagedestroy($image);

        return "{$upload_path}/{$file_name}";
    }

    /**
     * image create
     * @param $file
     * @param $extension
     */
    protected function imageCreate($file)
    {
        $extension = $file->getClientOriginalExtension();

        if (in_array(strtolower($extension), ['jpg', 'jpeg'])) {
            $image = imagecreatefromjpeg($file);
        } elseif (strtolower($extension) == 'png') {
            $image = imagecreatefrompng($file);
        } elseif (strtolower($extension) == 'gif') {
            $image = imagecreatefromgif($file);
        }

        if (!$image) {
            throw_if(true, Exception::class, "Image could not be generated!", 400);
        }

        return $image;
    }
}
