<?php

namespace RiseUpLabs\ImageCropper;

use Illuminate\Support\ServiceProvider;

class ImageCropperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('image-crop', function () {
            return new ImageCrop;
        });
    }

    public function boot()
    {
        # code...
    }
}
