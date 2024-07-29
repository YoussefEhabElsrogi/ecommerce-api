<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Apply the 'auth.admin' middleware to all methods except 'login' and 'register'
        $this->middleware('auth.admin');
    }
    public function index()
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            return ApiResponse::sendResponse(200, 'No Categories Yet', []);
        }

        $categoriesResource = CategoryResource::collection($categories);

        return ApiResponse::sendResponse(200, 'Categories Retrieved Successfully', $categoriesResource);
    }

    public function store(StoreCategoryRequest $request)
    {
        $admin = Auth::guard('admin')->user()->name;

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = ImageHelper::uploadImage('categories', $request->file('image'));
        }

        $data['created_by'] = $admin;

        $category = Category::create($data);

        if (!$category) {
            return ApiResponse::sendResponse(500, 'Category creation failed', []);
        }

        $categoryResource = new CategoryResource($category);

        return ApiResponse::sendResponse(201, 'Category created successfully', $categoryResource);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return ApiResponse::sendResponse(404, 'Category Not Found', []);
        }

        $categoryResource = new CategoryResource($category);

        return ApiResponse::sendResponse(200, 'Category Retrieved Successfully', $categoryResource);
    }

    public function update(UpdateCategoryRequest $request, $slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return ApiResponse::sendResponse(404, 'Category Not Found', []);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old category image
            ImageHelper::handleImageUpdate($category, 'categories');

            // Update the image path and public_id in the data array
            $data['image'] = ImageHelper::uploadImage('categories', $request->file('image'));
        }

        $category->update($data);

        return ApiResponse::sendResponse(200, 'Category Updated Successfully', new CategoryResource($category));
    }

    public function destroy($slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return ApiResponse::sendResponse(404, 'Category Not Found', []);
        }

        // Delete old category image
        ImageHelper::handleImageUpdate($category, 'categories');

        $category->delete();

        return ApiResponse::sendResponse(200, 'Category Deleted Successfully', []);
    }
}
