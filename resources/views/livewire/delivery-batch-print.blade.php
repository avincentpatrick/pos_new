<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Slip</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            -webkit-print-color-adjust: exact; /* For printing background colors */
        }
        .ticket-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Two columns */
            grid-template-rows: repeat(2, 1fr); /* Two rows */
            height: 210mm; /* A4 height for landscape */
            width: 297mm; /* A4 width for landscape */
            margin: 0 auto;
            padding: 3mm; /* Reverted padding */
            box-sizing: border-box;
        }
        .ticket {
            border: 1px solid #ccc;
            padding: 7px; /* Reverted padding for better appearance */
            margin: 2px; /* Reduced margin */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start; /* Align content to start, not space-between */
            font-size: 12px; /* Increased font size */
            position: relative;
            height: 99mm; /* Reverted height */
            overflow: hidden; /* Hide overflowing content within the ticket */
        }
        .company-header {
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .company-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .company-header p {
            margin: 0;
            font-size: 10px;
        }
        .delivery-slip-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .info-table th, .info-table td {
            border: 1px solid #eee;
            padding: 2px 4px;
            text-align: left;
        }
        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px; /* Reverted margin */
        }
        .ticket-table th, .ticket-table td {
            border: 1px solid #eee;
            padding: 4px; /* Adjusted padding */
            text-align: left;
            font-size: 11px;  /* Increased font size */
            line-height: 1.2; /* Adjusted line height for readability */
        }
        .ticket-table th {
            background-color: #f5f5f5;
        }
        .consolidated-quantities, .total-collectibles {
            margin-top: 8px; /* Reverted margin */
            font-size: 11px;  /* Increased font size */
            line-height: 1.2; /* Adjusted line height for readability */
        }
        .consolidated-quantities p, .total-collectibles p {
            margin: 0;
        }
        .text-red-500 { color: #ef4444; }
        .text-green-500 { color: #22c55e; }
        .text-gray-500 { color: #6b7280; }
        .font-semibold { font-weight: 600; }
        .block { display: block; }

        @media print {
            @page {
                size: A4 landscape; /* Force landscape orientation */
            }
            html, body {
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box;
            }
            .ticket-container {
                display: grid; /* Ensure grid is applied in print media */
                grid-template-columns: repeat(2, 1fr); /* Two columns for landscape */
                grid-template-rows: repeat(2, 1fr); /* Two rows for landscape */
                height: auto; /* Let grid determine height */
                width: 297mm; /* A4 width for landscape */
                margin: 0;
                padding: 0; /* Removed padding for print */
            }
            .ticket {
                border: 1px solid #ccc;
                padding: 4px; /* Adjusted padding for print */
                margin: 1px; /* Adjusted margin for print */
                font-size: 11px;  /* Increased font size for print */
                height: 97mm; /* Adjusted height to fit 4 tickets */
                overflow: hidden; /* Hide overflowing content within the ticket */
                page-break-inside: avoid; /* Prevent ticket from breaking across pages */
            }
            .company-header {
                margin-bottom: 8px;
            }
            .company-header h2 {
                font-size: 15px;
            }
            .company-header p {
                font-size: 9px;
            }
            .delivery-slip-title {
                font-size: 16px;
                margin-bottom: 8px;
            }
            .info-table {
                font-size: 10px;
                margin-bottom: 4px;
            }
            .info-table th, .info-table td {
                padding: 1px 3px;
            }
            .ticket-table th, .ticket-table td {
                padding: 2px; /* Adjusted padding for print */
                font-size: 10px; /* Increased font size for print */
                line-height: 1.1; /* Adjusted line height for readability */
            }
            .consolidated-quantities, .total-collectibles {
                font-size: 10px; /* Increased font size for print */
                line-height: 1.1; /* Adjusted line height for readability */
            }
            .page-break { /* Re-added page-break for print media */
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    @php
        $batchesCount = count($deliveryBatches);
        $ticketsPerPage = 4;
        $pageCount = ceil($batchesCount / $ticketsPerPage);
    @endphp

    @for ($page = 0; $page < $pageCount; $page++)
        <div class="ticket-container">
            @for ($i = 0; $i < $ticketsPerPage; $i++)
                @php
                    $batchIndex = ($page * $ticketsPerPage) + $i;
                    $batch = $deliveryBatches[$batchIndex] ?? null;
                @endphp

                @if ($batch)
                    <div class="ticket" id="ticket-{{ $batch->id }}">
                        <div class="company-header">
                            <h2>ELMACRIS ICE TRADING</h2>
                            <p>Solid Road, Brgy. San Manuel, 5300 Puerto Princesa City (Capital), Palawan</p>
                        </div>
                        <div class="delivery-slip-title">DELIVERY SLIP</div>

                        <table class="info-table">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $batch->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($batch->created_at)->format('F d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="info-table">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Helper</th>
                                    <th>Route</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $batch->driver->driver_name ?? 'N/A' }}</td>
                                    <td>{{ $batch->helper->helper_name ?? 'N/A' }}</td>
                                    <td>{{ $batch->route->route_name ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="flex-grow">
                            <table class="ticket-table">
                                <thead>
                                    <tr>
                                        <th>CLIENT NAME</th>
                                        <th>QTY</th>
                                        <th>UNIT</th>
                                        <th>ACTUAL</th>
                                        <th>REMARKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allBatchTransactions = $batch->deliveries->map(fn($d) => $d->transaction)->filter()->unique('id');
                                    @endphp
                                    @forelse ($allBatchTransactions as $transaction)
                                    @php
                                        $salesCount = $transaction->sales->count();
                                        $firstPayment = $transaction->payments->first();
                                        $paymentMethodId = $firstPayment->payment_method_id ?? null;
                                        $isFullyPaid = $transaction->remaining_balance <= 0;
                                        $firstPaymentMethodName = $firstPayment && $firstPayment->paymentMethod ? $firstPayment->paymentMethod->payment_method_name : 'N/A';

                                        $paymentMethodDisplay = '';
                                        if ($paymentMethodId === 3) { // Credit
                                            $paymentMethodDisplay = '<span>' . $firstPaymentMethodName . '</span>';
                                        } elseif ($paymentMethodId === 7) { // COD
                                            if (!$isFullyPaid) {
                                                $paymentMethodDisplay = '<span>' . $firstPaymentMethodName . '<span class="block text-red-500"><small><i>(-₱' . number_format($transaction->remaining_balance, 2) . ')</i></small></span></span>';
                                            } else {
                                                $paymentMethodDisplay = '<span>Paid</span>';
                                            }
                                        } else { // Other payment methods (assuming fully paid if not COD/Credit)
                                            $paymentMethodDisplay = '<span>Paid</span>';
                                            }
                                        @endphp
                                        @foreach ($transaction->sales as $sale)
                                            <tr>
                                                @if ($loop->first)
                                                    <td rowspan="{{ $salesCount }}">{{ $transaction->client->name ?? 'N/A' }}</td>
                                                @endif
                                                <td>{{ $sale->quantity }}</td>
                                                <td>{{ $sale->product->product_description ?? 'N/A' }}</td>
                                                <td></td> <!-- Actual column -->
                                                @if ($loop->first)
                                                    <td rowspan="{{ $salesCount }}">{!! $paymentMethodDisplay !!}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">No transactions in this batch.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @php
                            $consolidatedQuantities = [];
                            $totalCollectibles = 0;
                            $codPaymentMethodId = 7;

                            foreach ($allBatchTransactions as $transaction) {
                                foreach ($transaction->sales as $sale) {
                                    $unit = $sale->product->product_description ?? 'N/A';
                                    if (!isset($consolidatedQuantities[$unit])) {
                                        $consolidatedQuantities[$unit] = 0;
                                    }
                                    $consolidatedQuantities[$unit] += $sale->quantity;
                                }

                                $firstPayment = $transaction->payments->first();
                                if ($firstPayment && $firstPayment->payment_method_id === $codPaymentMethodId && $transaction->remaining_balance > 0) {
                                    $totalCollectibles += $transaction->remaining_balance;
                                }
                            }
                        @endphp

                        <div class="consolidated-quantities">
                            <p class="font-semibold">Consolidated Quantities:</p>
                            @forelse ($consolidatedQuantities as $unit => $quantity)
                                <p class="ml-2">Total No. of "{{ $unit }}": {{ $quantity }}</p>
                            @empty
                                <p class="ml-2">No items in this batch.</p>
                            @endforelse
                        </div>
                        <div class="total-collectibles">
                            <p class="font-semibold">Total Collectibles: ₱{{ number_format($totalCollectibles, 2) }}</p>
                        </div>
                    </div>
                @endif
            @endfor
        </div>
        @if ($page < $pageCount - 1)
            <div class="page-break"></div>
        @endif
    @endfor

</body>
</html>
