<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk — {{ $sale->sale_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 300px;
            margin: 0 auto;
            padding: 16px 8px;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .item-name {
            flex: 1;
            padding-right: 8px;
        }

        h2 {
            font-size: 16px;
        }

        h3 {
            font-size: 13px;
        }

        .total-row {
            font-size: 14px;
            font-weight: bold;
        }

        @media print {
            body {
                width: 100%;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="center">
        <h2>POS INVENTORY</h2>
        <p>Struk Pembelian</p>
    </div>

    <div class="divider"></div>

    <div class="row"><span>No. Transaksi</span><span>{{ $sale->sale_number }}</span></div>
    <div class="row"><span>Tanggal</span><span>{{ $sale->sale_date->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $sale->user->name }}</span></div>
    @if ($sale->customer)
        <div class="row"><span>Pelanggan</span><span>{{ $sale->customer->name }}</span></div>
    @endif

    <div class="divider"></div>

    @foreach ($sale->items as $item)
        <div style="margin: 4px 0;">
            <p class="bold">{{ $item->product->name }}</p>
            <div class="row">
                <span>
                    {{ number_format($item->qty, 0) }} {{ $item->product->unit->symbol }}
                    x Rp {{ number_format($item->sale_price, 0, ',', '.') }}
                    @if ($item->discount_percent > 0)
                        (disc {{ $item->discount_percent }}%)
                    @endif
                </span>
                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endforeach

    <div class="divider"></div>

    <div class="row"><span>Subtotal</span><span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
    @if ($sale->discount_amount > 0)
        <div class="row"><span>Diskon</span><span>- Rp
                {{ number_format($sale->discount_amount, 0, ',', '.') }}</span></div>
    @endif
    @if ($sale->tax_amount > 0)
        <div class="row"><span>Pajak ({{ $sale->tax_percent }}%)</span><span>Rp
                {{ number_format($sale->tax_amount, 0, ',', '.') }}</span></div>
    @endif

    <div class="divider"></div>

    <div class="row total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
    </div>
    <div class="row"><span>Dibayar ({{ strtoupper($sale->payment_method) }})</span><span>Rp
            {{ number_format($sale->paid_amount, 0, ',', '.') }}</span></div>
    <div class="row bold"><span>Kembalian</span><span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="center">
        <p>Terima kasih atas kunjungan Anda</p>
        <p>Barang yang sudah dibeli</p>
        <p>tidak dapat dikembalikan</p>
    </div>

    <div class="no-print" style="margin-top: 16px; text-align: center;">
        <button onclick="window.print()"
            style="padding: 8px 24px; background: #2563eb; color: white;
                       border: none; border-radius: 8px; cursor: pointer; font-size: 13px;">
            Cetak Struk
        </button>
        <button onclick="window.close()"
            style="padding: 8px 24px; background: #e5e7eb; color: #374151;
                       border: none; border-radius: 8px; cursor: pointer; font-size: 13px; margin-left: 8px;">
            Tutup
        </button>
    </div>
</body>

</html>
