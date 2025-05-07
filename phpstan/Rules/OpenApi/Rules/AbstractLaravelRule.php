<?php

declare(strict_types=1);

namespace Rules\Rules;

use;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\ShouldNotHappenException;

abstract class AbstractLaravelRule
{
    /**
     * @var array<class-string>
     */
    private array $validationCache = [];

    /**
     * @return class-string<Collector>
     */
    abstract protected function getCollector(): string;

    /**
     * @return array<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    abstract protected function validate(array $declaration): array;

    /**
     * @param CollectedDataNode $node
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $collectedData = $node->get($this->getCollector());
        foreach ($collectedData as $declarations) {
            foreach ($declarations as &$declaration) {
                $declaration = unserialize($declaration);
                $isParentScoped = $declaration['is_parent_scoped'];
                if ($isParentScoped) {
                    continue;
                }

                $errors = [
                    ...$errors,
                    ...$this->validate($declaration),
                ];
            }
        }

        return $errors;
    }

    protected function alreadyValidated(string $class, string $name): bool
    {
        $cacheKey = sprintf('%s::%s::%s', static::class, $class, $name);
        if (in_array($cacheKey, $this->validationCache)) {
            return true;
        }

        $this->validationCache[] = $cacheKey;

        return false;
    }
}
