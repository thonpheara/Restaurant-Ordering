<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class UserDashboardController extends Controller
{
    public function pageDashboard()
    {
        // Cache expensive queries for 5 minutes
        $totalProducts = Cache::remember('dashboard.total_products', 300, function () {
            return Product::count();
        });

        $totalCategories = Cache::remember('dashboard.total_categories', 300, function () {
            return Category::count();
        });

        $totalTables = Cache::remember('dashboard.total_tables', 300, function () {
            return Reservation::select('table_id')->distinct()->count('table_id');
        });

        $totalUsers = Cache::remember('dashboard.total_users', 300, function () {
            return DB::table('logins')->count();
        });

        $totalRevenue = Cache::remember('dashboard.total_revenue', 300, function () {
            return Order::sum('total_amount');
        });

        $recentProducts = Cache::remember('dashboard.recent_products', 300, function () {
            return Product::with('category')->latest()->take(5)->get();
        });

        // Cache sales chart data (last 30 days) - more expensive query
        $salesData = Cache::remember('dashboard.sales_chart', 600, function () { // 10 minutes
            $start = Carbon::now()->subDays(29)->startOfDay();
            $end   = Carbon::now()->endOfDay();

            $allDays = collect();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $allDays[$d->format('d M')] = 0;
            }

            $salesRaw = Order::selectRaw("DATE(created_at) as day_date, SUM(total_amount) as total")
                ->whereBetween('created_at', [$start, $end])
                ->groupByRaw("DATE(created_at)")
                ->orderByRaw("DATE(created_at)")
                ->get()
                ->mapWithKeys(function ($row) {
                    $label = Carbon::parse($row->day_date)->format('d M');
                    return [$label => (float) $row->total];
                });

            $merged = $allDays->merge($salesRaw);

            return [
                'labels' => $merged->keys(),
                'values' => $merged->values()->map(fn($v) => round((float)$v, 2))
            ];
        });

        $salesLabels = $salesData['labels'];
        $salesValues = $salesData['values'];

        // Revenue and orders change calculations
        $todayRevenue     = Order::whereDate('created_at', today())->sum('total_amount');
        $yesterdayRevenue = Order::whereDate('created_at', today()->subDay())->sum('total_amount');
        $revenueChange    = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : ($todayRevenue > 0 ? 100 : 0);

        $todayOrders     = Order::whereDate('created_at', today())->count();
        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())->count();
        $ordersChange    = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1)
            : ($todayOrders > 0 ? 100 : 0);

        return view('User.dashboard', compact(
            'totalProducts', 'totalCategories', 'totalTables',
            'totalUsers', 'totalRevenue', 'recentProducts',
            'salesLabels', 'salesValues',
            'todayRevenue', 'revenueChange',
            'todayOrders',  'ordersChange'
        ));
    }
}