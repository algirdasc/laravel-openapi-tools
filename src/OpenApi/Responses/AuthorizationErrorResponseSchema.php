<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Response(
    response: 'OpenApiTools.Responses.AuthorizationErrorResponse',
    description: 'Authorization error response',
    content: new OA\JsonContent(
        properties: [
            new OA\Property('status', type: 'integer', example: Response::HTTP_FORBIDDEN),
            new OA\Property('success', type: 'boolean', example: false),
            new OA\Property('error', properties: [
                new OA\Property('code', type: 'integer', example: 2000),
                new OA\Property('message', type: 'string', example: 'Unauthorized'),
                new OA\Property('Environment', type: 'string', example: 'production'),
            ], type: 'object'),
        ]
    )
)]
abstract class AuthorizationErrorResponseSchema
{
}
