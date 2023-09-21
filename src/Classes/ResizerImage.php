<?php

namespace RiseUpLabs\ImageCropper\Classes;

class ResizerImage extends BaseClass
{
    /**
     * resizer
     * 
     * @param $file
     * @param $folder
     * @param $resize_arr
     * @param $type
     *
     * @return array
     */
    public function resize($file, $folder, $resize_arr, $type, $quality = 100): array
    {
        $uploaded_paths  = [];

        $image_create = $this->imageCreate($file);

        foreach ($resize_arr ?? [] as $key => $resize) {
            $width  = $resize['width'] ?? 0;
            $height = $resize['height'] ?? 0;

            $upload_path = "{$folder}/perfect_{$width}X{$height}";
            $this->makeDir($upload_path);

            switch ($type) {
                case 'perfect':
                    $new_image = $this->resize_image_perfect($image_create, $width, $height);
                    break;
                case 'force':
                    $new_image = $this->resize_image_force($image_create, $width, $height);
                    break;
                case 'crop':
                    $new_image = $this->resize_image_crop($image_create, $width, $height);
                    break;
            }

            $uploaded_path  = $this->imageUpload($file, $new_image, $upload_path, $quality);
            $uploaded_paths += ["resize_{$width}" => $uploaded_path];
        }
        return $uploaded_paths;
    }

    /**
     * resize image perfectly
     * 
     * @param $image
     * @param $width
     * @param $height
     *
     * @return array
     */
    public function resize_image_perfect($image, $width, $height)
    {
        $w = imagesx($image); //current width
        $h = imagesy($image); //current height
        if ((!$w) || (!$h)) {
            throw_if(true, Exception::class, "Image could not be resized because it was not a valid image.", 400);
        }

        if (($w <= $width) && ($h <= $height)) {
            return $image;
        } //no resizing needed

        // try max width first...
        $ratio = $width / $w;
        $new_w = $width;
        $new_h = $h * $ratio;

        // if that didn't work
        if ($new_h > $height) {
            $ratio = $height / $h;
            $new_h = $height;
            $new_w = $w * $ratio;
        }

        $new_image = imagecreatetruecolor($new_w, $new_h);
        $white = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $white);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        imageresolution($new_image, 96, 96);

        return $new_image;
    }

    /**
     * force image perfectly
     * 
     * @param $image
     * @param $width
     * @param $height
     *
     * @return array
     */
    public function resize_image_force($image, $width, $height)
    {
        $w = imagesx($image); //current width
        $h = imagesy($image); //current height
        if ((!$w) || (!$h)) {
            throw_if(true, Exception::class, "Image could not be resized because it was not a valid image.", 400);
        }
        if (($w == $width) && ($h == $height)) {
            return $image;
        } //no resizing needed

        $image2 = imagecreatetruecolor($width, $height);
        imagecopyresampled($image2, $image, 0, 0, 0, 0, $width, $height, $w, $h);

        return $image2;
    }

    /**
     * crop image perfectly
     * 
     * @param $image
     * @param $width
     * @param $height
     *
     * @return array
     */
    public function resize_image_crop($image, $width, $height)
    {
        $w = imagesx($image); //current width
        $h = imagesy($image); //current height
        if ((!$w) || (!$h)) {
            throw_if(true, Exception::class, "Image could not be resized because it was not a valid image.", 400);
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
}
