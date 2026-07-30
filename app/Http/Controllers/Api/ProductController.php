<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Resources\Api\ProductResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 10);

        $products = Product::select('products.*')
            ->selectRaw('
                (
                    SELECT COALESCE(SUM(quantity), 0)
                    FROM sale_details
                    WHERE sale_details.product_id = products.id
                ) as total_sold
            ')
            ->orderByRaw('CASE WHEN stock > 0 THEN 1 ELSE 0 END DESC')
            ->orderBy('total_sold', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return ProductResource::collection($products);
    }
}
