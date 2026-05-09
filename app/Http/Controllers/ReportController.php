<?php

namespace App\Http\Controllers;

use App\Models\HppRecord;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:reports.hpp')->only(['hpp']);
        $this->middleware('can:reports.sales')->only(['sales', 'saleDetail']);
        $this->middleware('can:reports.inventory')->only(['inventory']);
    }

    // ─── Laporan HPP ──────────────────────────────────────────
    public function hpp(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to',   now()->format('Y-m-d'));

        // Ringkasan HPP per produk dalam periode
        $hppByProduct = HppRecord::with('product.unit')
            ->whereBetween('period_date', [$from, $to])
            ->select('product_id')
            ->selectRaw('SUM(total_qty_sold)            AS total_qty')
            ->selectRaw('SUM(sale_revenue)               AS total_revenue')
            ->selectRaw('SUM(total_hpp)                  AS total_hpp')
            ->selectRaw('SUM(gross_profit)               AS total_profit')
            ->selectRaw('SUM(total_hpp) / NULLIF(SUM(total_qty_sold), 0) AS avg_hpp_per_unit')
            ->selectRaw('SUM(gross_profit) / NULLIF(SUM(sale_revenue), 0) * 100 AS avg_margin')
            ->groupBy('product_id')
            ->orderByDesc('total_profit')
            ->get();

        // Ringkasan HPP per hari (untuk chart)
        $hppByDay = HppRecord::whereBetween('period_date', [$from, $to])
            ->select('period_date')
            ->selectRaw('SUM(sale_revenue) AS revenue')
            ->selectRaw('SUM(total_hpp)    AS hpp')
            ->selectRaw('SUM(gross_profit) AS profit')
            ->groupBy('period_date')
            ->orderBy('period_date')
            ->get();

        // Total keseluruhan
        $totals = [
            'revenue' => $hppByProduct->sum('total_revenue'),
            'hpp'     => $hppByProduct->sum('total_hpp'),
            'profit'  => $hppByProduct->sum('total_profit'),
            'margin'  => $hppByProduct->sum('total_revenue') > 0
                ? round($hppByProduct->sum('total_profit') / $hppByProduct->sum('total_revenue') * 100, 2)
                : 0,
        ];

        // HPP per kategori
        $hppByCategory = HppRecord::with('product.category')
            ->whereBetween('period_date', [$from, $to])
            ->get()
            ->groupBy('product.category.name')
            ->map(fn($rows) => [
                'revenue' => $rows->sum('sale_revenue'),
                'hpp'     => $rows->sum('total_hpp'),
                'profit'  => $rows->sum('gross_profit'),
            ]);

        $chartDays    = $hppByDay->pluck('period_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'));
        $chartRevenue = $hppByDay->pluck('revenue');
        $chartHpp     = $hppByDay->pluck('hpp');
        $chartProfit  = $hppByDay->pluck('profit');

        return view('reports.hpp', compact(
            'from',
            'to',
            'hppByProduct',
            'totals',
            'hppByCategory',
            'chartDays',
            'chartRevenue',
            'chartHpp',
            'chartProfit'
        ));
    }

    // ─── Laporan Penjualan ────────────────────────────────────
    public function sales(Request $request)
    {
        $from   = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to     = $request->input('to',   now()->format('Y-m-d'));
        $search = $request->input('search');
        $method = $request->input('payment_method');

        $query = Sale::with(['user', 'customer'])
            ->completed()
            ->whereBetween('sale_date', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        $sales = $query->latest('sale_date')->paginate(20)->withQueryString();

        // Statistik periode
        $stats = Sale::completed()
            ->whereBetween('sale_date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(total_hpp) as total_hpp'),
                DB::raw('SUM(gross_profit) as total_profit'),
                DB::raw('AVG(total_amount) as avg_transaction'),
            )
            ->first();

        // Penjualan per hari (chart)
        $salesByDay = Sale::completed()
            ->whereBetween('sale_date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('COUNT(*) as total_trx'),
                DB::raw('SUM(total_amount) as revenue'),
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Per metode pembayaran
        $byPayment = Sale::completed()
            ->whereBetween('sale_date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select('payment_method')
            ->selectRaw('COUNT(*) as total_trx')
            ->selectRaw('SUM(total_amount) as revenue')
            ->groupBy('payment_method')
            ->get();

        $chartDays    = $salesByDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'));
        $chartRevenue = $salesByDay->pluck('revenue');
        $chartTrx     = $salesByDay->pluck('total_trx');

        return view('reports.sales', compact(
            'from',
            'to',
            'sales',
            'stats',
            'byPayment',
            'chartDays',
            'chartRevenue',
            'chartTrx'
        ));
    }

    // ─── Detail transaksi ─────────────────────────────────────
    public function saleDetail(Sale $sale)
    {
        $sale->load([
            'user',
            'customer',
            'items.product.unit',
            'items.saleItemLots.stockLot',
        ]);

        return view('reports.sale-detail', compact('sale'));
    }
}
