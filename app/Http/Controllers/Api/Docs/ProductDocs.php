<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Docs;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Schema(
    schema: "Product",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Laptop HP"),
        new OA\Property(property: "price", type: "number", format: "float", example: 850.50),
        new OA\Property(property: "stock", type: "integer", example: 15),
        new OA\Property(property: "image", type: "string", nullable: true, example: "https://example.com/images/laptop.jpg")
    ]
)]
class ProductDocs
{
    #[OA\Get(
        path: "/products",
        summary: "List all products",
        description: "Returns a paginated list of products registered in the database, ordered by stock availability and sales count. Supports filtering by product name.",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Filter products by name (case-insensitive search)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number to retrieve",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Number of products per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Paginated list of products",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Product")
                        ),
                        new OA\Property(
                            property: "links",
                            type: "object",
                            properties: [
                                new OA\Property(property: "first", type: "string", example: "http://api-sales.test/api/products?page=1"),
                                new OA\Property(property: "last", type: "string", example: "http://api-sales.test/api/products?page=1"),
                                new OA\Property(property: "prev", type: "string", nullable: true, example: null),
                                new OA\Property(property: "next", type: "string", nullable: true, example: null)
                            ]
                        ),
                        new OA\Property(
                            property: "meta",
                            type: "object",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "from", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 1),
                                new OA\Property(property: "per_page", type: "integer", example: 10),
                                new OA\Property(property: "to", type: "integer", example: 1),
                                new OA\Property(property: "total", type: "integer", example: 1)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthorized"
            )
        ]
    )]
    public function index() {}
}
