<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Response(
    response: 'OpenApiTools.Responses.UnprocessableEntityErrorResponse',
    description: 'Validation error',
    content: new OA\JsonContent(
        properties: [
            new OA\Property('status', type: 'integer', example: Response::HTTP_UNPROCESSABLE_ENTITY),
            new OA\Property('success', type: 'boolean', example: false),
            new OA\Property('error', properties: [
                new OA\Property('code', type: 'integer', example: 2004),
                new OA\Property('message', type: 'string', example: 'Some validation error'),
                new OA\Property('validation_messages', type: 'array', items: new OA\Items(
                    type: 'string',
                    example: 'Some validation error'
                )),
            ]),
        ]
    )
)]
abstract class UnprocessableEntityErrorResponseSchema
{
}
