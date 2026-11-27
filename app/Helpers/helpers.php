<?php 
// use Intervention\Image\ImageManagerStatic as Image;
// use Illuminate\Support\Str;

// function saveImage($file, $path = 'data/images/', $width = 800, $height = 800) {
//     $originalFilename = $file->getClientOriginalName();
//     $filenameWithoutExtension = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME), '-');
//     $extension = $file->getClientOriginalExtension();
//     $filename = $filenameWithoutExtension . '.' . $extension;

//     while (file_exists(public_path($path . $filename))) {
//         $filename = $filenameWithoutExtension . '_' . rand(0, 99) . '.' . $extension;
//     }

//     $img = Image::make($file)
//         ->resize($width, $height, function ($constraint) {
//             $constraint->aspectRatio();
//         })
//         ->save(public_path($path . $filename));

//     return $filename;
// }


