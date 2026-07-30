<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Docs;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class SaleDetailDocs
{
    #[OA\Get(
        path: "/sales/summary",
        summary: "Get sales summary statistics",
        description: "Returns summary statistics including total revenue, sales count, average ticket, items sold, today's revenue, cancelled sales count/revenue, and top selling product.",
        tags: ["Sales"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Sales summary statistics",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "total_revenue", type: "number", format: "float", example: 12500.50),
                        new OA\Property(property: "total_sales_count", type: "integer", example: 15),
                        new OA\Property(property: "average_ticket", type: "number", format: "float", example: 833.37),
                        new OA\Property(property: "total_items_sold", type: "integer", example: 42),
                        new OA\Property(property: "today_revenue", type: "number", format: "float", example: 1200.00),
                        new OA\Property(property: "cancelled_sales_count", type: "integer", example: 2),
                        new OA\Property(property: "cancelled_revenue", type: "number", format: "float", example: 450.00),
                        new OA\Property(
                            property: "top_product",
                            type: "object",
                            properties: [
                                new OA\Property(property: "name", type: "string", nullable: true, example: "Laptop HP"),
                                new OA\Property(property: "total_quantity", type: "integer", example: 10)
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
