<!DOCTYPE html>
<html>

<head>
    <title>Warranty Invoice</title>
    <style>
        /* 1. Set Page Size and Margins */
        @page {
            size: letter portrait;
            margin: 72pt 90pt 72pt 90pt;
            /* Top, Right, Bottom, Left */
        }

        /* 2. Reset Body to prevent double-margins */
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        /* 3. Ensure table doesn't overflow margins */
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Forces table to stay within margins */
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            word-wrap: break-word;
            /* Prevents long text from breaking layout */
        }

        .table th {
            background: #f5f5f5;
        }

        .right {
            text-align: right;
        }

        .footer-total {
            margin-top: 20px;
            text-align: right;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Warranty Monitoring System Invoice</h2>
        <p>Purchase Date: {{ $date }}</p>
        <p>Customer Name: {{ $email }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 40%;">Product</th>
                <th style="width: 20%;">Expiry Date</th>
                <th style="width: 25%;">Serial Number</th>
                <th style="width: 15%;">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($warranties as $warranty)
                <tr>
                    <td>{{ $warranty->product->name }}</td>
                    <td>{{ $warranty->expiry_date->format('M d, y') }}</td>
                    <td>{{ $warranty->serial_number }}</td>
                    <td class="right">₱{{ number_format($warranty->purchase_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-total">
        <strong>Total: ₱{{ number_format($total, 2) }}</strong>
    </div>

</body>

</html>
