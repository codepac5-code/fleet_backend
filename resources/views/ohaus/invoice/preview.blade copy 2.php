<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHAUS Invoice {{ $invoiceNumber }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">
    <style>
        /* جميع الأنماط تبقى كما هي */
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Questrial', sans-serif;
            color: #000000;
            font-size: 9px;
            line-height: 1.4;
            background-color: #f5f5f5;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        /* Invoice Container - A4 Size */
        .invoice-container {
            width: 210mm; /* A4 width */
            height: 297mm; /* A4 height */
            background-color: white;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .customer-info {
            font-size: 10.5px;
            line-height: 1.2;
        }

        .company-info {
            text-align: right;
            line-height: 1.2;
        }

        .company-name {
            color: #E30613;
            font-weight: bold;
            font-size: 15px;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .company-address {
            font-size: 10px;
            line-height: 1.25;
        }

        /* Invoice Title */
        .invoice-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* Address Grid */
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .address-block {
            margin-bottom: 12px;
        }

        .block-title {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 9px;
        }

        .address-text {
            font-size: 8px;
            line-height: 1.3;
        }

        /* Contact Table */
        .contact-table {
            width: 100%;
            font-size: 8px;
            border-collapse: collapse;
        }

        .contact-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .contact-table td:first-child {
            width: 40%;
        }

        .contact-table td:last-child {
            text-align: right;
        }

        /* Purchase Information */
        .purchase-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            margin-top: 20px;
            margin-bottom: 15px;
            font-size: 8px;
            line-height: 1.4;
        }

        .purchase-section {
            padding-right: 20px;
        }

        .purchase-label {
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-top: 10px;
        }

        .items-table th {
            font-weight: bold;
            text-align: left;
            border-bottom: 0.5px solid #000;
            padding: 5px 3px;
        }

        .items-table td {
            padding: 5px 3px;
            vertical-align: top;
        }

        .num {
            text-align: right;
        }

        .item-notes {
            margin-top: 3px;
            font-size: 7px;
            line-height: 1.3;
        }

        /* Totals Section */
        .totals {
            width: 45%;
            margin-left: auto;
            margin-top: 15px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .grand-total {
            border-top: 1px solid #000;
            margin-top: 0px;
            padding-top: 0px;
            font-weight: bold;
        }

        /* Footer Section */
        .footer {
            position: absolute;
            bottom: 15mm;
            width: calc(100% - 30mm);
        }

        .bank-info {
            font-size: 9px;
            line-height: 1.3;
            margin-bottom: 10px;
        }

        .ohaus-footer {
            color: #E30613;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
            text-align: right;
            margin-bottom: 5px;
        }

        .page-info {
            font-size: 8px;
            text-align: right;
        }

        /* Print-specific styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
                margin: 0;
                font-family: 'Questrial', sans-serif;
            }

            .invoice-container {
                box-shadow: none;
                padding: 15mm;
                width: 100%;
                height: 100%;
                margin: 0;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="header">
            <div class="customer-info">
                {{ $customerPage ?? 'Customer Page 400058339' }}<br>
                {{ $currentPage ?? '1' }} of {{ $totalPages ?? '3' }}
            </div>
            <div class="company-info">
                <div class="company-name">{{ $companyName ?? 'OHAUS CORPORATION' }}</div>
                <div class="company-address">
                    {{ $companyAddress ?? 'P.O. Box 5667, Parsippany, NJ 07054' }}<br>
                    Phone: {{ $companyPhone ?? '(973) 377-9000 / (800) 672-7722' }}<br>
                    Fax: {{ $companyFax ?? '(973) 944-7177' }}<br>
                    {{ $companyWebsite ?? 'www.ohaus.com' }}
                </div>
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">Invoice {{ $invoiceNumber ?? '637458087' }}</div>

        <!-- Address Grid -->
        <div class="address-grid">
            <div class="left-column">
                <!-- Bill-To Section -->
                <div class="address-block">
                    <div class="block-title">Bill-To / {{ $customerNumber ?? '400058339' }}</div>
                    <div class="address-text">
                        {{ ($billTo['name'] ?? 'Ramo Trading') }}<br>
                        {{ ($billTo['address'] ?? '15205 Spectrum') }}<br>
                        {{ ($billTo['cityStateZip'] ?? 'IRVINE, CA 92618-3425') }}
                    </div>
                </div>

                <!-- Remit-To Section -->
                <div class="address-block">
                    <div class="block-title">Remit-To</div>
                    <div class="address-text">
                        {{ ($remitTo['name'] ?? 'Ohaus Corporation') }}<br>
                        {{ ($remitTo['address'] ?? '23812 Network Place') }}<br>
                        {{ ($remitTo['cityStateZip'] ?? 'Chicago IL 60673') }}<br>
                        {{ ($remitTo['note'] ?? 'Please reference invoice 637458087 with your payment') }}
                    </div>
                </div>

                <!-- Customer Contact Section -->
                <div class="address-block">
                    <div class="block-title">Customer Contact</div>
                    <table class="contact-table">
                        <tr>
                            <td>Name</td>
                            <td>{{ $customerContact['name'] ?? 'Asaad Ramo' }}</td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td>{{ $customerContact['telephone'] ?? '+1 (833) 669 0944' }}</td>
                        </tr>
                        <tr>
                            <td>E-Mail</td>
                            <td>{{ $customerContact['email'] ?? 'info@ramofrading.com' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="right-column">
                <!-- Sold-To Section -->
                <div class="address-block">
                    <div class="block-title">Sold-To / {{ $customerNumber ?? '400058339' }}</div>
                    <div class="address-text">
                        {{ ($soldTo['name'] ?? 'Ramo Trading') }}<br>
                        {{ ($soldTo['address'] ?? '15205 Spectrum') }}<br>
                        {{ ($soldTo['cityStateZip'] ?? 'IRVINE, CA 92618-3425') }}
                    </div>
                </div>

                <!-- Ship-To Section -->
                <div class="address-block">
                    <div class="block-title">Ship-To / {{ $customerNumber ?? '400058339' }}</div>
                    <div class="address-text">
                        {{ ($shipTo['name'] ?? 'Asaad Ramo') }}<br>
                        {{ ($shipTo['company'] ?? 'Ramo Trading Consulting Inc') }}<br>
                        {{ ($shipTo['address'] ?? '2 chaparral Court') }}<br>
                        {{ ($shipTo['cityStateZip'] ?? 'Rancho Sanat Margerita, CA 92688') }}
                    </div>
                </div>

                <!-- Invoice Data Section -->
                <div class="address-block">
                    <div class="block-title">Invoice Data</div>
                    <table class="contact-table">
                        <tr>
                            <td>Invoice Date</td>
                            <td>{{ $invoiceDate ?? '12/17/2024' }}</td>
                        </tr>
                        <tr>
                            <td>Invoice No.</td>
                            <td>{{ $invoiceNumber ?? '637458087' }}</td>
                        </tr>
                        <tr>
                            <td>Order No.</td>
                            <td>{{ $orderNumber ?? '901050786' }}</td>
                        </tr>
                        <tr>
                            <td>Collect Account</td>
                            <td>{{ $collectAccount ?? '694758479' }}</td>
                        </tr>
                        <tr>
                            <td>Payment Terms</td>
                            <td>{{ $paymentTerms ?? 'Due 30 Days from Invoice' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Purchase Information -->
        <div class="purchase-info">
            <div class="purchase-section">
                <div class="purchase-label">Purchase order</div>
                {{ $purchaseInfo['purchaseOrder'] ?? 'PO 2712' }}
                <div class="purchase-label">Delivery Note</div>
                {{ $purchaseInfo['deliveryNote'] ?? '99326096' }}
            </div>
            <div class="purchase-section">
                <div class="purchase-label">Order Date</div>
                {{ $purchaseInfo['orderDate'] ?? '12/16/2024' }}
                <div class="purchase-label">Ship Date</div>
                {{ $purchaseInfo['shipDate'] ?? '12/17/2024' }}
            </div>
            <div class="purchase-section">
                <div class="purchase-label">Customer-No.</div>
                {{ $customerNumber ?? '400058339' }}
                <div class="purchase-label">Shipment No.</div>
                {{ $purchaseInfo['shipmentNumber'] ?? '5606840' }}
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Product ID</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th class="num">Price per Unit</th>
                    <th class="num">Discount</th>
                    <th class="num">Total USD</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $defaultItems = [
                        [
                            'description' => 'Standard Conduct 12.88mS/cm 250ml',
                            'productId' => '30100444',
                            'quantity' => 5,
                            'unit' => 'EA',
                            'pricePerUnit' => 29.00,
                            'discount' => '(30.00%)',
                            'total' => 101.50,
                            'notes' => [
                                'Commodity Code 3105100000',
                                'Country of Origin US',
                                'Ship Via FedEx Ground',
                                'Carrier Tracking Number: 283404897460'
                            ]
                        ],
                        [
                            'description' => 'Standard Conduct 1413μS/cm 250ml',
                            'productId' => '30100443',
                            'quantity' => 5,
                            'unit' => 'EA',
                            'pricePerUnit' => 29.00,
                            'discount' => '(30.00%)',
                            'total' => 101.50,
                            'notes' => [
                                'Commodity Code 3105100000',
                                'Country of Origin US',
                                'Ship Via FedEx Ground',
                                'Carrier Tracking Number: 283404897460'
                            ]
                        ]
                    ];

                    $itemsToDisplay = $items ?? $defaultItems;
                    $subTotal = $subTotal ?? 203.00;
                    $invoiceTotal = $invoiceTotal ?? 203.00;
                @endphp

                @foreach($itemsToDisplay as $item)
                <tr>
                    <td></td>
                    <td>
                        {{ $item['description'] }}
                        <div class="item-notes">
                            @foreach($item['notes'] as $note)
                                {{ $note }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </div>
                    </td>
                    <td>{{ $item['productId'] }}</td>
                    <td class="num">{{ $item['quantity'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td class="num">{{ number_format($item['pricePerUnit'], 2) }}</td>
                    <td class="num">{{ $item['discount'] }}</td>
                    <td class="num">{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals">
            <div class="total-line">
                <strong>Sub-Total</strong>
                <strong>{{ number_format($subTotal, 2) }}</strong>
            </div>
            <div class="total-line grand-total">
                <strong>Invoice Total USD</strong>
                <strong>{{ number_format($invoiceTotal, 2) }}</strong>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <div class="bank-info">
                Bank USD: {{ $bankInfo['bankName'] ?? 'JP Morgan Chase, N.A., New York' }}<br>
                Routing #: {{ $bankInfo['routingNumber'] ?? '071000013' }}&nbsp;&nbsp;Account #: {{ $bankInfo['accountNumber'] ?? '722620283' }}<br>
                Swift Code: {{ $bankInfo['swiftCode'] ?? 'CHASUS33' }}
            </div>
            <div class="ohaus-footer">OHAUS</div>
            <div class="page-info">{{ $customerPage ?? 'Customer Page 400058339' }} — {{ $currentPage ?? '1' }} of {{ $totalPages ?? '3' }}</div>
        </div>
    </div>

    <script>
        // تأكد من تحميل الخط قبل الطباعة
        document.fonts.load('1em "Questrial"').then(() => {
            console.log('Questrial font loaded');
        });
    </script>
</body>
</html>
