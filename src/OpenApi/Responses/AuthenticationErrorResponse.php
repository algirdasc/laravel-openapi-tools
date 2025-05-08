<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationErrorResponse extends OA\Response
{
    public function __construct()
    {
        parent::__construct(
            ref: AuthenticationErrorResponseSchema::class,
            response: Response::HTTP_UNAUTHORIZED,
        );
    }
}
