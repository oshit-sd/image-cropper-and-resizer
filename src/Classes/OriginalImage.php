<?php

namespace RiseUpLabs\ImageCropper\Classes;

use Illuminate\Support\Facades\Storage;

class OriginalImage extends BaseClass
{
    /**
     * original image upload
     * @param $file
     * @param $folder
     *
     * @return string
     */
    public function upload($file, $folder): string
    {
        $file_name     = $file->hashName();
        $upload_path = "{$folder}/original";

        $this->makeDir($upload_path);

        return Storage::disk("{$this->DISK_NAME}")->putFileAs($upload_path, $file, $file_name);
        return "";
    }

    /**
     * original image compress and upload
     * @param $file
     * @param $folder
     *
     * @return string
     */
    public function compress($file, $folder, $quality): string
    {
        $upload_path = "{$folder}/compress";
        $this->makeDir($upload_path);

        $image = $this->imageCreate($file);

        return $this->imageUpload($file, $image, $upload_path, $quality);
    }
}
