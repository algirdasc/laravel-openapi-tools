<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\DTO;

use OpenApi\Attributes\Schema;
use PhpParser\Node\Attribute;

readonly class SchemaAttribute
{
    public function __construct(
        private string $class,
        private string $file,
        private Schema $schema,
        private Attribute $attribute
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}
