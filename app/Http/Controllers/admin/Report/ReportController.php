<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function pageReport(Request $request)
    {
        $filter = $this->resolveReportFilter($request);
        $activeTab = $request->query('tab', 'reservations');

        // All Products and Categories (Full Catalog)
        $productsQuery = Product::with('category');
        $categoriesQuery = Category::withCount('products');
        
        // Orders and Reservations (Filtered by Date)
        $ordersQuery = Order::with('items.product')->orderBy('created_at', 'desc');
        $orderItemsQuery = OrderItem::with('product');
        $reservationsQuery = Reservation::orderBy('created_at', 'desc');

        $this->applyCreatedAtFilter($ordersQuery, 'created_at', $filter);
        $this->applyCreatedAtFilter($orderItemsQuery, 'created_at', $filter);
        $this->applyDateColumnFilter($reservationsQuery, 'date', $filter);

        $products     = $productsQuery->get();
        $categories   = $categoriesQuery->get();
        $orders       = $ordersQuery->get();
        $orderItems   = $orderItemsQuery->get();
        $reservations = $reservationsQuery->get();

        // Totals for the CURRENT FILTERED PERIOD
        $completedOrders = $orders->where('status', 'completed');
        $grandTotal = $completedOrders->sum('total_amount');

        // REAL-TIME Statistics (Independent of filter)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $todayOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->get();
        $monthOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->get();

        $todayTotal = $todayOrders->sum('total_amount');
        $todayCount = $todayOrders->count();
        $monthlySalesTotal = $monthOrders->sum('total_amount');
        $monthlyCount = $monthOrders->count();
        $dailySalesTotal = $todayTotal; // For compatibility with existing view variable name

        $averageSale = (float) $completedOrders->avg('total_amount');

        $salesByDate = $completedOrders
            ->groupBy(fn($o) => Carbon::parse($o->created_at)->format('Y-m-d'))
            ->sortKeysDesc();

        return view('Admin.Reports.report', compact(
            'products',
            'categories',
            'orders',
            'orderItems',
            'reservations',
            'completedOrders',
            'dailySalesTotal',
            'monthlySalesTotal',
            'grandTotal',
            'todayTotal',
            'todayCount',
            'monthlyCount',
            'averageSale',
            'salesByDate',
            'filter',
            'activeTab',
        ));
    }


    public function exportExcel(Request $request)
    {
        $type = $request->get('type', 'all');
        $filter = $this->resolveReportFilter($request);

        $rows    = [];
        $headers = [];

        switch ($type) {
            case 'reservations':
                $headers = ['ID', 'Full Name', 'Phone', 'Date', 'Time', 'Table'];
                $query = Reservation::orderBy('created_at', 'desc');
                $this->applyDateColumnFilter($query, 'date', $filter);
                foreach ($query->get() as $r) {
                    $rows[] = [$r->id, $r->full_name, $r->phone_number, $r->date, $r->time, $r->table_id];
                }
                break;

            case 'orders':
                $headers = ['ID', 'Items', 'Status', 'Total Amount', 'Date'];
                $query = Order::with('items.product')->orderBy('created_at', 'desc');
                $this->applyCreatedAtFilter($query, 'created_at', $filter);
                foreach ($query->get() as $o) {
                    $itemsList = $o->items->map(fn($i) => ($i->name ?? $i->product?->name ?? 'Unknown') . ' x' . $i->quantity)->join(', ');
                    $rows[] = [$o->id, $itemsList ?: '—', $o->status, $o->total_amount, $o->created_at->format('d M Y h:i A')];
                }
                break;

            case 'products':
                $headers = ['ID', 'Name', 'Category', 'Price', 'Cost', 'Qty'];
                $query = Product::with('category');
                foreach ($query->get() as $p) {
                    $rows[] = [$p->name, $p->category?->name ?? 'N/A', $p->price, $p->cost ?? 0, $p->qty ?? 'N/A'];
                }
                break;

            case 'categories':
                $headers = ['ID', 'Name', 'Code', 'Total Products', 'Created At'];
                $query = Category::withCount('products');
                foreach ($query->get() as $c) {
                    $rows[] = [$c->id, $c->name, $c->code ?? '—', $c->products_count, $c->created_at->format('d M Y')];
                }
                break;

            case 'sales':
                $headers = ['Date', 'Order ID', 'Items', 'Time', 'Amount'];
                $query = Order::with('items.product')->where('status', 'completed')->orderBy('created_at', 'desc');
                $this->applyCreatedAtFilter($query, 'created_at', $filter);
                $completed = $query->get();
                foreach ($completed as $o) {
                    $itemsList = $o->items->map(fn($i) => ($i->name ?? $i->product?->name ?? 'Unknown') . ' x' . $i->quantity)->join(', ');
                    $rows[] = [
                        Carbon::parse($o->created_at)->format('d M Y'),
                        '#' . $o->id,
                        $itemsList ?: '—',
                        Carbon::parse($o->created_at)->format('h:i A'),
                        number_format($o->total_amount ?? 0, 2),
                    ];
                }
                $rows[] = ['', '', '', 'GRAND TOTAL', number_format($completed->sum('total_amount'), 2)];
                break;

            default:
                $headers = ['Section', 'ID', 'Col1', 'Col2', 'Col3', 'Col4'];
                $reservationsQuery = Reservation::query();
                $this->applyDateColumnFilter($reservationsQuery, 'date', $filter);
                foreach ($reservationsQuery->get() as $r) {
                    $rows[] = ['Reservation', $r->id, $r->full_name, $r->phone_number, $r->date . ' ' . $r->time, 'Table ' . $r->table_id];
                }
                $ordersQuery = Order::with('items.product');
                $this->applyCreatedAtFilter($ordersQuery, 'created_at', $filter);
                foreach ($ordersQuery->get() as $o) {
                    $itemsList = $o->items->map(fn($i) => ($i->name ?? $i->product?->name ?? 'Unknown') . ' x' . $i->quantity)->join(', ');
                    $rows[] = ['Order', $o->id, $itemsList ?: '—', $o->status, '$' . number_format($o->total_amount, 2), $o->created_at->format('d M Y')];
                }
                $productsQuery = Product::with('category');
                $this->applyCreatedAtFilter($productsQuery, 'created_at', $filter);
                foreach ($productsQuery->get() as $p) {
                    $rows[] = ['Product', $p->id, $p->name, $p->category?->name ?? 'N/A', '$' . number_format($p->price, 2), $p->qty ?? 'N/A'];
                }
                $categoriesQuery = Category::withCount('products');
                $this->applyCreatedAtFilter($categoriesQuery, 'created_at', $filter);
                foreach ($categoriesQuery->get() as $c) {
                    $rows[] = ['Category', $c->id, $c->name, $c->products_count . ' items', $c->created_at->format('d M Y'), ''];
                }
                break;
        }

        $filename = 'fastbite-report-' . $type . '-' . now()->format('Ymd-His') . '.csv';

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


    public function exportPdf(Request $request)
    {
        $type = $request->get('type', 'all');
        $filter = $this->resolveReportFilter($request);

        $reservationsQuery = Reservation::orderBy('created_at', 'desc');
        $ordersQuery = Order::with('items.product')->orderBy('created_at', 'desc');
        $productsQuery = Product::with('category');
        $categoriesQuery = Category::withCount('products');

        $this->applyDateColumnFilter($reservationsQuery, 'date', $filter);
        $this->applyCreatedAtFilter($ordersQuery, 'created_at', $filter);

        $data = [
            'type'         => $type,
            'generatedAt'  => now()->format('d M Y, h:i A'),
            'reservations' => in_array($type, ['all', 'reservations']) ? $reservationsQuery->get() : collect(),
            'orders'       => in_array($type, ['all', 'orders'])       ? $ordersQuery->get()       : collect(),
            'products'     => in_array($type, ['all', 'products'])     ? $productsQuery->get()     : collect(),
            'categories'   => in_array($type, ['all', 'categories'])   ? $categoriesQuery->get()   : collect(),
        ];

        $filename = 'fastbite-report-' . $type . '-' . now()->format('Ymd-His') . '.pdf';

        return Pdf::loadView('Admin.Reports.report_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }

    protected function resolveReportFilter(Request $request): array
    {
        $mode = $request->query('report_mode', 'day');
        $mode = in_array($mode, ['day', 'month'], true) ? $mode : 'day';

        if ($mode === 'month') {
            $monthInput = $request->query('report_month', now()->format('Y-m'));

            try {
                $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
            } catch (\Throwable $e) {
                $month = now()->startOfMonth();
                $monthInput = $month->format('Y-m');
            }

            return [
                'mode' => 'month',
                'report_date' => $month->toDateString(),
                'report_month' => $monthInput,
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
                'heading' => 'Monthly Report',
                'label' => $month->format('F Y'),
                'description' => 'Showing all records for ' . $month->format('F Y') . '.',
            ];
        }

        $dateInput = $request->query('report_date', now()->toDateString());

        try {
            $date = Carbon::parse($dateInput)->startOfDay();
        } catch (\Throwable $e) {
            $date = now()->startOfDay();
            $dateInput = $date->toDateString();
        }

        return [
            'mode' => 'day',
            'report_date' => $dateInput,
            'report_month' => $date->format('Y-m'),
            'start' => $date->copy()->startOfDay(),
            'end' => $date->copy()->endOfDay(),
            'heading' => 'Daily Report',
            'label' => $date->format('d F Y'),
            'description' => 'Showing all records for ' . $date->format('d F Y') . '.',
        ];
    }

    protected function applyCreatedAtFilter($query, string $column, array $filter): void
    {
        $query->whereBetween($column, [$filter['start'], $filter['end']]);
    }

    protected function applyDateColumnFilter($query, string $column, array $filter): void
    {
        if ($filter['mode'] === 'month') {
            $query->whereBetween($column, [
                $filter['start']->toDateString(),
                $filter['end']->toDateString(),
            ]);

            return;
        }

        $query->whereDate($column, $filter['start']->toDateString());
    }
}
