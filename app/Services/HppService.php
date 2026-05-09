<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\HppRecord;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class HppService
{
    public function __construct(private FifoService $fifoService) {}

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 1 — PROSES TRANSAKSI POS LENGKAP
     * ═══════════════════════════════════════════
     *
     * Dipanggil satu kali saat kasir submit transaksi.
     * Alur:
     *   1. Validasi semua stok cukup
     *   2. Konsumsi lot FIFO per item (via FifoService)
     *   3. Update SaleItem dengan hpp_per_unit, hpp_amount, gross_profit
     *   4. Catat HppRecord per item
     *   5. Update Sale dengan total_hpp dan gross_profit
     *
     * @param  Sale  $sale  Sale yang sudah tersimpan (status completed)
     * @throws \Exception   Jika stok tidak cukup
     */
    public function processSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->load('items.product');

            // ── Langkah 1: Validasi semua stok sebelum ada yang dikurangi ──
            foreach ($sale->items as $item) {
                if (!$this->fifoService->hasSufficientStock($item->product_id, (float) $item->qty)) {
                    $available = $this->fifoService->getAvailableStock($item->product_id);
                    throw new \Exception(
                        "Stok {$item->product->name} tidak cukup. " .
                        "Dibutuhkan: {$item->qty}, tersedia: {$available}."
                    );
                }
            }

            $totalSaleHpp    = 0.0;
            $totalSaleProfit = 0.0;

            // ── Langkah 2-4: Proses per item ──
            foreach ($sale->items as $saleItem) {
                // Konsumsi lot FIFO → dapat detail cost
                $fifoResult = $this->fifoService->consumeStock($saleItem, $sale);

                $hppAmount   = $fifoResult['total_hpp'];
                $hppPerUnit  = $fifoResult['hpp_per_unit'];
                $grossProfit = round((float) $saleItem->subtotal - $hppAmount, 2);

                // Update SaleItem
                $saleItem->update([
                    'hpp_per_unit' => $hppPerUnit,
                    'hpp_amount'   => $hppAmount,
                    'gross_profit' => $grossProfit,
                ]);

                // Catat HppRecord
                $this->recordHpp($sale, $saleItem, $hppAmount, $hppPerUnit, $grossProfit);

                $totalSaleHpp    += $hppAmount;
                $totalSaleProfit += $grossProfit;
            }

            // ── Langkah 5: Update total HPP & laba di header Sale ──
            $sale->update([
                'total_hpp'    => round($totalSaleHpp, 2),
                'gross_profit' => round($totalSaleProfit, 2),
            ]);
        });
    }

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 2 — LAPORAN HPP PER PERIODE
     * ═══════════════════════════════════════════
     *
     * Merangkum HPP, penjualan, dan laba kotor per produk
     * dalam rentang tanggal tertentu.
     *
     * @return Collection<array{
     *   product_id: int, product_name: string, product_code: string,
     *   total_qty: float, total_revenue: float, total_hpp: float,
     *   total_profit: float, avg_margin: float
     * }>
     */
    public function getHppReportByPeriod(string $from, string $to): Collection
    {
        return HppRecord::with('product')
            ->inPeriod($from, $to)
            ->select('product_id')
            ->selectRaw('SUM(total_qty_sold)   AS total_qty')
            ->selectRaw('SUM(sale_revenue)      AS total_revenue')
            ->selectRaw('SUM(total_hpp)         AS total_hpp')
            ->selectRaw('SUM(gross_profit)      AS total_profit')
            ->selectRaw('AVG(gross_margin_percent) AS avg_margin')
            ->groupBy('product_id')
            ->orderByDesc('total_profit')
            ->get()
            ->map(fn($r) => [
                'product_id'    => $r->product_id,
                'product_name'  => $r->product->name ?? '-',
                'product_code'  => $r->product->code ?? '-',
                'total_qty'     => round((float) $r->total_qty, 2),
                'total_revenue' => round((float) $r->total_revenue, 2),
                'total_hpp'     => round((float) $r->total_hpp, 2),
                'total_profit'  => round((float) $r->total_profit, 2),
                'avg_margin'    => round((float) $r->avg_margin, 2),
            ]);
    }

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 3 — RINGKASAN HARIAN (DASHBOARD)
     * ═══════════════════════════════════════════
     */
    public function getDailySummary(string $date): array
    {
        $sales = Sale::completed()->byDate($date)->get();

        return [
            'date'              => $date,
            'total_transactions'=> $sales->count(),
            'total_revenue'     => $sales->sum('total_amount'),
            'total_hpp'         => $sales->sum('total_hpp'),
            'gross_profit'      => $sales->sum('gross_profit'),
            'avg_margin_percent'=> $sales->count() > 0
                ? round($sales->sum('gross_profit') / $sales->sum('total_amount') * 100, 2)
                : 0,
        ];
    }

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 4 — HPP PER PRODUK (detail produk)
     * ═══════════════════════════════════════════
     */
    public function getProductHppHistory(int $productId, string $from, string $to): Collection
    {
        return HppRecord::with('sale')
            ->byProduct($productId)
            ->inPeriod($from, $to)
            ->orderBy('period_date')
            ->get()
            ->map(fn($r) => [
                'date'          => $r->period_date->format('d/m/Y'),
                'sale_number'   => $r->sale->sale_number ?? '-',
                'qty_sold'      => $r->total_qty_sold,
                'revenue'       => $r->sale_revenue,
                'hpp'           => $r->total_hpp,
                'hpp_per_unit'  => $r->avg_hpp_per_unit,
                'profit'        => $r->gross_profit,
                'margin'        => $r->gross_margin_percent . '%',
            ]);
    }

    /**
     * ═══════════════════════════════════════════
     * BAGIAN 5 — NILAI PERSEDIAAN AKHIR
     * ═══════════════════════════════════════════
     * Untuk kebutuhan laporan neraca / balance sheet.
     */
    public function getInventoryValuation(): array
    {
        $items       = $this->fifoService->getInventoryValue();
        $totalValue  = array_sum(array_column($items, 'total_value'));

        return [
            'items'       => $items,
            'total_value' => round($totalValue, 2),
            'as_of_date'  => now()->format('d/m/Y'),
        ];
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────────────────

    private function recordHpp(
        Sale $sale,
        SaleItem $saleItem,
        float $totalHpp,
        float $avgHppPerUnit,
        float $grossProfit,
    ): HppRecord {
        $revenue       = (float) $saleItem->subtotal;
        $marginPercent = $revenue > 0
            ? round(($grossProfit / $revenue) * 100, 4)
            : 0;

        return HppRecord::create([
            'sale_id'              => $sale->id,
            'sale_item_id'         => $saleItem->id,
            'product_id'           => $saleItem->product_id,
            'period_date'          => $sale->sale_date->toDateString(),
            'total_qty_sold'       => $saleItem->qty,
            'sale_revenue'         => $revenue,
            'total_hpp'            => $totalHpp,
            'avg_hpp_per_unit'     => $avgHppPerUnit,
            'gross_profit'         => $grossProfit,
            'gross_margin_percent' => $marginPercent,
        ]);
    }
}
