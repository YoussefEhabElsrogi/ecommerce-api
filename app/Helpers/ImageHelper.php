<?php

namespace App\Helpers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageHelper
{
    public static function uploadImage($path, $image)
    {
        $publicId = time() . '-' . $image->getClientOriginalName();
        $uploadImage = Cloudinary::upload($image->getRealPath(), [
            'folder' => $path,
            'public_id' => $publicId
        ])->getSecurePath();

        return $uploadImage;
    }
    public static function deleteImage($publicId)
    {
        Cloudinary::destroy($publicId);
    }
    public static function handleImageUpdate($value, $path)
    {
        $oldImageValue = $value->image;
        if ($oldImageValue) {
            // Extract public_id from the image URL
            $parsedUrl = parse_url($oldImageValue, PHP_URL_PATH);
            // Remove folder path and extension to get the public_id
            $publicId = "$path/" . pathinfo($parsedUrl, PATHINFO_FILENAME);

            self::deleteImage($publicId);
        }
    }
}
