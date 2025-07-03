<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $distribution->delivery_number }}</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
            margin: 0;
            padding: 20px;
        }

        /* Header Company */
        .company-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
            position: relative;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .company-logo {
            flex: 0 0 80px;
        }

        .logo-placeholder {
            width: 80px;
            height: 60px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            line-height: 1.2;
        }

        .company-details {
            flex: 1;
            margin-left: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }

        .invoice-info {
            text-align: right;
            flex: 0 0 250px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #007bff;
        }

        /* Document Title */
        .document-title {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 10px;
        }

        .document-title h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 3px;
        }

        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Invoice Details */
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }

        .bill-to-section,
        .invoice-info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px dotted #dee2e6;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 11px;
        }

        .detail-value {
            color: #333;
            font-size: 12px;
            text-align: right;
        }

        /* Items Table */
        .invoice-items {
            margin: 30px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .items-table thead {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .items-table th {
            padding: 15px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
        }

        .items-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Pricing Styles */
        .price {
            font-weight: bold;
            color: #007bff;
        }

        .total-price {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
        }

        /* Summary Section */
        .invoice-summary {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .summary-table {
            width: 350px;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #007bff;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-row:last-child {
            border-bottom: none;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .summary-label {
            font-weight: bold;
        }

        .summary-value {
            text-align: right;
        }

        /* Batch Badge */
        .batch-badge {
            background: #007bff;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 10px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        /* Text Alignment Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* Payment Information */
        .payment-info {
            margin: 30px 0;
            padding: 20px;
            background: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 10px;
        }

        .payment-title {
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .bank-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            font-size: 11px;
        }

        .bank-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border-left: 3px solid #2196f3;
        }

        /* Terms Section */
        .terms-section {
            margin: 30px 0;
            padding: 20px;
            background: #fff3e0;
            border: 2px solid #ff9800;
            border-radius: 10px;
            font-size: 11px;
        }

        .terms-title {
            font-weight: bold;
            color: #f57c00;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .terms-list {
            list-style: none;
            padding: 0;
        }

        .terms-list li {
            margin-bottom: 5px;
            padding-left: 15px;
            position: relative;
        }

        .terms-list li:before {
            content: "•";
            color: #ff9800;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-overdue {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* QC Badge */
        .qc-badge {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #007bff;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 11px;
            }
            
            .company-header {
                margin-bottom: 15px;
                padding-bottom: 10px;
            }
            
            .document-title {
                margin: 15px 0;
                padding: 15px;
            }
            
            .invoice-details {
                margin: 20px 0;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 6px;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .company-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .invoice-details {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .bank-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Company Header -->
    <div class="company-header">
        <div class="company-info">
            <div class="company-logo">
                <div class="logo-placeholder">
                    PROD<br>CORE
                </div>
            </div>
            <div class="company-details">
                <div class="company-name">PT. PRODCORE MANUFACTURING</div>
                <div class="company-address">Jl. Industri Raya No. 123, Kawasan Industri MM2100</div>
                <div class="company-address">Bekasi 17520, Jawa Barat, Indonesia</div>
                <div class="company-address">Telp: (021) 8888-9999 | Email: finance@prodcore.com</div>
                <div class="company-address">NPWP: 01.234.567.8-901.000</div>
            </div>
            <div class="invoice-info">
                <div style="font-size: 12px; font-weight: bold; color: #007bff; margin-bottom: 8px;">INVOICE INFO</div>
                <div class="detail-item">
                    <span class="detail-label">Invoice Date:</span>
                    <span class="detail-value">{{ $distribution->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">{{ $distribution->created_at->addDays(30)->format('d/m/Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-pending">PENDING</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>INVOICE</h1>
        <div class="invoice-number">INV-{{ $distribution->delivery_number }}</div>
    </div>

    <!-- Invoice Details -->
    <div class="invoice-details">
        <!-- Bill To Section -->
        <div class="bill-to-section">
            <div class="section-title">
                🏢 BILL TO
            </div>
            <div style="margin-bottom: 15px;">
                <div style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 8px;">
                    {{ $distribution->customer_name }}
                </div>
                <div style="color: #666; line-height: 1.6;">
                    {{ $distribution->delivery_address }}
                </div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Customer ID:</span>
                <span class="detail-value">CUST-{{ str_pad(crc32($distribution->customer_name), 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Delivery Number:</span>
                <span class="detail-value">{{ $distribution->delivery_number }}</span>
            </div>
        </div>

        <!-- Invoice Info Section -->
        <div class="invoice-info-section">
            <div class="section-title">
                📋 INVOICE DETAILS
            </div>
            <div class="detail-item">
                <span class="detail-label">Invoice Number:</span>
                <span class="detail-value">INV-{{ $distribution->delivery_number }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Delivery Date:</span>
                <span class="detail-value">{{ $distribution->distribution_date->format('d/m/Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Terms:</span>
                <span class="detail-value">Net 30 Days</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Sales Rep:</span>
                <span class="detail-value">{{ $distribution->preparedBy->name ?? 'Sales Team' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Delivery Method:</span>
                <span class="detail-value">Truck Delivery</span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="invoice-items">
        <div class="section-title" style="margin-bottom: 15px;">
            📦 ITEMS & SERVICES
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Batch Number</th>
                    <th style="width: 30%;">Product Description</th>
                    <th style="width: 10%;">QC Status</th>
                    <th style="width: 10%;">Quantity</th>
                    <th style="width: 12%;">Unit Price (Rp)</th>
                    <th style="width: 8%;">Disc %</th>
                    <th style="width: 10%;">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subTotal = 0;
                    // Base price calculation per product type
                    $basePrices = [
                        'Brakepad Honda' => 75000,
                        'Brakepad Yamaha' => 80000,
                        'Brakepad Suzuki' => 70000,
                        'Brakepad Kawasaki' => 95000,
                        'Brakepad TVS' => 65000
                    ];
                @endphp
                
                @foreach($distribution->items as $index => $item)
                @php
                    // Determine price based on product name
                    $unitPrice = 75000; // Default price
                    foreach($basePrices as $brand => $price) {
                        if (strpos($item['product_name'], explode(' ', $brand)[1]) !== false) {
                            $unitPrice = $price;
                            break;
                        }
                    }
                    
                    $quantity = $item['quantity'];
                    $discount = 0; // No discount for now
                    $lineTotal = $quantity * $unitPrice * (1 - $discount/100);
                    $subTotal += $lineTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <span class="batch-badge">{{ $item['batch_number'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $item['product_name'] }}</strong>
                        @if(isset($item['product_brand']))
                        <br><small style="color: #666;">Brand: {{ $item['product_brand'] }}</small>
                        @endif
                        <br><small style="color: #666;">Weight: {{ number_format($item['unit_weight'], 2) }} kg/pcs</small>
                    </td>
                    <td class="text-center">
                        <span class="qc-badge">✓ PASSED</span>
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($quantity) }}</strong> pcs
                    </td>
                    <td class="text-right">
                        <span class="price">{{ number_format($unitPrice, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        {{ $discount }}%
                    </td>
                    <td class="text-right">
                        <span class="total-price">{{ number_format($lineTotal, 0, ',', '.') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Invoice Summary -->
    <div class="invoice-summary">
        <div class="summary-table">
            @php
                $ppn = $subTotal * 0.11; // PPN 11%
                $grandTotal = $subTotal + $ppn;
            @endphp
            
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value">Rp {{ number_format($subTotal, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Discount:</span>
                <span class="summary-value">Rp 0</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">PPN (11%):</span>
                <span class="summary-value">Rp {{ number_format($ppn, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Shipping:</span>
                <span class="summary-value">Included</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">TOTAL AMOUNT:</span>
                <span class="summary-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="payment-info">
        <div class="payment-title">💳 PAYMENT INFORMATION</div>
        <div class="bank-details">
            <div class="bank-item">
                <strong>Bank BCA</strong><br>
                Acc: 1234-567-890<br>
                A/N: PT. ProdCore Manufacturing
            </div>
            <div class="bank-item">
                <strong>Bank Mandiri</strong><br>
                Acc: 0987-654-321<br>
                A/N: PT. ProdCore Manufacturing
            </div>
            <div class="bank-item">
                <strong>Bank BNI</strong><br>
                Acc: 5555-666-777<br>
                A/N: PT. ProdCore Manufacturing
            </div>
            <div class="bank-item">
                <strong>Virtual Account</strong><br>
                VA: 8881234567890<br>
                Available 24/7
            </div>
        </div>
        <div style="margin-top: 15px; font-size: 11px; color: #1976d2;">
            <strong>📧 Email konfirmasi pembayaran ke:</strong> finance@prodcore.com<br>
            <strong>📱 WhatsApp konfirmasi:</strong> +62 812-3456-7890
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div class="terms-section">
        <div class="terms-title">📜 TERMS & CONDITIONS</div>
        <ul class="terms-list">
            <li>Payment is due within 30 days of invoice date</li>
            <li>Late payment will be subject to 2% monthly interest charge</li>
            <li>All prices are in Indonesian Rupiah (IDR)</li>
            <li>Goods delivered are guaranteed to meet quality standards</li>
            <li>Any complaints must be reported within 7 days of delivery</li>
            <li>This invoice is computer generated and valid without signature</li>
            <li>For any inquiries, please contact our finance department</li>
        </ul>
        
        <div style="margin-top: 15px; padding: 10px; background: white; border-radius: 5px; border-left: 3px solid #ff9800;">
            <strong>Important:</strong> Please include the invoice number (INV-{{ $distribution->delivery_number }}) 
            when making payment to ensure proper credit to your account.
        </div>
    </div>

    <!-- Notes Section -->
    @if($distribution->notes)
    <div style="margin: 20px 0; padding: 15px; background: #e8f5e8; border: 2px solid #4caf50; border-radius: 8px;">
        <div style="font-weight: bold; color: #2e7d32; margin-bottom: 8px;">📝 SPECIAL NOTES:</div>
        <div style="color: #2e7d32; font-size: 11px;">{{ $distribution->notes }}</div>
    </div>
    @endif

    <!-- Authorized Signature -->
    <div style="margin: 40px 0; display: flex; justify-content: space-between;">
        <div style="text-align: center; width: 200px;">
            <div style="font-weight: bold; margin-bottom: 50px; color: #007bff;">Prepared By:</div>
            <div style="border-bottom: 2px solid #333; margin-bottom: 8px;"></div>
            <div style="font-size: 12px;">{{ $distribution->preparedBy->name ?? 'Finance Team' }}</div>
            <div style="font-size: 10px; color: #666;">Finance Department</div>
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="font-weight: bold; margin-bottom: 50px; color: #007bff;">Authorized By:</div>
            <div style="border-bottom: 2px solid #333; margin-bottom: 8px;"></div>
            <div style="font-size: 12px;">Finance Manager</div>
            <div style="font-size: 10px; color: #666;">PT. ProdCore Manufacturing</div>
        </div>
    </div>

    <!-- Invoice Footer -->
    <div class="invoice-footer">
        <div style="margin-bottom: 10px;">
            <strong>Thank you for your business!</strong>
        </div>
        <div>Invoice generated on {{ now()->format('d/m/Y \a\t H:i:s') }} | Reference: {{ $distribution->delivery_number }}</div>
        <div style="margin-top: 5px;">
            PT. ProdCore Manufacturing | Phone: (021) 8888-9999 | Email: finance@prodcore.com
        </div>
        <div style="margin-top: 10px; font-size: 9px; color: #999;">
            This is a computer-generated invoice. For questions regarding this invoice, 
            please contact our finance department at finance@prodcore.com
        </div>
    </div>
</body>
</html>