<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLot;
use App\Models\StockMovement;
use App\Models\GoodsReceipt;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemLot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FifoService
{
    /**
     * ═══════════════════════════════════════════
     * BAGIAN 1 — STOK MASUK (saat GR dikonfirmasi)
     * ═══════════════════════════════════════════
     *
     * Setiap item di GoodsReceipt menghasilkan 1 StockLot baru.
     * Lot ini menyimpan tanggal terima + harga beli → dasar urutan FIFO.
     */
    public function processStockIn(GoodsReceipt $goodsReceipt): void
    {
        DB::transaction(function () use ($goodsReceipt) {
            foreach ($goodsReceipt->items as $grItem) {
                // 1. Buat lot baru
                $lot = StockLot::create([
                    'product_id'       => $grItem->product_id,
                    'goods_receipt_id' => $goodsReceipt->id,
                    'lot_number'       => $this->generateLotNumber(),
                    'received_date'    => $goodsReceipt->receipt_date,
                    'unit_cost'        => $grItem->unit_cost,
                    'qty_initial'      => $grItem->qty_received,
                    'qty_remaining'    => $grItem->qty_received,
                    'is_exhausted'     => false,
                ]);

                // 2. Update relasi GR item ke lot yang baru dibuat
                $grItem->update(['stock_lot_id' => $lot->id]);

                // 3. Update qty_received di PO item
                $poItem = $grItem->purchaseOrderItem;
                $poItem->increment('qty_received', $grItem->qty_received);

                // 4. Catat movement: stock_in
                $this->recordMovement(
                    product: $grItem->product,
                    lot: $lot,
                    reference: $goodsReceipt,
                    type: StockMovement::TYPE_STOCK_IN,
                    qty: $grItem->qty_received,
                    unitCost: $grItem->unit_cost,
                    userId: $goodsReceipt->user_id,
                    movementDate: $goodsReceipt->receipt_date,
                );
            }

            // 5. Update status PO jika semua item sudah diterima penuh
            $this->updatePurchaseOrderStatus($goodsReceipt->purchaseOrder);
        });
    }

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 2 — STOK KELUAR (saat penjualan)
     * ═══════════════════════════════════════════
     *
     * Mengambil lot dari yang paling lama (FIFO).
     * Mengembalikan array detail konsumsi per lot untuk disimpan
     * ke sale_item_lots dan dipakai HppService menghitung HPP.
     *
     * @return array [
     *   'lots'          => [['lot' => StockLot, 'qty_taken' => float, 'unit_cost' => float, 'total_cost' => float], ...],
     *   'total_hpp'     => float,
     *   'hpp_per_unit'  => float,
     * ]
     */
    public function consumeStock(
        SaleItem $saleItem,
        Sale $sale
    ): array {
        $productId = $saleItem->product_id;
        $qtyNeeded = (float) $saleItem->qty;

        // Lock baris agar aman di concurrent request
        $lots = StockLot::forProduct($productId)
            ->available()
            ->lockForUpdate()
            ->get();

        // Validasi stok — lapisan ketiga (database level)
        $totalAvailable = (float) $lots->sum('qty_remaining');

        if ($totalAvailable <= 0) {
            $product = $saleItem->product;
            throw new \Exception("Stok {$product->name} sudah habis saat transaksi diproses.");
        }

        if ($totalAvailable < $qtyNeeded) {
            $product = $saleItem->product;
            throw new \Exception(
                "Stok {$product->name} tidak cukup. "
                    . "Dibutuhkan: {$qtyNeeded} {$product->unit->symbol}, "
                    . "Tersedia: {$totalAvailable} {$product->unit->symbol}."
            );
        }

        $consumedLots = [];
        $totalHpp     = 0.0;
        $remaining    = $qtyNeeded;

        foreach ($lots as $lot) {
            if ($remaining <= 0) break;

            // Ambil sebanyak yang tersedia di lot ini, maksimal sesuai kebutuhan
            $taken = min((float) $lot->qty_remaining, $remaining);
            $cost  = round($taken * (float) $lot->unit_cost, 2);

            // Update lot
            $newRemaining = (float) $lot->qty_remaining - $taken;
            $lot->update([
                'qty_remaining' => $newRemaining,
                'is_exhausted'  => $newRemaining <= 0,
            ]);

            // Simpan detail konsumsi lot ini ke sale_item_lots
            SaleItemLot::create([
                'sale_item_id' => $saleItem->id,
                'stock_lot_id' => $lot->id,
                'qty_taken'    => $taken,
                'unit_cost'    => $lot->unit_cost,
                'total_cost'   => $cost,
            ]);

            // Catat stock movement: stock_out
            $this->recordMovement(
                product: $lot->product,
                lot: $lot,
                reference: $sale,
                type: StockMovement::TYPE_STOCK_OUT,
                qty: $taken,
                unitCost: $lot->unit_cost,
                userId: $sale->user_id,
                movementDate: $sale->sale_date,
            );

            $consumedLots[] = [
                'lot'        => $lot,
                'qty_taken'  => $taken,
                'unit_cost'  => (float) $lot->unit_cost,
                'total_cost' => $cost,
            ];

            $totalHpp  += $cost;
            $remaining -= $taken;
        }

        $hppPerUnit = $qtyNeeded > 0 ? round($totalHpp / $qtyNeeded, 4) : 0;

        return [
            'lots'         => $consumedLots,
            'total_hpp'    => round($totalHpp, 2),
            'hpp_per_unit' => $hppPerUnit,
        ];
    }

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 3 — CEK KETERSEDIAAN STOK
     * ═══════════════════════════════════════════
     * Dipakai sebelum transaksi untuk validasi UI kasir.
     */
    public function getAvailableStock(int $productId): float
    {
        return (float) StockLot::forProduct($productId)
            ->available()
            ->sum('qty_remaining');
    }

    /**
     * Cek apakah stok cukup untuk qty yang diminta.
     */
    public function hasSufficientStock(int $productId, float $qty): bool
    {
        return $this->getAvailableStock($productId) >= $qty;
    }

    /**
     * Ambil ringkasan lot aktif per produk (untuk tampilan di UI inventory).
     */
    public function getActiveLots(int $productId): \Illuminate\Support\Collection
    {
        return StockLot::forProduct($productId)
            ->available()
            ->with('goodsReceipt')
            ->get()
            ->map(fn($lot) => [
                'lot_number'    => $lot->lot_number,
                'received_date' => $lot->received_date->format('d/m/Y'),
                'unit_cost'     => $lot->unit_cost,
                'qty_remaining' => $lot->qty_remaining,
                'current_value' => $lot->current_value,
            ]);
    }

    /**
     * Hitung nilai persediaan akhir seluruh produk (untuk neraca).
     * Nilai persediaan FIFO = qty sisa × harga beli masing-masing lot.
     */
    public function getInventoryValue(): array
    {
        $lots = StockLot::where('is_exhausted', false)
            ->where('qty_remaining', '>', 0)
            ->with('product')
            ->get();

        $summary = [];
        foreach ($lots as $lot) {
            $pid = $lot->product_id;
            if (!isset($summary[$pid])) {
                $summary[$pid] = [
                    'product'       => $lot->product->name,
                    'total_qty'     => 0,
                    'total_value'   => 0,
                ];
            }
            $summary[$pid]['total_qty']   += $lot->qty_remaining;
            $summary[$pid]['total_value'] += $lot->current_value;
        }

        return array_values($summary);
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────────────────

    private function recordMovement(
        Product $product,
        StockLot $lot,
        $reference,
        string $type,
        float $qty,
        float $unitCost,
        int $userId,
        $movementDate,
    ): void {
        // running_stock = stok kumulatif produk setelah movement ini
        $runningStock = StockLot::forProduct($product->id)
            ->where('is_exhausted', false)
            ->sum('qty_remaining');

        StockMovement::create([
            'product_id'     => $product->id,
            'stock_lot_id'   => $lot->id,
            'reference_id'   => $reference->id,
            'reference_type' => get_class($reference),
            'movement_type'  => $type,
            'qty'            => $qty,
            'unit_cost'      => $unitCost,
            'total_cost'     => round($qty * $unitCost, 2),
            'running_stock'  => $runningStock,
            'user_id'        => $userId,
            'movement_date'  => $movementDate,
        ]);
    }

    private function generateLotNumber(): string
    {
        // Format: LOT-20240115-A3F9
        return 'LOT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }

    private function updatePurchaseOrderStatus($purchaseOrder): void
    {
        $allItems   = $purchaseOrder->items;
        $allFull    = $allItems->every(fn($i) => $i->isFullyReceived());
        $anyPartial = $allItems->some(fn($i) => $i->qty_received > 0);

        $purchaseOrder->update([
            'status' => match (true) {
                $allFull    => 'received',
                $anyPartial => 'partial',
                default     => $purchaseOrder->status,
            }
        ]);
    }
}
