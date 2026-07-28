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
        $products = Product::paginate($perPage);

        return ProductResource::collection($products);
    }
}
