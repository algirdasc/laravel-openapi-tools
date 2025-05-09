<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\DTO;

use OpenApi\Attributes\Schema;
use PhpParser\Node\ArrayItem;

class ReturnStatement
{
    /**
     * @param array<ArrayItem> $items
     */
    public function __construct(
        private readonly string $class,
        private readonly string $file,
        private readonly int    $line,
        private readonly ?Schema $schema,
        private array $items = [],
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

    public function getLine(): int
    {
        return $this->line;
    }

    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    /**
     * @return array<ArrayItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array<ArrayItem> $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
