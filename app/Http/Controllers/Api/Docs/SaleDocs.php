<?php
namespace App\Http\Controllers\Api\Docs;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class SaleDocs
{
    #[OA\Post(
        path: "/sales",
        summary: "Create a new sale",
        description: "Registers a new sale, creates its details, calculates subtotales/total, and deducts product stock in a single transaction.",
        tags: ["Sales"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "items"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1),
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
                        new OA\Property(property: "message", type: "string", example: "Venta creada correctamente"),
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
}
