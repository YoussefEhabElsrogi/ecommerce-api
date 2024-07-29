<?php

namespace App\Http\Controllers\Api\user;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddToCartRequest;
use App\Http\Requests\Api\UpdateAddToCartRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }
    public function addToCart(StoreAddToCartRequest $request)
    {
        $data = $request->validated();

        $currentUser = Auth::id();

        $data['user_id'] = $currentUser;

        $cartItem = Cart::updateOrCreate(
            ['user_id' => $currentUser, 'product_id' => $data['product_id']],
            ['quantity' => $data['quantity']]
        );

        $cartItemResource = new CartResource($cartItem);

        return ApiResponse::sendResponse(200, 'Product added to cart successfully', $cartItemResource);
    }
    public function getCartItems()
    {
        $currentUser = Auth::id();

        $user = User::find($currentUser);

        if (!$user) {
            return ApiResponse::sendResponse(404, 'User Not Found', []);
        }

        $cartItems = Cart::where('user_id', $currentUser)->with(['user', 'product'])->get();

        $cartItemsResource = CartResource::collection($cartItems);

        return ApiResponse::sendResponse(200, 'Cart items retrieved successfully', $cartItemsResource);
    }
    public function updateCartItem(UpdateAddToCartRequest $request, $id)
    {
        $data = $request->validated();
        $currentUser = Auth::id();

        $cartItem = Cart::where('user_id', $currentUser)->where('id', $id)->first();

        if (!$cartItem) {
            return ApiResponse::sendResponse(404, 'Cart item not found', []);
        }

        $cartItem->update(['quantity' => $data['quantity']]);

        return ApiResponse::sendResponse(200, 'Cart item updated successfully', []);
    }
    public function removeCartItem($id)
    {
        $currentUser = Auth::id();
        $cartItem = Cart::where('user_id', $currentUser)->where('id', $id)->first();

        if (!$cartItem) {
            return ApiResponse::sendResponse(404, 'Cart item not found', []);
        }

        $cartItem->delete();

        return ApiResponse::sendResponse(200, 'Cart item removed successfully', []);
    }
}
