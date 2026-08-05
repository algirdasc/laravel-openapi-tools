<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Helpers;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;

class NodeHelper
{
    /**
     * Attributes that are safe to keep on detached nodes.
     */
    private const KEPT_ATTRIBUTES = [
        'startLine' => true,
        'endLine' => true,
        'startFilePos' => true,
        'endFilePos' => true,
        'startTokenPos' => true,
        'endTokenPos' => true,
    ];

    /**
     * Deep clones nodes and drops every attribute except the positional ones.
     *
     * PHPStan attaches analysis state to the nodes it visits - e.g. "phpstanCachedTypes" on
     * closures, which holds Type objects that can reference a ClassReflection and, through it,
     * the non-serializable ClassReflectionFactory anonymous class. Collected data has to be
     * serializable, so nodes must be detached from that state before they are stored.
     *
     * @template TNode of Node
     *
     * @param array<TNode> $nodes
     *
     * @return array<TNode>
     */
    public static function detach(array $nodes): array
    {
        $traverser = new NodeTraverser(
            new CloningVisitor(),
            new class (self::KEPT_ATTRIBUTES) extends NodeVisitorAbstract {
                /**
                 * @param array<string, true> $keptAttributes
                 */
                public function __construct(private readonly array $keptAttributes)
                {
                }

                public function enterNode(Node $node): null
                {
                    $node->setAttributes(array_intersect_key($node->getAttributes(), $this->keptAttributes));

                    return null;
                }
            },
        );

        /** @var array<TNode> $detached */
        $detached = $traverser->traverse($nodes);

        return $detached;
    }

    /**
     * @param array<Node\Arg|Node\VariadicPlaceholder> $args
     * @param string $name
     * @return Node\Expr|null
     */
    public static function findInArgsByName(array $args, string $name): ?Node\Expr
    {
        foreach ($args as $arg) {
            if ($arg instanceof Node\VariadicPlaceholder) {
                continue;
            }

            if ($arg->name?->name === $name) {
                return $arg->value;
            }
        }

        return null;
    }
}
