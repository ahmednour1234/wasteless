<?php

namespace App\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    public static function uploadImage(?UploadedFile $file, string $directory = 'uploads'): ?string
    {
        if (! $file) {
            return null;
        }

        // make sure directory exists
        $destination = public_path($directory);
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        // unique file name
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        // move file to /public/{directory}
        $file->move($destination, $filename);

        return $directory.'/'.$filename;       // → use with asset()
    }
}
