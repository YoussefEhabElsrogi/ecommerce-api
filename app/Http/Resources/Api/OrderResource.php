<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // Order ID
            'username' => $this->user->first_name . ' ' . $this->user->last_name, // Concatenate first name and last name
            'total' => $this->total, // Total amount
            'status' => $this->status, // Order status
            'address' => $this->address, // Shipping address
            'items' => OrderItemResource::collection($this->items) // Using OrderItemResource for items
        ];
    }
}
