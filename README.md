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

- [Operation Helpers](/docs/helper/operation.md)

## Rules

- OpenApiTools\PHPStan\Rules\OpenApi\Schema\SchemaNameRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Schema\PropertiesRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Schema\RequiredPropertiesRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerInvokeMethodRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerMethodParametersRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ControllerMethodsRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\DescriptionRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\PathRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\RequestBodyReferenceRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\ResponsesRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\SummaryRule
  - todo;
- OpenApiTools\PHPStan\Rules\OpenApi\Operation\TagCountRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\EnumRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\MissingSchemaPropertiesRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\MissingSchemaRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\NullableRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\FormRequest\RequiredRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\Resource\BooleanPropertyRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\Resource\MissingReturnPropertyRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\Resource\MissingSchemaPropertiesRule
  - todo;
- OpenApiTools\PHPStan\Rules\Laravel\Resource\MissingSchemaPropertyRule
  - todo;
  
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

If you want to ignore rules to specific files or file pattern, add `ignoreErrors` to your `phpstan.neon`. 
Example, how to ignore `openApiTools.missingRequestSchemaAttribute` errors:
```yaml
    ignoreErrors:
        -
          identifier: openApiTools.missingRequestSchemaAttribute
          paths:
            - app/Path/To/Directory/*
```

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
