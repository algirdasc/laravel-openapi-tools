<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Response(
    response: 'OpenApiTools.Responses.RequestAcceptedResponse',
    description: 'Success',
    content: new OA\JsonContent(
        properties: [
            new OA\Property('status', type: 'integer', example: Response::HTTP_OK),
            new OA\Property('success', type: 'boolean', example: true),
            new OA\Property('data', type: 'object', example: ['message' => 'Request accepted']),
        ]
    )
)]
abstract class RequestAcceptedResponseSchema
{
}
