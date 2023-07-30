<?php

/**
 * Dev: @OSHIT SUTRA DAR
 */

namespace RiseUpLabs\ImageCropper\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;

trait ResizeTrait
{
    private $disk = null;

    /**
     * Call Resizer Function
     * @param $type, $file, $folder
     *
     * @return resize_wise_image_url
     */
    protected function resizer($file, $folder, $original = false, $resize = false)
    {
        $this->disk = config('filesystems.default');
        $diskRoot = config('filesystems.disks')[$this->disk]['root'] ?? '';

        $this->makeDir($folder);

        $imageUrls = [];

        // original image upload
        if ($original) {
            // file_name generate
            $fileName = $file->hashName();
            // upload original image
            $this->makeDir($folder, 'original');
            Storage::disk("{$this->disk}")->putFileAs("{$folder}/original", $file, $fileName);
            $imageUrls += ["original" => "{$folder}/original/" . $fileName];
        }

        // original image compress
        if ($this->resize_type == 'original_compress') {
            // upload original image compress
            $this->makeDir($folder, 'original_compress');
            $upload_path = "{$diskRoot}/{$folder}/original_compress/";
            $fileName    = $this->resize_image($file, $upload_path, 100, 100);
            $imageUrls += ["original_compress" => "{$folder}/original_compress/" . $fileName];
        }

        // All resize image
        if ($resize) {
            foreach ($this->resize_arr ?? [] as $key => $resize) {
                $subFolder = "{$this->resize_type}_{$resize['width']}X{$resize['height']}";
                // folder create
                $this->makeDir($folder, $subFolder);

                // folder path and and resize
                $upload_path = "{$diskRoot}/{$folder}/{$subFolder}/";
                $fileName    = $this->resize_image($file, $upload_path, $resize['width'], $resize['height']);

                if (!empty($fileName['errors'])) { // if any error occured
                    throw_if(true, Exception::class, "An error occurred while processing croping image, please try again", 400);
                }

                $imageUrls += ["resize_{$resize['width']}" => "{$folder}/{$subFolder}/" . $fileName];
            }
        }

        if (!empty($GLOBALS['errors'])) {
            return $this->counting_errors();
        }

        return $imageUrls;
    }

