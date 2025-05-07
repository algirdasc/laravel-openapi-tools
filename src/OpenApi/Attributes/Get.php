<?php

declare(strict_types=1);

namespace OpenApiTools\OpenApi\Attributes;

use App\Http\OpenApi\Parameters\Pagination\PageQueryParameter;
use App\Http\OpenApi\Parameters\Pagination\PerPageQueryParameter;
use App\Http\OpenApi\Parameters\Pagination\SortByQueryParameter;
use App\Http\OpenApi\Parameters\Pagination\SortDirectionQueryParameter;
use OpenApi\Attributes\Attachable;
use OpenApi\Attributes\ExternalDocumentation;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Server;
use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Get extends OA\Get
{
    /**
     * @param string|null $path
     * @param string|null $operationId
     * @param string|null $description
     * @param string|null $summary
     * @param array<array-key, mixed>|null $security
     * @param array<Server>|null $servers
     * @param RequestBody|null $requestBody
     * @param array<string>|null $tags
     * @param array<Parameter>|null $parameters
     * @param array<Parameter>|class-string<FormRequest>|null $queryParameters
     * @param array<Response>|null $responses
     * @param array<array-key, mixed>|null $callbacks
     * @param ExternalDocumentation|null $externalDocs
     * @param bool|null $deprecated
     * @param bool $paginated
     * @param array<string,mixed>|null $x
     * @param array<Attachable>|null $attachables
     * @noinspection PhpTooManyParametersInspection
     * @noinspection PhpRedundantDocCommentInspection
     */
    public function __construct(
        ?string $path = null,
        ?string $operationId = null,
        ?string $description = null,
        ?string $summary = null,
        ?array $security = null,
        ?array $servers = null,
        ?RequestBody $requestBody = null,
        ?array $tags = null,
        ?array $parameters = [],
        mixed $queryParameters = [],
        ?array $responses = null,
        ?array $callbacks = null,
        ?ExternalDocumentation $externalDocs = null,
        ?bool $deprecated = null,
        bool $paginated = false,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        $queryParameters = $this->getQueryParameters($queryParameters);
        $paginationParameters = [];
        if ($paginated) {
            $paginationParameters = [
                new OA\Parameter(ref: PageQueryParameter::class),
                new OA\Parameter(ref: PerPageQueryParameter::class),
                new OA\Parameter(ref: SortByQueryParameter::class),
                new OA\Parameter(ref: SortDirectionQueryParameter::class),
            ];
        }

        parent::__construct(
            path: $path,
            operationId: $operationId,
            description: $description,
            summary: $summary,
            security: $security,
            servers: $servers,
            requestBody: $requestBody,
            tags: $tags,
            parameters: [
                ...$parameters,
                ...$queryParameters,
                ...$paginationParameters,
            ],
            responses: $responses,
            callbacks: $callbacks,
            externalDocs: $externalDocs,
            deprecated: $deprecated,
            x: $x,
            attachables: $attachables,
        );
    }

    private function getQueryParameters(array|string $queryParameters): array
    {
        if (is_string($queryParameters)) {
            $attributes = (new \ReflectionClass($queryParameters))->getAttributes(OA\Schema::class);
            if (!$attributes) {
                return [];
            }

            $schema = (new \ReflectionClass($queryParameters))->getAttributes(OA\Schema::class)[0]->getArguments();

            $queryParameters = [];
            foreach ($schema['properties'] ?? [] as $property) {
                $queryParameters[] = new OA\QueryParameter(
                    name: $property->property,
                    description: !Generator::isDefault($property->description) ? $property->description : null,
                    schema: new OA\Schema(
                        properties: !Generator::isDefault($property->properties) ? $property->properties : null,
                        type: !Generator::isDefault($property->type) ? $property->type : null,
                        format: !Generator::isDefault($property->format) ? $property->format : null,
                        items: !Generator::isDefault($property->items) ? $property->items : null,
                        default: !Generator::isDefault($property->default) ? $property->default : null,
                        enum: !Generator::isDefault($property->enum) ? $property->enum : null,
                        example: !Generator::isDefault($property->example) ? $property->example : null,
                        nullable: !Generator::isDefault($property->nullable) ? $property->nullable : null,
                    )
                );
            }
        }

        return $queryParameters;
    }
}
