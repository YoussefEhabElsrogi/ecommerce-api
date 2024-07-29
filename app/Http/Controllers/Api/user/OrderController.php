<?php

namespace App\Http\Controllers\Api\user;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlaceOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function placeOrder(PlaceOrderRequest $request)
    {
        $data = $request->validated();
        $currentUser = Auth::id();

        $cartItems = Cart::where('user_id', $currentUser)->get();

        if ($cartItems->isEmpty()) {
            return ApiResponse::sendResponse(400, 'Cart is empty', []);
        }

        DB::beginTransaction();

        try {
            // Create a new order
            $order = Order::create([
                'user_id' => $currentUser,
                'total' => $cartItems->sum(function ($cartItem) {
                    return $cartItem->product->price * $cartItem->quantity;
                }),
                'status' => 'pending',
                'address' => $data['address'] // Adding the address here
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            // Clear the cart
            Cart::where('user_id', $currentUser)->delete();

            DB::commit();

            // Return the created order resource
            $orderResource = new OrderResource($order);
            return ApiResponse::sendResponse(200, 'Order placed successfully', $orderResource);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::sendResponse(500, 'An error occurred while placing the order: ' . $e->getMessage(), []);
        }
    }
}