    /**
     * Resize Image
     */
    protected function resize_image($file, $upload_path, $width, $height)
    {
        if ((!array_key_exists('errors', $GLOBALS)) || (!is_array($GLOBALS['errors']))) {
            $GLOBALS['errors'] = [];
        }
        $type = $this->resize_type;

        $extension = "." . $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), ['.jpg', '.jpeg', '.png', '.gif', '.bmp'])) {
            throw_if(true, Exception::class, "Invalid source file extension, supported ext: jpg, jpeg, png, gif", 400);
        }

        if (in_array(strtolower($extension), ['.jpg', '.jpeg'])) {
            $image = @imagecreatefromjpeg($file);
        } elseif (strtolower($extension) == '.png') {
            $image = @imagecreatefrompng($file);
        } elseif (strtolower($extension) == '.gif') {
            $image = @imagecreatefromgif($file);
        } elseif (strtolower($extension) == '.bmp') {
            $image = @imagecreatefromwbmp($file);
        }

        if (!$image) {
            throw_if(true, Exception::class, "Image could not be generated!", 400);
        } else {
            $current_width  = imagesx($image);
            $current_height = imagesy($image);
            if ((!$current_width) || (!$current_height)) {
                throw_if(true, Exception::class, "Generated image has invalid dimensions!", 400);
            }
        }

        $quality = 100;
        if ($type == 'force') {
            $new_image = $this->resize_image_force($image, $width, $height);
        } elseif ($type == 'perfect') {
            $new_image = $this->resize_image_perfect($image, $width, $height);
        } elseif ($type == 'crop') {
            $new_image = $this->resize_image_crop($image, $width, $height);
        } elseif ($type == 'original_compress') {
            $quality = 20;
            $new_image = $image;
        }

        if ((!$new_image) && (count($GLOBALS['errors']) == 0)) {
            throw_if(true, Exception::class, "New image could not be generated!", 400);
        }
        if (count($GLOBALS['errors']) > 0) {
            @imagedestroy($image);
            return $this->counting_errors();
        }

        $fileName = $file->hashName();
        $upload_path .= $fileName;

        $save_error = false;
        if (in_array(strtolower($extension), ['.jpg', '.jpeg'])) {
            imagejpeg($new_image, $upload_path, $quality) or ($save_error = true);
        } elseif (strtolower($extension) == '.png') {
            $quality = round((100 - $quality) * 0.09);
            imagepng($new_image, $upload_path, $quality) or ($save_error = true);
        } elseif (strtolower($extension) == '.gif') {
            imagegif($new_image, $upload_path) or ($save_error = true);
        } elseif (strtolower($extension) == '.bmp') {
            imagewbmp($new_image, $upload_path, $quality) or ($save_error = true);
        }
        if ($save_error) {
            throw_if(true, Exception::class, "New image could not be saved!", 400);
        }
        if (count($GLOBALS['errors']) > 0) {
            @imagedestroy($image);
            @imagedestroy($new_image);
            return $this->counting_errors();
        }

        @imagedestroy($image);
        @imagedestroy($new_image);

        return $fileName;
    }

    /**
     * Resize Image Perfect
     *
     * @return image_obj
     */
    protected function resize_image_perfect($image, $max_width, $max_height)
    {
        $w = imagesx($image); //current width
        $h = imagesy($image); //current height
        if ((!$w) || (!$h)) {
            $GLOBALS['errors'][] = 'Image could not be resized because it was not a valid image.';
            return false;
        }

        if (($w <= $max_width) && ($h <= $max_height)) {
            return $image;
        } //no resizing needed

        // try max width first...
        $ratio = $max_width / $w;
        $new_w = $max_width;
        $new_h = $h * $ratio;

        // if that didn't work
        if ($new_h > $max_height) {
            $ratio = $max_height / $h;
            $new_h = $max_height;
            $new_w = $w * $ratio;
        }

        $new_image = imagecreatetruecolor($new_w, $new_h);
        // sets background to red
        $white = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $white);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        imageresolution($new_image, 600, 600);

        return $new_image;
    }

    /**
     * Resize Image With Crop
     *
     * @return image_obj
     */
    protected function resize_image_crop($image, $width, $height)
    {
        $w = @imagesx($image); //current width
        $h = @imagesy($image); //current height
        if ((!$w) || (!$h)) {
            $GLOBALS['errors'][] = 'Image could not be resized because it was not a valid image.';
            return false;
        }
        if (($w == $width) && ($h == $height)) {
            return $image;
        } //no resizing needed

        //try max width first...
        $ratio = $width / $w;
        $new_w = $width;
        $new_h = $h * $ratio;

        //if that created an image smaller than what we wanted, try the other way
        if ($new_h < $height) {
            $ratio = $height / $h;
            $new_h = $height;
            $new_w = $w * $ratio;
        }

        $image2 = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($image2, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        //check to see if cropping needs to happen
        if (($new_h != $height) || ($new_w != $width)) {
            $image3 = imagecreatetruecolor($width, $height);
            if ($new_h > $height) { //crop vertically
                $extra = $new_h - $height;
                $x     = 0; //source x
                $y     = round($extra / 2); //source y
                imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
            } else {
                $extra = $new_w - $width;
                $x     = round($extra / 2); //source x
                $y     = 0; //source y
                imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
            }
            imagedestroy($image2);
            return $image3;
        } else {
            return $image2;
        }
    }

    /**
     * Resize Image With Force
     *
     * @return image_obj
     */
    protected function resize_image_force($image, $width, $height)
    {
        $w = @imagesx($image); //current width
        $h = @imagesy($image); //current height
        if ((!$w) || (!$h)) {
            $GLOBALS['errors'][] = 'Image could not be resized because it was not a valid image.';
            return false;
        }
        if (($w == $width) && ($h == $height)) {
            return $image;
        } //no resizing needed

        $image2 = imagecreatetruecolor($width, $height);
        imagecopyresampled($image2, $image, 0, 0, 0, 0, $width, $height, $w, $h);

        return $image2;
    }

    /**
     * If Any Errors Occured
     *
     * @return image_obj
     */
    protected function counting_errors()
    {
        if ((!array_key_exists('errors', $GLOBALS)) || (!is_array($GLOBALS['errors']))) {
            $GLOBALS['errors'] = [];
        }
        $errors = [];
        foreach ($GLOBALS['errors'] as $error) {
            array_push($errors, $error);
        }
        // return ['errors' => $errors];
        throw_if(true, Exception::class, "An error occurred while processing croping image, please try again", 400);
    }

    /*-----Folder Create-----*/
    public function makeDir($folder, $subfolder = null)
    {
        $diskRoot = config('filesystems.disks')[$this->disk]['root'] ?? '';

        $main_dir = "{$diskRoot}/{$folder}";
        if ($subfolder) {
            $main_dir = "{$diskRoot}/{$folder}/{$subfolder}";
        }

        if (!file_exists($main_dir)) {
            mkdir($main_dir, 0777, true);
        }
    }
}
