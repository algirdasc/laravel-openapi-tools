<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\RequestBody;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @codeCoverageIgnore
 */
class RequiredRequestBody extends OA\RequestBody
{
    /**
     * @template T of FormRequest
     * @param class-string<T> $ref
     */
    public function __construct(
        string $ref,
    ) {
        parent::__construct(
            required: true,
            content: new OA\JsonContent(ref: $ref)
        );
    }
}
