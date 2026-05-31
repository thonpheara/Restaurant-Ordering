<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ResourceDeletionRequest;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResourceDeletionRequestService
{
    public function submit(array $attributes): array
    {
        $pendingRequest = ResourceDeletionRequest::query()
            ->where('resource_type', $attributes['resource_type'])
            ->where('resource_id', $attributes['resource_id'])
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return [
                'request' => $pendingRequest,
                'created' => false,
            ];
        }

        return [
            'request' => ResourceDeletionRequest::create($attributes),
            'created' => true,
        ];
    }

    public function approve(ResourceDeletionRequest $request, int $approvedBy): void
    {
        DB::transaction(function () use ($request, $approvedBy) {
            $this->deleteResource($request);

            $request->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);
        });
    }

    protected function deleteResource(ResourceDeletionRequest $request): void
    {
        match ($request->resource_type) {
            'product' => $this->deleteProduct($request->resource_id),
            'category' => $this->deleteCategory($request->resource_id),
            'order' => $this->deleteOrder($request->resource_id),
            'reservation' => $this->deleteReservation($request->resource_id),
            default => null,
        };
    }

    protected function deleteProduct(?int $resourceId): void
    {
        $product = Product::find($resourceId);

        if (!$product) {
            return;
        }

        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $image) {
                if ($image && Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $product->delete();
    }

    protected function deleteCategory(?int $resourceId): void
    {
        $category = Category::find($resourceId);

        if (!$category) {
            return;
        }

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
    }

    protected function deleteOrder(?int $resourceId): void
    {
        $order = Order::with('items')->find($resourceId);

        if (!$order) {
            return;
        }

        $order->items()->delete();
        $order->delete();
    }

    protected function deleteReservation(?int $resourceId): void
    {
        $reservation = Reservation::find($resourceId);

        if (!$reservation) {
            return;
        }

        $reservation->delete();
    }
}
