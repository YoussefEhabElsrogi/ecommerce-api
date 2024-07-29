<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id, // Product ID
            'product_name' => $this->product->name, // Product name
            'quantity' => $this->quantity, // Quantity ordered
            'price' => $this->price, // Price per unit
            'total' => $this->quantity * $this->price, // Total price for the item
        ];
    }
}
