# Laravel OpenAPI tools

A Laravel package that provides rules for PHPStan and utilities & tools for working with OpenAPI specifications in Laravel framework.

# Rule set

TODO;

## Requirements

- PHP 8.2 or higher
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

## Customizations

### Schema naming rule override

By default, schema names should be named by namespaces separated by dot, for example Laravel resource `App\Http\Resources\SomeController\SomeResource`
should be named `Resources.SomeController.SomeResource`, 
but if you want to change to your customized rule, you can do so by specifying class, implementing `SchemaNameGeneratorInterface` in your phpstan config file. For example:

```yaml
...
services:
  schemaNameGenerator:
    class: OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators\LaravelSchemaNameGenerator
...
```

### Property naming rule override

By default, property names should be named using `snake_case` naming convention, 
but if you want to change to `kebabCase` or your customized rule, you can do so by specifying class, implementing `PropertyNameGeneratorInterface` in your phpstan config file. For example:

```yaml
...
services:
  propertyNameGenerator:
    class: OpenApiTools\PHPStan\Rules\OpenApi\Schema\Generators\KebabCasePropertyNameGenerator
...
```
