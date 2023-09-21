<?php

namespace RiseUpLabs\ImageCropper;

use Exception;
use Illuminate\Http\UploadedFile;
use RiseUpLabs\ImageCropper\Classes\OriginalImage;
use RiseUpLabs\ImageCropper\Classes\ResizerImage;

class ImageCrop
{
    public $resize_arr = [];
    public $resize_type = [];

    public $originalImage;
    public $resizerImage;

    public function __construct()
    {
        $this->originalImage    = new OriginalImage();
        $this->resizerImage     = new ResizerImage();
    }

    /**
     * original image size
     * @param $file
     * @param $folder
     *
     * @return uploaded_path
     */
    public function original(UploadedFile $file, string $folder)
    {
        $this->throw_message($file, $folder, [], 'original');

        return $this->originalImage->upload($file, $folder);
    }

    public function compress(UploadedFile $file, string $folder, int $quality)
    {
        $this->throw_message($file, $folder, [], 'original');
        throw_unless($quality, Exception::class, "Quality is required", 400);

        return $this->originalImage->compress($file, $folder, $quality);
    }

    /**
     * perfect image sizes
     * @param $file
     * @param $folder
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    public function perfect(UploadedFile $file, string $folder, array $resize_arr = [], int $quality = 100)
    {
        $this->throw_message($file, $folder, $resize_arr);

        return $this->resizerImage->resize($file, $folder, $resize_arr, 'perfect', $quality);
    }

    public function perfectWithOriginal(UploadedFile $file, string $folder, array $resize_arr = [], int $quality = 100)
    {
        $this->throw_message($file, $folder, $resize_arr);

        $resize_paths  = $this->resizerImage->resize($file, $folder, $resize_arr, 'perfect', $quality);
        $resize_paths += ['original' => $this->originalImage->upload($file, $folder)];

        return $resize_paths;
    }

    /**
     * force image sizes
     * @param $file
     * @param $folder
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    public function force(UploadedFile $file, string $folder, array $resize_arr = [], int $quality = 100)
    {
        $this->throw_message($file, $folder, $resize_arr);

        return $this->resizerImage->resize($file, $folder, $resize_arr, 'force', $quality);
    }

    public function forceWithOriginal(UploadedFile $file, string $folder, array $resize_arr = [], int $quality = 100)
    {
        $this->throw_message($file, $folder, $resize_arr);

        $resize_paths  = $this->resizerImage->resize($file, $folder, $resize_arr, 'force', $quality);
        $resize_paths += ['original' => $this->originalImage->upload($file, $folder)];

        return $resize_paths;
    }

    /**
     * crop image sizes
     * @param $file
     * @param $folder
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    public function crop(UploadedFile $file, string $folder, array $resize_arr = [], int $quality = 100)
    {
        $this->throw_message($file, $folder, $resize_arr);

        return $this->resizerImage->resize($file, $folder, $resize_arr, 'crop', $quality);
    }

    public function cropWithOriginal(UploadedFile $file, string $folder, array $resize_arr = [], int $quality = 100)
    {
        $this->throw_message($file, $folder, $resize_arr);

        $resize_paths  = $this->resizerImage->resize($file, $folder, $resize_arr, 'crop', $quality);
        $resize_paths += ['original' => $this->originalImage->upload($file, $folder)];

        return $resize_paths;
    }

    /**
     * throw_message
     * @param $file
     * @param $folder
     * @param $resize_arr
     *
     * @return array_of_images_url
     */
    private function throw_message($file = null, $folder = null, $resize_arr = [], $original = false)
    {
        throw_unless($file, Exception::class, "File is required", 400);
        throw_unless($folder, Exception::class, "Folder path is required", 400);

        if (!$original)
            throw_if(empty($resize_arr), Exception::class, "Resize array is required", 400);

        $extension = $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            throw_if(true, Exception::class, "Invalid source file extension, supported ext: jpg, jpeg, png, gif", 400);
        }
    }
}
