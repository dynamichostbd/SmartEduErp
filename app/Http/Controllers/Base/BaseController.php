<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BaseController extends Controller
{

    protected function upload($file, $path, $old = null, $base64 = false)
    {
        $code = date('ymdhis') . '_' . rand(1111, 9999);
        $publicPath = public_path('upload/');
        $this->makeDir($publicPath . $path);

        // DELETE OLD FILE
        if (!empty($old)) {
            $oldFile = $this->oldFile($old, true); // ✅ let oldFile handle deletion
        }

        // FILE/IMAGE UPLOAD
        if (!empty($file) && !$base64) {
            $fileName = Str::lower($code . $file->getClientOriginalName());
            $file->move($publicPath . $path . '/', $fileName);

            return 'upload/' . $path . '/' . $fileName;
        }

        // BASE64 UPLOAD
        if (!empty($file) && $base64) {
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $file);
            $image = str_replace(' ', '+', $image);

            $imageName = $code . '.jpeg';
            $savePath  = $publicPath . $path . '/' . $imageName;

            file_put_contents($savePath, base64_decode($image));

            return 'upload/' . $path . '/' . $imageName;
        }

        return null;
    }

    /*-----OLD IMAGE-----*/
    public function oldFile($file, $isDelete = false)
    {
        if (empty($file)) {
            return null;
        }

        $relativePath = '';

        // Normalize path (remove domain if passed as full URL)
        $file = str_replace(url('/') . '/', '', $file);

        // Handle "upload/..." directly
        if (str_starts_with($file, 'upload/')) {
            $relativePath = $file;
        } elseif (str_starts_with($file, 'storage/')) {
            // Convert "storage/..." into "upload/..."
            $ex = explode('storage/', $file, 2);
            $relativePath = !empty($ex[1]) ? 'upload/' . $ex[1] : '';
        }

        if (empty($relativePath)) {
            return null;
        }

        // Build both possible full paths
        $uploadPath  = public_path($relativePath); // public/upload/...
        $storagePath = public_path('storage/' . str_replace('upload/', '', $relativePath)); // public/storage/upload/...

        // ✅ Check existence
        $finalPath = null;
        if (is_file($uploadPath)) {
            $finalPath = $uploadPath;
        } elseif (is_file($storagePath)) {
            $finalPath = $storagePath;
        }

        if ($isDelete && $finalPath) {
            unlink($finalPath);
        }

        // Return relative path if file exists, otherwise null
        return $finalPath ? $relativePath : null;
    }



    /*-----Folder Create-----*/
    public function makeDir($folder, $subfolder = null)
    {
        $main_dir = $folder;
        if ($subfolder) {
            $main_dir = $folder . '/' . $subfolder;
        }

        if (!file_exists($main_dir)) {
            mkdir($main_dir, 0777, true);
        }
    }
}
