<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationErrorResponse extends OA\Response
{
    public function __construct()
    {
        parent::__construct(
            ref: AuthorizationErrorResponseSchema::class,
            response: Response::HTTP_FORBIDDEN,
        );
    }
}
