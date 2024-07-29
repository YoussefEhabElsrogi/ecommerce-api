<?php

namespace App\Http\Controllers\Api\user;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\ProudctUserResource;
use App\Models\Category;
use App\Models\Product;

class UserCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
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
    public function showProducts($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return ApiResponse::sendResponse(404, 'Category Not Found', []);
        }

        $products = Product::where('category_id', $id)->paginate(3);

        if ($products->isNotEmpty()) {
            if ($products->total() > $products->perPage()) {
                $data = [
                    'records' => ProudctUserResource::collection($products),
                    'pagination links' => [
                        'current page' => $products->currentPage(),
                        'per page' => $products->perPage(),
                        'total' => $products->total(),
                        'links' => [
                            'first_page_url' => $products->url(1),
                            'last_page_url' => $products->url($products->lastPage()),
                            'next_page_url' => $products->nextPageUrl(),
                            'prev_page_url' => $products->previousPageUrl(),
                        ]
                    ]
                ];
            } else {
                $data  = ProudctUserResource::collection($products);
            }
            return ApiResponse::sendResponse(200, 'Products Retrieved Successfully', $data);
        }
        return ApiResponse::sendResponse(200, 'No Products Available', []);
    }
}
