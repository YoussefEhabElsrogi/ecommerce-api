<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateImageRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\Image;
use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    public function __construct()
    {
        // Apply the 'auth.admin' middleware to all methods except 'login' and 'register'
        $this->middleware('auth.admin');
    }
    public function index()
    {
        $products = Product::with('images')->get();

        if ($products->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No Products Yet', []);
        }

        $productResources = ProductResource::collection($products);

        return ApiResponse::sendResponse(200, 'Products Retrieved Successfully', $productResources);
    }
    public function store(StoreProductRequest $request)
    {
        // Validate the request data
        $data = $request->validated();

        // Create the product
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category_id' => $data['category_id'],
        ]);

        // Save images for the product
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Upload image and get its path
                $imagePath = ImageHelper::uploadImage('products', $image);

                Image::create([
                    'image' => $imagePath,
                    'product_id' => $product->id
                ]);
            }
        }

        // Create ProductResource
        $productResource = new ProductResource($product);

        // Return response
        return ApiResponse::sendResponse(201, 'Product Created Successfully', $productResource);
    }
    public function show($slug)
    {
        $product = Product::where('slug', $slug)->with('images')->first();

        if (!$product) {
            return ApiResponse::sendResponse(404, 'Product Not Found', []);
        }

        $productResource = new ProductResource($product);

        return ApiResponse::sendResponse(200, 'Product Retrieved Successfully', $productResource);
    }
    public function update(UpdateProductRequest $request, $slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return ApiResponse::sendResponse(404, 'Product Not Found', []);
        }

        $data = $request->validated();

        $product->update($data);

        return ApiResponse::sendResponse(200, 'Product Updated Successfully', []);
    }
    public function updateImage(UpdateImageRequest $request, $id)
    {
        // Find the image by ID
        $image = Image::find($id);

        if (!$image) {
            return ApiResponse::sendResponse(404, 'Image Not Found', []);
        }

        // Delete old category image
        ImageHelper::handleImageUpdate($image, 'products');

        $newImagePath =  ImageHelper::uploadImage('products', $request->file('image'));

        $image->update([
            'image' => $newImagePath,
        ]);

        return ApiResponse::sendResponse(200, 'Image Updated Successfully', []);
    }
    public function destroy($slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return ApiResponse::sendResponse(404, 'Product Not Found', []);
        }

        $images = $product->images;

        foreach ($images as $image) {

            // Extract public_id from the image URL
            $parsedUrl = parse_url($image->image, PHP_URL_PATH);
            // Remove folder path and extension to get the public_id
            $publicId = "products/" . pathinfo($parsedUrl, PATHINFO_FILENAME);

            Cloudinary::destroy($publicId);
        }

        $product->delete();

        return ApiResponse::sendResponse(200, 'Product Deleted Successfully', []);
    }
}
