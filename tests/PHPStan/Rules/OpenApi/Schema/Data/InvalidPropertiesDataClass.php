<?php

namespace Tests\PHPStan\Rules\OpenApi\Schema\Data;

use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property('duplicate-property'), // test case for duplicate names (should be ok!)
        new OA\Property(
            property: 'property1',
            properties: [
                new OA\Property('property1', type: 'bool'), // incorrect type
                new OA\Property('property1', type: 'boolean'), // duplicate property
                new OA\Property('duplicate-property'), // test case for duplicate property (should be ok!)
                new OA\Property('number_property', format: 'number'), // incorrect format
                new OA\Property('casedProperty'), // incorrect case
                new OA\Property('cased.property'), // incorrect case
                new OA\Property('cased property'), // incorrect case
                new OA\Property('some_date1_at', type: 'integer', format: 'date-time'), // date must be string
                new OA\Property('some_date2_at', type: 'string'), // date must have date format
                new OA\Property('referenced_property', ref: SchemalessDataClass::class), // reference without schema
                new OA\Property('referenced_property2', ref: 'oops'), // non-existing class
            ]
        ),
    ]
)]
class InvalidPropertiesDataClass
{
}