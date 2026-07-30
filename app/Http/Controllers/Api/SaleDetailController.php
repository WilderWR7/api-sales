<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SaleDetailController extends Controller
{
    /**
     * Display a summary of all sales statistics.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $totalRevenue = (float) Sale::sum('total');
        $totalSalesCount = (int) Sale::count();
        $averageTicket = $totalSalesCount > 0 ? (float) round((float) Sale::avg('total'), 2) : 0.0;
        $totalItemsSold = (int) SaleDetail::sum('quantity');
        $todayRevenue = (float) Sale::whereDate('created_at', today())->sum('total');

        $cancelledSalesCount = (int) Sale::onlyTrashed()->count();
        $cancelledRevenue = (float) Sale::onlyTrashed()->sum('total');

        $topProductDetail = SaleDetail::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name')
            ->first();

        $topProduct = [
            'name' => $topProductDetail?->product?->name,
            'total_quantity' => (int) ($topProductDetail?->total_qty ?? 0),
        ];

        return response()->json([
            'total_revenue' => round($totalRevenue, 2),
            'total_sales_count' => $totalSalesCount,
            'average_ticket' => $averageTicket,
            'total_items_sold' => $totalItemsSold,
            'today_revenue' => round($todayRevenue, 2),
            'cancelled_sales_count' => $cancelledSalesCount,
            'cancelled_revenue' => round($cancelledRevenue, 2),
            'top_product' => $topProduct,
        ]);
    }
}
