<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use App\Http\OpenApi\Schemas\PaginationResponseSchema;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class SuccessPaginatedResponse extends OA\Response
{
    /**
     * @param class-string $ref
     */
    public function __construct(string $ref)
    {
        parent::__construct(
            response: Response::HTTP_OK,
            description: 'Success response',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property('status', type: 'integer', example: Response::HTTP_OK),
                    new OA\Property('success', type: 'boolean', example: true),
                    new OA\Property('data', type: 'array', items: new OA\Items(ref: $ref)),
                    new OA\Property('meta', ref: PaginationResponseSchema::class),
                ]
            ),
        );
    }
}
