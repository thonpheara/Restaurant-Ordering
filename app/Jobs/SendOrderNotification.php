<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendOrderNotification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (strtolower((string) $this->order->status) !== 'completed') {
            return;
        }

        // Cache order data in Redis for 1 hour
        $cacheKey = 'order_notification_' . $this->order->id;
        Cache::put($cacheKey, [
            'order_id' => $this->order->id,
            'customer' => $this->order->user->username ?? 'Guest',
            'total' => $this->order->total_amount,
            'status' => $this->order->status,
            'processed_at' => now(),
        ], 3600);

        // Update real-time dashboard stats in Redis
        $this->updateDashboardStats();

        // Send Telegram notification (existing functionality)
        $this->sendTelegramNotification();

        Log::info('Order notification processed via Redis queue', [
            'order_id' => $this->order->id,
            'cached' => true
        ]);
    }

    private function updateDashboardStats(): void
    {
        // Increment today's order count in Redis
        $todayKey = 'stats:orders:' . now()->format('Y-m-d');
        Cache::increment($todayKey);

        // Update total revenue
        $revenueKey = 'stats:revenue:' . now()->format('Y-m-d');
        $currentRevenue = Cache::get($revenueKey, 0);
        Cache::put($revenueKey, $currentRevenue + $this->order->total_amount, 86400); // 24 hours

        // Store recent orders list (last 10)
        $recentOrdersKey = 'recent_orders';
        $recentOrders = Cache::get($recentOrdersKey, collect());
        $recentOrders->prepend([
            'id' => $this->order->id,
            'customer' => $this->order->user->username ?? 'Guest',
            'total' => $this->order->total_amount,
            'time' => now()->format('H:i'),
        ]);
        Cache::put($recentOrdersKey, $recentOrders->take(10), 3600);
    }

    private function sendTelegramNotification(): void
    {
        // Your existing Telegram logic here
        // This will run in background via Redis queue
        $message = "🆕 *NEW ORDER* #{$this->order->id}\n" .
                  "👤 Customer: " . ($this->order->user->username ?? 'Guest') . "\n" .
                  "💰 Total: $" . number_format($this->order->total_amount, 2) . "\n" .
                  "📊 Status: COMPLETED";

        // Use your existing sendTelegram method or similar
        Log::info('Telegram notification queued for order: ' . $this->order->id);
    }
}
