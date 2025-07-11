# Laravel OpenAPI toolkit

This toolkit provides rules for PHPStan and utilities for working with OpenAPI specifications in Laravel framework.

It should help you maintain documentation of your codebase more easily and lower data duplication as much as possible. 

Toolkit uses [PHPStan](https://phpstan.org) to statically check code for errors, leading to Swagger/OpenAPI specification errors.

__!! Only OpenApi attributes with named arguments is supported and checked !!__

## Requirements

- PHP 8.3 or higher
- PHPStan
- Laravel 11.x or higher
- Composer

## Installation

Install the package via composer:

```bash
composer require algirdasc/laravel-openapi-tools
```

Add rules to `phpstan.neon` config:
```yaml
includes:
    - vendor/algirdasc/laravel-openapi-tools/extension.neon
```

Run phpstan to analyze your project: 
```bash
vendor/bin/phpstan
```

## Helper classes

Toolkit includes helper class to ease and minimize duplication while defining API documentation in Laravel framework. Usage of them is completely optional, although is recomended.

- [Operation Helpers](/docs/helper/operation.md)
- [Response Helpers](/docs/helper/response.md)
- [Request Body Helpers](/docs/helper/request_body.md)

## Rules

Quick overview of rules provided in toolkit and their functions. List of incomplete and suspect for changes.

### Schemas - `OA\Schema`

- OpenApiTools\PHPStan\Rules\OpenApi\Schema\PropertiesRule
  - Validates property type
  - Validates property format
  - Validates property case
  - Validates property date type & format
  - Validated property `ref` class to have schema
- OpenApiTools\PHPStan\Rules\OpenApi\Schema\RequiredPropertiesRule
  - Validates if required properties defined in properties list
- OpenApiTools\PHPStan\Rules\OpenApi\Schema\SchemaNameRule
  - Validates naming convention

### Operation - `OA\Get`, `OA\Post`,`OA\Put`,`OA\Patch`,`OA\Delete`

- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule
  - Validates `__invoke` method has correct schema scope 
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerMethodParametersRule
  - Validates if operation attributes has methods
  - Validates method parameter types
  - Validates method parameters vs. operation path parameters
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerMethodsRule
  - Validates controller method count if `__invoke` is used
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\DescriptionRule
  - Validates `description` length
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\PathRule
  - Validates path leading & trailing slash 
  - Validates whether path parameters defined in operation schema parameters
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\RequestBodyReferenceRule
  - Validates whether `requestBody` is set when `FormRequest` instance provided in method parameters
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ResponsesRule
  - Validates if `Success` response is provided
  - Validates if `Error` response is provided
  - Validates if `Authorization` response is provided
  - Validates if `Unprocessable` response is provided when `FormRequest` instance provided in method parameters
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\SummaryRule
  - Validates `summary` optimal length
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\TagCountRule
  - Validates `tags` count

### Form Request - `Illuminate\Foundation\Http\FormRequest`

- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\EnumRule
  - Validates whether `enum` parameter is set in `OA\Property`, depending on validation rules
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\MissingSchemaPropertiesRule
  - Validates whether `properties` set 
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\MissingSchemaRule
  - Validates whether `OA\Schema` attribute is set
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\NullableRule
  - Validates whether `nullable` parameter is set in `OA\Property`, depending on validation rules
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\RequiredRule
  - Validates whether `required` parameter is set in `OA\Property`, depending on validation rules

### Json Resource - `Illuminate\Http\Resources\Json\JsonResource`
- OpenApiTools\PHPStan\Rules\Laravel\Resource\BooleanPropertyRule
  - Validates `boolean` property to match isset/haser naming convetion - `is_something`, `has_something` 
- OpenApiTools\PHPStan\Rules\Laravel\Resource\MissingReturnPropertyRule
  - Validates whether `OA\Schema` property returned in `toArray()` method (plain array return only)
- OpenApiTools\PHPStan\Rules\Laravel\Resource\MissingSchemaPropertiesRule
  - Validates whether `OA\Schema` contains `properties` parameter (plain array return only)
- OpenApiTools\PHPStan\Rules\Laravel\Resource\MissingSchemaPropertyRule
  - Validates whether returned property is in `OA\Schema` properties (plain array return only)
  
## Customizations

### Ignoring rules

You can ignore a specific rule one time, all you need to do is to add a doc comment where this error happens.
Example how to ignore `openApiTools.missingRequestSchemaAttribute` in a specific file:

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @phpstan-ignore openApiTools.missingJsonResourceSchemaAttribute
 */
class MyResource extends JsonResource
{
    public function toArray(mixed $request): array
    {
        return [
            'some_property' => $this->resource->some_property,
        ]);
    }
}
```

```php
...
#[OA\Schema(
    schema: 'Resources.Books.BookResource',
    properties: [
        /** @phpstan-ignore openApiTools.booleanInJsonResourceMustStartWithIs */
        new OA\Property('truncated', type: 'boolean', example: true),
    ],
)]
class BookResource extends JsonResource
...
```

If you want to ignore rules to specific files or file pattern, add `ignoreErrors` to your `phpstan.neon`. 
Example, how to ignore `openApiTools.missingRequestSchemaAttribute` errors:
```yaml
    ignoreErrors:
        -
          identifier: openApiTools.missingRequestSchemaAttribute
          paths:
            - app/Path/To/Directory/*
```

More on error ignoring - [PHPStan documentation](https://phpstan.org/user-guide/ignoring-errors)

### Schema naming rule override

By default, schema names should be named by namespaces separated by dot, for example Laravel resource `App\Http\Resources\SomeController\SomeResource`
should be named `Resources.SomeController.SomeResource`, 
but if you want to change to your customized rule, you can do so by specifying class, implementing `SchemaNameGeneratorInterface` in your `phpstan.neon`. For example:

```yaml
...
services:
  schemaNameGenerator:
    class: OpenApiTools\PHPStan\Generators\LaravelSchemaNameGenerator
...
```

### Property naming rule override

By default, property names should be named using `snake_case` naming convention, 
but if you want to change to `camelCase` or your customized rule, you can do so by specifying class, implementing `PropertyNameGeneratorInterface` in your `phpstan.neon`. For example:

```yaml
...
services:
  propertyNameGenerator:
    class: OpenApiTools\PHPStan\Generators\CamelCasePropertyNameGenerator
...
```
