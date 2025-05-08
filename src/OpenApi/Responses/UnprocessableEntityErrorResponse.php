<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityErrorResponse extends OA\Response
{
    public function __construct()
    {
        parent::__construct(
            ref: UnprocessableEntityErrorResponseSchema::class,
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
