<?php

declare(strict_types=1);

namespace OpenApiTools\PHPStan\Rules\OpenApi\Operation\Validators;

use OpenApi\Annotations\Operation;
use OpenApi\Generator;
use OpenApiTools\PHPStan\Helpers\RuleIdentifier;
use OpenApiTools\PHPStan\Rules\OpenApi\Operation\ValidatorInterface;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionMethod;
use PHPStan\Rules\RuleErrorBuilder;
use Symfony\Component\HttpFoundation\Response;

readonly class ResponsesValidator implements ValidatorInterface
{
    private const array ERROR_RESPONSES = [
        Response::HTTP_BAD_REQUEST,
    ];

    public function validate(ReflectionClass|ReflectionMethod $reflection, Operation $operation): array
    {
        $errors = [];

        $responses = !Generator::isDefault($operation->responses) ? $operation->responses : [];
        $hasRequestBody = !Generator::isDefault($operation->requestBody);

        $successResponseFound = false;
        $errorResponseFound = false;
        $unauthorizedResponseFound = false;
        $unprocessableResponseFound = false;
        foreach ($responses as $response) {
            if ($response->response >= Response::HTTP_OK && $response->response < Response::HTTP_MULTIPLE_CHOICES) {
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
            $errors[] = RuleErrorBuilder::message(sprintf('Operation "%s" must have success response set', $operation->path))
                ->identifier(RuleIdentifier::identifier('noSuccessResponse'))
                ->build();
        }

        if ($unauthorizedResponseFound === false) {
            $errors[] = RuleErrorBuilder::message(sprintf('Operation for "%s" must have unauthorized response set', $operation->path))
                ->identifier(RuleIdentifier::identifier('noUnauthorizedResponse'))
                ->build();
        }

        if ($errorResponseFound === false) {
            $errors[] = RuleErrorBuilder::message(sprintf('Operation for "%s" must have error response set', $operation->path))
                ->identifier(RuleIdentifier::identifier('noErrorResponse'))
                ->build();
        }

        if ($unprocessableResponseFound === false && $hasRequestBody) {
            $errors[] = RuleErrorBuilder::message(sprintf('Operation for "%s" must have unprocessable response set', $operation->path))
                ->identifier(RuleIdentifier::identifier('noUnprocessableResponse'))
                ->build();
        }

        return $errors;
    }
}
