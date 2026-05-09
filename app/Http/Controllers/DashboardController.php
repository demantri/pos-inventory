<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\FifoService;
use App\Services\HppService;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __construct(
        private HppService  $hppService,
        private FifoService $fifoService,
    ) {}

    public function index()
    {
        $today   = now()->toDateString();
        $summary = $this->hppService->getDailySummary($today);

        // Produk stok menipis
        $lowStockProducts = Product::active()
            ->with(['unit', 'stockLots' => fn($q) => $q->available()])
            ->get()
            ->filter(fn($p) => $p->isLowStock())
            ->take(10);

        // 5 transaksi terakhir
        $recentSales = Sale::with(['user', 'customer'])
            ->completed()
            ->latest('sale_date')
            ->take(5)
            ->get();

        // Ringkasan bulan ini
        $monthStart = now()->startOfMonth()->toDateString();
        $monthSummary = $this->hppService->getHppReportByPeriod($monthStart, $today);

        return view('dashboard.index', compact(
            'summary',
            'lowStockProducts',
            'recentSales',
            'monthSummary',
        ));
    }
}
