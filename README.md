# Laravel OpenAPI toolkit

This toolkit provides rules for PHPStan and utilities for working with OpenAPI specifications in Laravel framework.

Toolkit uses [PHPStan](https://phpstan.org) to statically check code for errors, leading to Swagger/OpenAPI specification errors.
Only OpenApi attributes with named arguments is supported and checked.

Rule set includes following checks:

- schema property type & format check
- property inconsistencies between declared OpenApi schema and `JsonResource` return array or `FormRequest` validation rules (when possible):
  - does `JsonResource` fields declared in schema and vice versa
  - does `FormRequest` validations rules declared in schema properties with valid options (`required`, `nullabe`, `enumarable`, etc.)
- missing or incorrect operation parameters, properties, response types
  - does response include success, error, unprocessable or authorization errors
  - is request body provided, when `FormRequest` has validation
  - is request body referencing to declared schema
  - is parameter count & type is exactly the same as in controller method and vice versa
- naming consistency checks:
  - does returned booleans start with `is_` prefix
  - does returned dates has set `type` and `format`
  - does request has all enumerated values listed
- more...

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
