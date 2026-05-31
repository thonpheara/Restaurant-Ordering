<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FastBite Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        /* Updated font: modern, clean, highly readable sans-serif stack */
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        font-size: 12px;
        color: #000000;
        background: #ffffff;
        padding: 30px;
        line-height: 1.4;
    }
    /* Improve font rendering */
    .header, .section-title, table, .footer, .badge {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    /* Monochrome palette: only black, white, grays */
    .header {
        text-align: center;
        border-bottom: 3px solid #000000;
        padding-bottom: 16px;
        margin-bottom: 24px;
    }
    .header h1 {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.3px;
        color: #000000;
        font-family: inherit;
    }
    /* No orange span — keep black and white */
    .header h1 span {
        color: #000000;
        font-weight: 800;
    }
    .header p {
        color: #444444;
        font-size: 11px;
        margin-top: 5px;
        letter-spacing: 0.2px;
    }
    .section {
        margin-bottom: 28px;
        page-break-inside: avoid;
    }
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #000000;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-left: 4px solid #000000;
        padding-left: 12px;
        margin-bottom: 12px;
        font-family: inherit;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11.5px;
        font-family: inherit;
    }
    thead {
        background-color: #000000;
        color: #ffffff;
    }
    thead th {
        padding: 9px 12px;
        text-align: left;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-size: 10px;
        border-right: 1px solid #333333;
        font-family: inherit;
    }
    thead th:last-child {
        border-right: none;
    }
    tbody tr {
        border-bottom: 1px solid #cccccc;
        transition: background 0.1s ease;
    }
    tbody tr:nth-child(even) {
        background-color: #f5f5f5;
    }
    tbody td {
        padding: 8px 12px;
        color: #000000;
        border-right: 1px solid #e0e0e0;
        font-family: inherit;
    }
    tbody td:last-child {
        border-right: none;
    }
    /* Badge styles — strictly monochrome: black/white/gray backgrounds */
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 30px;
        font-size: 10px;
        font-weight: 600;
        background: #eaeaea;
        color: #000000;
        border: 1px solid #aaaaaa;
        letter-spacing: 0.2px;
        font-family: inherit;
    }
    .badge-orange, .badge-green, .badge-yellow, .badge-blue, .badge-red {
        /* override any color variations: force monochrome */
        background: #e0e0e0;
        color: #000000;
        border: 1px solid #777777;
    }
    /* Additional specific badge handling for all grayscale */
    .badge-green { background: #d4d4d4; color: #000000; border: 1px solid #666; }
    .badge-yellow { background: #cbcbcb; color: #000000; border: 1px solid #666; }
    .badge-blue { background: #cecece; color: #000000; border: 1px solid #666; }
    .badge-red { background: #b0b0b0; color: #000000; border: 1px solid #555; }
    .badge-orange { background: #d8d8d8; color: #000000; border: 1px solid #666; }

    .footer {
        text-align: center;
        margin-top: 32px;
        padding-top: 12px;
        border-top: 1px solid #cccccc;
        font-size: 10px;
        color: #555555;
        font-family: inherit;
        letter-spacing: 0.2px;
    }
    @media print {
        body { padding: 15px; }
        .section { page-break-inside: avoid; }
        thead { background-color: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge { background: #ddd !important; border: 1px solid #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>

<div class="header">
    <h1><span>Fast</span>Bite — Report</h1>
    <p>Generated: {{ $generatedAt }} &nbsp;|&nbsp; Type: {{ ucfirst($type) }}</p>
</div>

<!-- RESERVATIONS -->
@if($reservations->count())
<div class="section">
    <div class="section-title">Reservations ({{ $reservations->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Date & Time</th>
                <th>Table</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->full_name }}</td>
                <td>{{ $r->phone_number }}</td>
                <td>{{ $r->date }} {{ $r->time }}</td>
                <td><span class="badge badge-orange">Table {{ $r->table_id }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- ORDERS -->
@if($orders->count())
<div class="section">
    <div class="section-title">Orders ({{ $orders->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
            @php
                // keep badge classes but they will render grayscale due to forced styles
                $badgeClass = match(strtolower($o->status)) {
                    'completed' => 'badge-green',
                    'pending'   => 'badge-yellow',
                    'confirmed' => 'badge-blue',
                    'cancelled' => 'badge-red',
                    default     => 'badge-orange',
                };
            @endphp
            <tr>
                <td>{{ $o->id }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($o->status) }}</span></td>
                <td>{{ number_format($o->total_amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d M Y, h:i A') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- PRODUCTS -->
@if($products->count())
<div class="section">
    <div class="section-title">Products ({{ $products->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->name }}</td>
                <td><span class="badge badge-orange">{{ $p->category?->name ?? 'N/A' }}</span></td>
                <td>{{ number_format($p->price, 2) }}</td>
                <td>{{ $p->qty ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- CATEGORIES -->
@if($categories->count())
<div class="section">
    <div class="section-title">Categories ({{ $categories->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Products</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->name }}</td>
                <td><span class="badge badge-green">{{ $c->products_count }} items</span></td>
                <td>{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">© {{ date('Y') }} FastBite Restaurant Ordering System &nbsp;|&nbsp; Confidential</div>

</body>
</html>