<?php
namespace App\Http\Controllers\Api\Docs;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class SaleDocs
{
    #[OA\Get(
        path: "/sales",
        summary: "List all sales",
        description: "Returns a paginated list of sales with their associated details.",
        tags: ["Sales"],
        security: [["bearerAuth" => []]],
        parameters: [
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
                description: "Number of sales per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Paginated list of sales",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "total", type: "number", format: "float", example: 16500.0),
                                    new OA\Property(property: "created_at", type: "string", example: "2026-07-30"),
                                    new OA\Property(
                                        property: "details",
                                        type: "array",
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: "product", ref: "#/components/schemas/Product"),
                                                new OA\Property(property: "quantity", type: "integer", example: 2),
                                                new OA\Property(property: "subtotal", type: "number", format: "float", example: 15000.0)
                                            ]
                                        )
                                    ),
                                    new OA\Property(
                                        property: "user",
                                        type: "object",
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: "name", type: "string", example: "Wilder Mayta")
                                        ]
                                    )
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "links",
                            type: "object",
                            properties: [
                                new OA\Property(property: "first", type: "string", example: "http://api-sales.test/api/sales?page=1"),
                                new OA\Property(property: "last", type: "string", example: "http://api-sales.test/api/sales?page=1"),
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
    public function index()
    {
    }

    #[OA\Post(
        path: "/sales",
        summary: "Create a new sale",
        description: "Registers a new sale for the authenticated user, creates its details, calculates subtotals/total, and deducts product stock in a single transaction.",
        tags: ["Sales"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["items"],
                properties: [
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "product_id", type: "integer", example: 1),
                                new OA\Property(property: "quantity", type: "integer", example: 2)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Sale created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Sale created successfully"),
                        new OA\Property(property: "sale_id", type: "integer", example: 1),
                        new OA\Property(property: "total", type: "number", format: "float", example: 16500.0)
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error or insufficient stock",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Insufficient stock for product: Laptop HP")
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthorized"
            )
        ]
    )]
    public function store()
    {
    }

    #[OA\Delete(
        path: "/sales/{id}",
        summary: "Delete a sale",
        description: "Deletes a sale, removes its details, and restores product stock.",
        tags: ["Sales"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID of the sale to delete",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Sale deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Sale deleted successfully")
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Sale not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Sale not found")
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthorized"
            )
        ]
    )]
    public function destroy()
    {
    }
}
