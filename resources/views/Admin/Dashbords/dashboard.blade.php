@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--admin-accent); border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    [x-cloak] { display: none !important; }

    .stat-card {
        background: var(--admin-card-bg);
        border: 1px solid var(--admin-border);
        transition: all 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: var(--admin-accent);
    }

    .metric-icon {
        background: linear-gradient(135deg, var(--admin-accent), var(--orange-light));
        color: white;
    }

    .chart-container {
        position: relative;
        height: 250px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<title>FastBite | Dashboard</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]" x-data="dashboardData()" x-init="init()">
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">

        {{-- Sidebar (owns its own mobile header + overlay + Alpine open state) --}}
        @include('components.asidebar')

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">

            {{-- Header Card --}}
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mb-6 mt-3 md:mt-0">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-[var(--admin-accent)]/10 text-[var(--admin-accent)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">Dashboard Overview</h2>
                            <p class="text-[var(--admin-text-secondary)] text-sm">Welcome back, {{ Auth::user()->username ?? 'Admin' }}! Here's a summary of your restaurant's performance.</p>
                        </div>
                    </div>
                    <div class="bg-[var(--admin-bg-primary)] rounded-xl px-4 py-2 text-sm text-[var(--admin-text-secondary)]">
                        <span>Last updated: </span>
                        <span class="font-semibold" x-text="currentTime"></span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!--Total Revenue -->
                <div class="stat-card rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl metric-icon flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[var(--admin-text-secondary)]">Total Revenue</p>
                            <p class="text-2xl font-bold text-[var(--admin-text-primary)]">${{ number_format($totalRevenue, 2) }}</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">
                        +{{ $revenueChange }}% from yesterday
                    </span>
                </div>

                {{-- Today's Orders --}}
                <div class="stat-card rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl metric-icon flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[var(--admin-text-secondary)]">Today's Orders</p>
                            <p class="text-2xl font-bold text-[var(--admin-text-primary)]">{{ $todayOrders }}</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">
                        +{{ $ordersChange }}% from yesterday
                    </span>
                </div>

                {{-- Total Products --}}
                <div class="stat-card rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl metric-icon flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[var(--admin-text-secondary)]">Total Products</p>
                            <p class="text-2xl font-bold text-[var(--admin-text-primary)]">{{ $totalProducts }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-[var(--admin-text-secondary)]">Across {{ $totalCategories }} categories</p>
                </div>

                {{-- Total Users --}}
                <div class="stat-card rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl metric-icon flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[var(--admin-text-secondary)]">Total Users</p>
                            <p class="text-2xl font-bold text-[var(--admin-text-primary)]">{{ $totalUsers }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-[var(--admin-text-secondary)]">System administrators & staff</p>
                </div>

            </div>

            <!--Charts and Recent Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                {{-- Sales Chart --}}
                <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-[var(--admin-text-primary)]">Sales Overview</h3>
                            <p class="text-sm text-[var(--admin-text-secondary)]">Last 30 days performance</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-[var(--admin-accent)]"></div>
                            <span class="text-sm font-medium text-[var(--admin-text-secondary)]">Revenue</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

            <!-- Recent Products -->
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-[var(--admin-text-primary)]">Recent Products</h3>
                        <p class="text-sm text-[var(--admin-text-secondary)]">Latest additions to your menu</p>
                    </div>
                    <a href="{{ route('menu.index') }}"
                       class="text-sm font-bold text-[var(--admin-accent)] hover:opacity-80 transition tracking-wide uppercase">
                        Order now →
                    </a>
                </div>
                <div class="space-y-4 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                    @foreach($recentProducts as $product)
                    <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-orange-50/5 transition border border-transparent hover:border-[var(--admin-border)]">
                        <div class="w-12 h-12 rounded-xl bg-[var(--admin-bg-primary)] flex items-center justify-center overflow-hidden border border-[var(--admin-border)]">
                            @if($product->image)
                                <img src="/storage/{{ $product->image }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <svg class="w-6 h-6 text-[var(--admin-text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-[var(--admin-text-primary)]">{{ $product->name }}</h4>
                            <p class="text-sm text-[var(--admin-text-secondary)]">{{ $product->category->name ?? 'No Category' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-[var(--admin-accent)] text-lg">${{ number_format($product->price, 2) }}</p>
                            <p class="text-[10px] text-[var(--admin-text-secondary)] uppercase font-bold tracking-wider">{{ $product->qty }} in stock</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            </div>

             {{-- Quick Actions --}}
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 mb-6">
                <h3 class="text-lg font-bold text-[var(--admin-text-primary)] mb-6">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <a href="{{ route('products-form.index') }}"
                       class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[#EE6D3C] hover:bg-orange-50/10 transition group">
                        <div class="w-12 h-12 rounded-xl bg-[var(--admin-accent)] flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-[var(--admin-text-secondary)]">Add Product</span>
                    </a>

                    <a href="{{ route('addcategory.index') }}"
                       class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[#EE6D3C] hover:bg-orange-50/10 transition group">
                        <div class="w-12 h-12 rounded-xl bg-[#EE6D3C] flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-[var(--admin-text-secondary)]">Add Category</span>
                    </a>

                    <a href="{{ route('user.create') }}"
                       class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[#EE6D3C] hover:bg-orange-50/10 transition group">
                        <div class="w-12 h-12 rounded-xl bg-[#EE6D3C] flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-[var(--admin-text-secondary)]">Add User</span>
                    </a>

                    <a href="{{ route('report.index') }}"
                       class="flex flex-col items-center gap-3 p-4 rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[#EE6D3C] hover:bg-orange-50/10 transition group">
                        <div class="w-12 h-12 rounded-xl bg-[#EE6D3C] flex items-center justify-center group-hover:scale-110 transition">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-[var(--admin-text-secondary)]">View Reports</span>
                    </a>

                </div>
            </div>

        </main>
    </div>

    <script>
        function dashboardData() {
            return {
                currentTime: '',

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                    this.initChart();
                },

                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },

                initChart() {
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($salesLabels),
                            datasets: [{
                                label: 'Sales ($)',
                                data: @json($salesValues),
                                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-accent').trim(),
                                backgroundColor: 'rgba(244, 81, 30, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--admin-accent').trim(),
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(0,0,0,0.05)' },
                                    ticks: {
                                        callback: function(value) { return '$' + value; }
                                    }
                                },
                                x: { grid: { display: false } }
                            },
                            elements: { point: { hoverBorderWidth: 3 } }
                        }
                    });
                }
            }
        }
    </script>
</div>