<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $distribution->delivery_number }}</title>
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
            border-bottom: 3px solid #28a745;
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
            background: linear-gradient(135deg, #28a745, #20c997);
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
            color: #28a745;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }

        .document-info {
            text-align: right;
            flex: 0 0 200px;
        }

        /* Document Title */
        .document-title {
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 8px;
            border: 2px solid #28a745;
        }

        .document-title h1 {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .delivery-number {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            font-family: 'Courier New', monospace;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            display: inline-block;
        }

        /* Info Sections */
        .info-section {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 11px;
            text-transform: uppercase;
        }

        .info-value {
            color: #333;
            font-size: 12px;
            padding: 5px 0;
        }

        /* Items Table */
        .items-section {
            margin: 30px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .items-table thead {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
        }

        .items-table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05);
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }

        .items-table tfoot td {
            padding: 12px 8px;
            border-top: 2px solid #28a745;
            font-size: 13px;
        }

        /* Batch Badge */
        .batch-badge {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        /* Text Alignment Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* Summary Stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border-left: 4px solid #28a745;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Notes Section */
        .notes-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .notes-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .notes-content {
            color: #856404;
            font-size: 11px;
            line-height: 1.5;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .signature-box {
            text-align: center;
            padding: 20px;
            border: 1px dashed #dee2e6;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #28a745;
            font-size: 12px;
        }

        .signature-line {
            height: 60px;
            border-bottom: 2px solid #333;
            margin: 20px 0 10px 0;
        }

        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .signature-date {
            font-size: 10px;
            color: #666;
        }

        /* Footer */
        .document-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 15px;
                font-size: 11px;
            }
            
            .company-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            
            .document-title {
                margin: 20px 0;
                padding: 10px;
            }
            
            .signature-section {
                margin-top: 30px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 6px;
            }
        }

        /* Quality Indicator */
        .qc-badge {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        /* Status Indicators */
        .status-prepared { color: #856404; }
        .status-shipped { color: #0c5460; }
        .status-delivered { color: #155724; }
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
                <div class="company-address">Telp: (021) 8888-9999 | Email: info@prodcore.com</div>
                <div class="company-address">NPWP: 01.234.567.8-901.000</div>
            </div>
            <div class="document-info">
                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Tanggal Cetak:</div>
                <div style="font-weight: bold; margin-bottom: 10px;">{{ now()->format('d/m/Y H:i') }}</div>
                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Status:</div>
                <div class="status-{{ $distribution->status }}">
                    <strong>{{ strtoupper($distribution->status) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>SURAT JALAN</h1>
        <div class="delivery-number">{{ $distribution->delivery_number }}</div>
    </div>

    <!-- Customer Information -->
    <div class="info-section">
        <div class="section-title">
            🏢 INFORMASI PENERIMA
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nama Customer</div>
                <div class="info-value">{{ $distribution->customer_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tanggal Distribusi</div>
                <div class="info-value">{{ $distribution->distribution_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label">Alamat Pengiriman</div>
                <div class="info-value">{{ $distribution->delivery_address }}</div>
            </div>
        </div>
    </div>

    <!-- Delivery Information -->
    <div class="info-section">
        <div class="section-title">
            🚛 INFORMASI PENGIRIMAN
        </div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nama Driver</div>
                <div class="info-value">{{ $distribution->driver_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Nomor Kendaraan</div>
                <div class="info-value" style="font-family: 'Courier New', monospace; font-weight: bold;">{{ $distribution->vehicle_number }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Disiapkan Oleh</div>
                <div class="info-value">{{ $distribution->preparedBy->name ?? 'System' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Waktu Persiapan</div>
                <div class="info-value">{{ $distribution->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="summary-stats">
        <div class="stat-card">
            <div class="stat-value">{{ count($distribution->items) }}</div>
            <div class="stat-label">Jenis Item</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($distribution->total_quantity) }}</div>
            <div class="stat-label">Total Pieces</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($distribution->total_weight, 2) }}</div>
            <div class="stat-label">Total Berat (kg)</div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="items-section">
        <div class="section-title" style="margin-bottom: 15px;">
            📦 DETAIL PRODUK YANG DIKIRIM
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Batch Number</th>
                    <th style="width: 35%;">Nama Produk</th>
                    <th style="width: 12%;">QC Status</th>
                    <th style="width: 10%;">Quantity</th>
                    <th style="width: 10%;">Unit Weight</th>
                    <th style="width: 10%;">Total Berat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distribution->items as $index => $item)
                @php
                    $totalItemWeight = $item['quantity'] * $item['unit_weight'];
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <span class="batch-badge">{{ $item['batch_number'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $item['product_name'] }}</strong>
                        @if(isset($item['product_brand']))
                        <br><small style="color: #666;">{{ $item['product_brand'] }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="qc-badge">✓ QC PASSED</span>
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($item['quantity']) }}</strong> pcs
                    </td>
                    <td class="text-right">
                        {{ number_format($item['unit_weight'], 2) }} kg
                    </td>
                    <td class="text-right">
                        <strong>{{ number_format($totalItemWeight, 2) }} kg</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>TOTAL KESELURUHAN:</strong></td>
                    <td class="text-right"><strong>{{ number_format($distribution->total_quantity) }} pcs</strong></td>
                    <td class="text-center">-</td>
                    <td class="text-right"><strong>{{ number_format($distribution->total_weight, 2) }} kg</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Notes Section -->
    @if($distribution->notes)
    <div class="notes-section">
        <div class="notes-title">📝 CATATAN KHUSUS:</div>
        <div class="notes-content">{{ $distribution->notes }}</div>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">DISERAHKAN OLEH</div>
            <div class="signature-line"></div>
            <div class="signature-name">{{ $distribution->driver_name }}</div>
            <div class="signature-date">Tanggal: {{ $distribution->distribution_date->format('d/m/Y') }}</div>
            <div style="font-size: 10px; color: #666; margin-top: 5px;">Driver / Pengirim</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">DITERIMA OLEH</div>
            <div class="signature-line"></div>
            <div class="signature-name">{{ $distribution->customer_name }}</div>
            <div class="signature-date">Tanggal: ___/___/______</div>
            <div style="font-size: 10px; color: #666; margin-top: 5px;">Penerima / Customer</div>
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div style="margin: 30px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 10px; color: #666;">
        <strong>SYARAT & KETENTUAN:</strong><br>
        1. Barang yang sudah diterima tidak dapat dikembalikan kecuali ada kesepakatan tertulis.<br>
        2. Segala kerusakan atau kekurangan mohon dilaporkan maksimal 2 x 24 jam setelah barang diterima.<br>
        3. Surat jalan ini merupakan bukti pengiriman resmi dari PT. ProdCore Manufacturing.<br>
        4. Untuk keluhan atau pertanyaan dapat menghubungi customer service kami di (021) 8888-9999.
    </div>

    <!-- Document Footer -->
    <div class="document-footer">
        <div>Dokumen ini dicetak secara otomatis pada {{ now()->format('d/m/Y \p\u\k\u\l H:i:s') }} oleh {{ auth()->user()->name ?? 'System' }}</div>
        <div style="margin-top: 5px;">PT. ProdCore Manufacturing - Surat Jalan {{ $distribution->delivery_number }}</div>
    </div>
</body>
</html>