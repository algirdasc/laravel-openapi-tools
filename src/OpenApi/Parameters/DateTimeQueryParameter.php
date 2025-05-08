<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Parameters;

use OpenApi\Attributes as OA;

class DateTimeQueryParameter extends OA\QueryParameter
{
    public function __construct(
        string $name,
        bool $required = true,
        ?string $example = '2025-01-01T00:00:00Z',
        ?string $description = 'the date-time notation as defined by RFC 3339, section 5.6'
    ) {
        parent::__construct(
            name: $name,
            description: $description,
            required: $required,
            schema: new OA\Schema(
                type: 'string',
                example: $example,
            )
        );
    }
}
