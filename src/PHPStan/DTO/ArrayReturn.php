<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\DTO;

use PhpParser\Node\ArrayItem;

class ArrayReturn
{
    /**
     * @param array<ArrayItem> $items
     */
    public function __construct(
        private readonly string $class,
        private readonly string $file,
        private readonly int    $line,
        private readonly bool   $isParentScoped,
        private array  $items = [],
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

    public function isParentScoped(): bool
    {
        return $this->isParentScoped;
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
