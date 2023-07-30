<?php

namespace RiseUpLabs\ImageCropper\Facades;

use Illuminate\Support\Facades\Facade;

class ImageCrop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'image-crop';
    }
}
