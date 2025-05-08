<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class SuccessResponse extends OA\Response
{
    /**
     * @template T of JsonResource|ResourceCollection
     * @param class-string<T> $ref
     */
    public function __construct(string $ref, int $response = Response::HTTP_OK)
    {
        parent::__construct(
            response: $response,
            description: 'Success response',
            content: new OA\JsonContent(
                ref: $ref,
            ),
        );
    }
}
