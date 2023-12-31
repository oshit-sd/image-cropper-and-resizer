# OSHITSD/ImageCrop
This package includes converting image compress, multiple image resizers; 

## License

Released under [MIT](/LICENSE) by [@oshit-sd](https://github.com/oshit-sd).

# Installation

You can install the package in to a Laravel project that uses ImageCrop via composer:

```bash
composer require riseuplabs/image-cropper
```


```php
// config filesystems.php 
'public_path' => [
    'driver'    => 'local',
    'root'      => public_path('uploads'),
    'url'       => public_path('/'),
],
```

```php
// use your driver in (.env) file
FILESYSTEM_DRIVER=public_path

// If you use storage path in FILESYSTEM_DRIVER
// run this command
php artisan storage:link
```

```php     
# Using namespace
use RiseUpLabs\ImageCropper\Facades\ImageCrop;

// in the controller just define resizes array if you have to resize an image in multiple pieces
class ImageCropController extends Controller
{
    // preferred size is set for uploading images
    private $resizeArr = [
        ["width" => 50, "height" => 50],
        ["width" => 100, "height" => 100],
        ["width" => 200, "height" => 200],
        ["width" => 300, "height" => 300],
        ["width" => 400, "height" => 400],
        ["width" => 500, "height" => 500],
        ["width" => 600, "height" => 600],
        ["width" => 700, "height" => 700],
    ];

    public function store(Request $request)
    {
        /**
         * @param $file
         * @param $folder_path ex: photos or album/photos
         * @param $this->resizeArr (optional)
         */ 
        $image_path = ImageCrop::perfect($request->file, "photos", $this->resizeArr);
        // $image_path = ImageCrop::force($request->file, "photos", $this->resizeArr);
        // $image_path = ImageCrop::crop($request->file, "photos", $this->resizeArr);
        // $image_path = ImageCrop::original($request->file, "photos");
        return $image_path;
    }
}

```

## #Upload original image

```php
/**
 * @param $file
 * @param $folder_path ex: photos or album/photos
 */ 
$image_path = ImageCrop::original($request->file, "photos");
echo $image_path;

# output
"photos/original/daHnhY2iZlQkpuFVnB0CfgLpooVBLLoKcu7ynKEe.jpg"
```

## #Upload original image with compress quality

```php
/**
 * @param $file
 * @param $folder_path ex: photos or album/photos
 * @param $quality
 */ 
$image_path = ImageCrop::compress($request->file, "photos", 20);
echo $image_path;

# output
"photos/original_compress/u49le8sesELKxv9VIfWPG8BGaVt5BAVn7WEs17Kk.jpg"
```

## #Resize image with perfectly

```php 
// Resize the image according to the width dimension
$images = ImageCrop::perfect($request->file, "photos", $this->resizeArr);
dd($images);

# output
[
    "resize_50" => "photos/perfect_50X50/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_100" => "photos/perfect_100X100/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_200" => "photos/perfect_200X200/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_300" => "photos/perfect_300X300/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_400" => "photos/perfect_400X400/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_500" => "photos/perfect_500X500/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_600" => "photos/perfect_600X600/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
    "resize_700" => "photos/perfect_700X700/1UOjVGBo1bV7VDUxgQrZVhn0h2si5VO8ROV6I7qr.jpg"
]
```

## #Resize image with perfectly and original file uploaded

```php 
// Resize the image according to the width dimension
$images = ImageCrop::perfectWithOriginal($request->file, "photos", $this->resizeArr);
dd($images);

# output
[
    "original" => "photos/original/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_50" => "photos/perfect_50X50/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_100" => "photos/perfect_100X100/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_200" => "photos/perfect_200X200/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_300" => "photos/perfect_300X300/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_400" => "photos/perfect_400X400/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_500" => "photos/perfect_500X500/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_600" => "photos/perfect_600X600/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
    "resize_700" => "photos/perfect_700X700/owVzb9Qg4ywXNaGKLOoMtsONH82rbWD13z9XylB6.jpg"
]
```

## #Resize image like as perfect (force / crop)

```php 
// resize with force image
// Resize the image according to the defined height and width
$images = ImageCrop::force($request->file, "photos", $this->resizeArr);
$images = ImageCrop::forceWithOriginal($request->file, "photos", $this->resizeArr);

// resize with crop image
// Crop the image to the center point of the image
$images = ImageCrop::crop($request->file, "photos", $this->resizeArr);
$images = ImageCrop::cropWithOriginal($request->file, "photos", $this->resizeArr);
```
