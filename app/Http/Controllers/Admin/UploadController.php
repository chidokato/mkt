<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        Log::info('Upload request received.');

        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('/uploads');

            // Resize the image
            $image = Image::make($file)->resize(1500, 1500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Save the resized image
            $image->save($destinationPath . '/' . $filename);

            $url = url('/uploads/' . $filename);

            Log::info('File uploaded successfully.', ['url' => $url]);

            return response()->json([
                'uploaded' => true,
                'url' => $url
            ]);
        }

        Log::error('File upload failed.');
        return response()->json([
            'uploaded' => false,
            'error' => [
                'message' => 'File upload failed.'
            ]
        ]);
    }
}
