<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Reservation;
use App\Services\ResourceDeletionRequestService;

class MenuController extends Controller
{
    const LOW_STOCK = 2;

    
    public function pageMenu()
    {
        $categories = Category::withCount('products')
            ->get()
            ->map(fn($cat) => [
                'id'    => $cat->id,
                'name'  => $cat->name,
                'count' => $cat->products_count,
            ]);

        $products = Product::with('category')
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'price'    => (float) $p->price,
                'category' => $p->category->name ?? 'Uncategorized',
                'image'    => $p->images[0] ?? null,
                'qty'      => (int) ($p->qty ?? 0),
            ]);

        $ordersQuery = Order::with(['items.product', 'user']);
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            $ordersQuery->where('user_id', $user->id);
        }

        $orders = $ordersQuery->latest()->get()
            ->each(function (Order $order) {
                $order->syncAutoStatus();
                $this->triggerCompletionAlerts($order);
            });

        $ordersJson = $orders->map(function ($o) {
            return [
                'id'           => $o->id,
                'table_number' => $o->table_number,
                'reservation_name' => $o->reservation_name,
                'tracking_target' => $o->tracking_target,
                'status'       => $o->status ?? 'pending',
                'handled_by'   => $o->user?->username ?? 'Unknown Staff',
                'total_amount' => $o->total_amount,
                'prep_time_min' => $o->prep_time_min,
                'prep_time_max' => $o->prep_time_max,
                'tracking_window' => $o->tracking_window,
                'estimated_ready_at' => $o->estimated_ready_at?->toIso8601String(),
                'created_at_iso' => $o->created_at?->toIso8601String(),
                'created_at'   => $o->created_at->format('d M Y'),
                'time'         => $o->created_at->format('h:i A'),
                'items'        => $o->items->map(function ($i, $idx) {
                    $product = $i->product;
                    return [
                        'uid'      => $i->id ?? $idx,
                        'name'     => $product?->name ?? $i->name ?? 'Deleted Product',
                        'quantity' => $i->quantity,
                        'image'    => $product?->images[0] ?? null,
                    ];
                })->values()->toArray(),
            ];
        })->values();

        $lowStockProducts   = Product::where('qty', '<=', self::LOW_STOCK)->where('qty', '>', 0)->get();
        $outOfStockProducts = Product::where('qty', '<=', 0)->get();

        $reservations = Reservation::latest()
            ->take(100)
            ->get()
            ->map(fn ($r) => [
                'name'  => $r->full_name,
                'table' => $r->table_id,
            ]);

        return view('Admin.Menus.menu', compact(
            'categories', 'products', 'orders', 'ordersJson',
            'lowStockProducts', 'outOfStockProducts',
            'reservations'
        ));
    }

    
    public function storeOrder(Request $request)
    {
        try {
            $isUserWebOrder = $request->input('source') === 'userweb';

            $validated = $request->validate([
                'table_number' => ['nullable', 'string', 'max:50'],
                'reservation_name' => ['nullable', 'string', 'max:255'],
                'total' => ['required', 'numeric', 'min:0'],
                'prep_time_min' => [$isUserWebOrder ? 'nullable' : 'required', 'integer', 'min:1', 'max:240'],
                'prep_time_max' => [$isUserWebOrder ? 'nullable' : 'required', 'integer', 'min:1', 'max:240', 'gte:prep_time_min'],
                'cart' => ['required', 'array', 'min:1'],
                'cart.*.id' => ['required', 'integer', 'exists:products,id'],
                'cart.*.name' => ['required', 'string'],
                'cart.*.price' => ['required', 'numeric', 'min:0'],
                'cart.*.qty' => ['required', 'integer', 'min:1'],
            ]);

            $prepTimeMin = $isUserWebOrder ? 5 : (int) $validated['prep_time_min'];
            $prepTimeMax = $isUserWebOrder ? 10 : (int) $validated['prep_time_max'];

            DB::beginTransaction();

            $tableNumber = $validated['table_number']
                ?? $validated['reservation_name']
                ?? 'Walk-in';

            $order = Order::create([
                'table_number' => $tableNumber,
                'reservation_name' => $validated['reservation_name'] ?? null,
                'total_amount' => $validated['total'],
                'status'       => 'pending',
                'user_id'      => auth()->id(),
                'prep_time_min' => $prepTimeMin,
                'prep_time_max' => $prepTimeMax,
                'estimated_ready_at' => now()->addMinutes($prepTimeMax),
            ]);

            $alertProducts = [];
            $itemLines     = [];

            foreach ($validated['cart'] as $item) {
                
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['qty'],
                    'price'      => $item['price'],
                    'name'       => $item['name'],
                ]);

                $itemLines[] = "• {$item['name']} x{$item['qty']} = \${$item['price']}";

                // Decrement stock
                $product = Product::find($item['id']);
                if ($product) {
                    $product->qty = max(0, ($product->qty ?? 0) - $item['qty']);
                    $product->save();

                    if ($product->qty <= self::LOW_STOCK) {
                        $alertProducts[] = $product;
                    }
                }
            }

            DB::commit();

            $order->syncAutoStatus();

            // ── Telegram: Stock alerts ──
            foreach ($alertProducts as $product) {
                $this->sendStockAlert($product);
            }

            return response()->json([
                'message'      => 'Order saved successfully',
                'order_id'     => $order->id,
                'reservation_name' => $order->reservation_name,
                'prep_time_min' => $order->prep_time_min,
                'prep_time_max' => $order->prep_time_max,
                'tracking_window' => $order->tracking_window,
                'estimated_ready_at' => $order->estimated_ready_at?->toIso8601String(),
                'stock_alerts' => collect($alertProducts)->map(fn($p) => [
                    'name' => $p->name,
                    'qty'  => $p->qty,
                ]),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,processing,completed,cancelled',
                'prep_time_min' => 'nullable|integer|min:1|max:240',
                'prep_time_max' => 'nullable|integer|min:1|max:240|gte:prep_time_min',
            ]);

            $order = Order::findOrFail($id);
            $user = auth()->user();

            if (!$user->isSuperAdmin() && $order->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $old   = $order->status;
            $order->status = $request->status;

            if ($request->filled('prep_time_min')) {
                $order->prep_time_min = (int) $request->prep_time_min;
            }

            if ($request->filled('prep_time_max')) {
                $order->prep_time_max = (int) $request->prep_time_max;
            }

            if ($order->prep_time_max) {
                $order->estimated_ready_at = Carbon::parse($order->created_at)->addMinutes((int) $order->prep_time_max);
            }

            $order->save();
            $order->syncAutoStatus();

            if ($old !== 'completed') {
                $this->triggerCompletionAlerts($order);
            }

            return response()->json([
                'message' => 'Status updated',
                'status'  => $order->status,
                'prep_time_min' => $order->prep_time_min,
                'prep_time_max' => $order->prep_time_max,
                'tracking_window' => $order->tracking_window,
                'estimated_ready_at' => $order->estimated_ready_at?->toIso8601String(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Invalid status value.'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
    public function destroyOrder($id)
    {
        try {
            $order = Order::with('items')->findOrFail($id);
            $user = auth()->user();

            if (!$user->isSuperAdmin() && $order->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $result = app(ResourceDeletionRequestService::class)->submit([
                'requester_id' => $user->id,
                'resource_type' => 'order',
                'resource_id' => $order->id,
                'resource_name' => 'Order #' . $order->id,
                'payload' => ['context' => $order->table_number ? 'Table ' . $order->table_number : 'Order record'],
                'reason' => null,
            ]);

            if (!$result['created']) {
                return response()->json(['error' => 'A pending deletion request already exists for this order.'], 422);
            }

            return response()->json([
                'message' => 'Order deletion request submitted for admin approval.',
                'request_submitted' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
    private function sendStockAlert(Product $product): void
    {
        $emoji   = $product->qty <= 0 ? '🚫' : '⚠️';
        $status  = $product->qty <= 0 ? '🚫 OUT OF STOCK' : '⚠️ LOW STOCK';

        $this->sendTelegram(implode("\n", [
            "{$emoji} *FastBite Stock Alert*",
            "━━━━━━━━━━━━━━━━━━━━",
            "*Product:* {$product->name}",
            "*Status:* {$status}",
            "*Remaining:* {$product->qty} units",
            "━━━━━━━━━━━━━━━━━━━━",
            "_Please restock immediately._",
        ]));
    }

    private function triggerCompletionAlerts(Order $order): void
    {
        if (strtolower((string) $order->status) !== 'completed') {
            return;
        }

        $cacheKey = 'order_completed_alert_sent_' . $order->id;

        if (!Cache::add($cacheKey, true, now()->addDays(30))) {
            return;
        }

        \App\Jobs\SendOrderNotification::dispatch($order);

        $this->sendTelegram(implode("\n", [
            "🟢 *Order Completed — FastBite*",
            "━━━━━━━━━━━━━━━━━━━━",
            "*Order ID:* #" . $order->id,
            "*Table:* " . ($order->table_number ?: 'Walk-in'),
            "*Reservation:* " . ($order->reservation_name ?: 'Walk-in'),
            "*Total:* \$" . number_format((float) $order->total_amount, 2),
            "━━━━━━━━━━━━━━━━━━━━",
            "_Order is ready / completed._",
        ]));
    }

    
    private function sendTelegram(string $message): void
    {
        $botToken = Setting::get('telegram_bot_token') ?? env('TELEGRAM_BOT_TOKEN');
        $chatId   = Setting::get('telegram_chat_id')   ?? env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram not configured — skipping alert.');
            return;
        }

        try {
            $response = Http::timeout(5)->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id'    => $chatId,
                    'text'       => $message,
                    'parse_mode' => 'Markdown',
                ]
            );

            if (!$response->successful()) {
                Log::warning('Telegram send failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Telegram exception: ' . $e->getMessage());
        }
    }
}
