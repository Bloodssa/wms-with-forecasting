<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warranty Report</title>
    <link rel="stylesheet" href="{{ public_path('report.css') }}">
</head>
<body>
    <div class="header">
        <h1>Warranty Report</h1>
        <p>Period: {{ $periodLabel }} | Generated on: {{ now()->format('M d, Y H:i A') }}</p>
    </div>
    <table class="stats-table">
        <tr>
            <td class="stats-box">
                <span class="label">Active Warranties</span>
                <span class="value">{{ number_format($stats['activeWarranty']) }}</span>
            </td>
            <td class="stats-box">
                <span class="label">Total Inquiries</span>
                <span class="value">{{ number_format($stats['warrantyClaimCount']) }}</span>
            </td>
            <td class="stats-box">
                <span class="label">Resolved Cases</span>
                <span class="value">{{ number_format($stats['resolvedInquiry']) }}</span>
            </td>
        </tr>
    </table>
    <div class="section-title">Inquiry Distribution ({{ $periodLabel }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Date / Month</th>
                <th style="width: 50%;">Visual</th>
                <th class="text-right" style="width: 20%;">Total Inquiry</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maxInquiry = $chartsData->inquiries->data->max() ?: 1;
            @endphp
            @foreach($chartsData->inquiries->labels as $index => $label)
                @php 
                    $count = $chartsData->inquiries->data[$index]; 
                    $width = ($count / $maxInquiry) * 100;
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>
                        <div style="background: #f3f4f6; width: 100%; height: 10px; border-radius: 2px;">
                            <div style="background: #111827; width: {{ $width }}%; height: 10px; border-radius: 2px;"></div>
                        </div>
                    </td>
                    <td class="text-right"><strong>{{ $count }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="section-title" style="margin-top: 20px;">Most Reported Products</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-right">Report Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chartsData->reportedProducts->labels as $index => $label)
            <tr>
                <td>{{ $label }}</td>
                <td class="text-right"><strong>{{ $chartsData->reportedProducts->data[$index] }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="section-title" style="margin-top: 20px;">Near-Expiry Warranties</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Customer</th>
                <th class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($nearExpiryWarranties as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->user->name }}</td>
                    <td class="text-right" style="color: #b91c1c;">
                        {{ (int) now()->diffInDays($item->expiry_date) }} days left
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center; color: #666;">No near-expiry records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>