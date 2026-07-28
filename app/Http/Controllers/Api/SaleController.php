<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSaleRequest;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends Controller
{
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
}
