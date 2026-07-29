<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSaleRequest;
use App\Models\Sale;
use App\Http\Resources\Api\SaleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends Controller
{
    /**
     * Display a listing of the sales.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 10);

        $sales = Sale::with('details.product')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return SaleResource::collection($sales);
    }
    /**
     * Store a newly created sale in storage.
     *
     * @param StoreSaleRequest $request
     * @return JsonResponse
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            $sale = Sale::createFromItems(
                (int) auth()->id(),
                $request->input('items')
            );

            return response()->json([
                'message' => 'Sale created successfully',
                'sale_id' => $sale->id,
                'total' => (float) $sale->total,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified sale from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $sale = Sale::with('details')->findOrFail($id);

        $sale->deleteWithStockRestoration();

        return response()->json([
            'message' => 'Sale deleted successfully',
        ], Response::HTTP_OK);
    }
}
