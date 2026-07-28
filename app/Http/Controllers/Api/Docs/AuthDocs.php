<?php

namespace App\Http\Controllers\Api\Docs;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Wilder Mayta"),
        new OA\Property(property: "email", type: "string", example: "wilder.mayta@example.com"),
        new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true, example: null),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-28T00:00:00.000000Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-07-28T00:00:00.000000Z")
    ]
)]
class AuthDocs
{
    #[OA\Post(
        path: "/register",
        summary: "Register new user",
        description: "Allows a new user to register in the application and returns an access token.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Wilder Mayta"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "wilder.mayta@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 6, example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "User registered successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "access_token", type: "string", example: "1|abcdef123456..."),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer")
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/login",
        summary: "User login",
        description: "Allows an existing user to log in and obtain an access token.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "wilder.mayta@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "User logged in successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User logged in successfully"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "access_token", type: "string", example: "2|abcdef123456..."),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer")
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Invalid credentials",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Invalid credentials")
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/logout",
        summary: "User logout",
        description: "Revokes and deletes the current access token of the authenticated user.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "User logged out successfully and token deleted"
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthorized"
            )
        ]
    )]
    public function logout() {}

    #[OA\Get(
        path: "/user",
        summary: "Get authenticated user profile",
        description: "Returns the data of the currently authenticated user.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Authenticated user profile data",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthorized"
            )
        ]
    )]
    public function user() {}
}
