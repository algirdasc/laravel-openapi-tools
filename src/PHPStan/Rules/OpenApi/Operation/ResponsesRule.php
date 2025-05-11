<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation;

use OpenApi\Generator;
use OpenApiTools\PHPStan\Collectors\ClassOperationCollector;
use OpenApiTools\PHPStan\Collectors\MethodOperationCollector;
use OpenApiTools\PHPStan\DTO\OperationAttribute;
use OpenApiTools\PHPStan\Helpers\NodeHelper;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Traits\IteratesOverCollection;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @implements Rule<CollectedDataNode>
 */
readonly class ResponsesRule implements Rule
{
    use IteratesOverCollection;

    private const array ERROR_RESPONSES = [
        Response::HTTP_BAD_REQUEST,
    ];

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];

        /** @var OperationAttribute $operationAttribute */
        foreach ($this->getIterator($node, [MethodOperationCollector::class, ClassOperationCollector::class]) as $operationAttribute) {
            $operation = $operationAttribute->getOperation();
            $operationName = sprintf('%s %s', strtoupper($operation->method), $operation->path);

            $responses = !Generator::isDefault($operation->responses) ? $operation->responses : [];
            $responsesNode = NodeHelper::findInArgsByName($operationAttribute->getAttribute()->args, 'responses');
            $hasRequestBody = !Generator::isDefault($operation->requestBody);

            $successResponseFound = false;
            $errorResponseFound = false;
            $unauthorizedResponseFound = false;
            $unprocessableResponseFound = false;
            foreach ($responses as $response) {
                if ($response->response >= Response::HTTP_OK && $response->response < Response::HTTP_BAD_REQUEST) {
                    $successResponseFound = true;
                }

                if ($response->response === Response::HTTP_UNAUTHORIZED) {
                    $unauthorizedResponseFound = true;
                }

                if ($response->response === Response::HTTP_UNPROCESSABLE_ENTITY) {
                    $unprocessableResponseFound = true;
                }

                if ($response->response >= Response::HTTP_INTERNAL_SERVER_ERROR || in_array($response->response, self::ERROR_RESPONSES)) {
                    $errorResponseFound = true;
                }
            }

            if ($successResponseFound === false) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" must have success response set', $operationName))
                    ->identifier(RuleIdentifier::identifier('noSuccessResponse'))
                    ->file($operationAttribute->getFile())
                    ->line($responsesNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if ($unauthorizedResponseFound === false) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" must have unauthorized response set', $operationName))
                    ->identifier(RuleIdentifier::identifier('noUnauthorizedResponse'))
                    ->file($operationAttribute->getFile())
                    ->line($responsesNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if ($errorResponseFound === false) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" must have error response set', $operationName))
                    ->identifier(RuleIdentifier::identifier('noErrorResponse'))
                    ->file($operationAttribute->getFile())
                    ->line($responsesNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }

            if ($unprocessableResponseFound === false && $hasRequestBody) {
                $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" must have unprocessable response set', $operationName))
                    ->identifier(RuleIdentifier::identifier('noUnprocessableResponse'))
                    ->file($operationAttribute->getFile())
                    ->line($responsesNode?->getLine() ?? $operationAttribute->getAttribute()->getLine())
                    ->build();
            }
        }

        return $errors;
    }
}
