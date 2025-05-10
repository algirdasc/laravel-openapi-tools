<?php

namespace Tests\PHPStan\Rules\OpenApi\Schema\Data;

use OpenApi\Attributes as OA;

#[OA\Schema(
    required: ['property1', 'property3'],
    properties: [
        new OA\Property(
            property: 'property1',
            required: ['sub-property1', 'sub-property3'],
            properties: [
                new OA\Property('sub-property1'),
                new OA\Property('sub-property2'),
            ]
        ),
        new OA\Property(
            property: 'property2',
            items: new OA\Items(
                required: ['item-property1', 'item-property3'],
                properties: [
                    new OA\Property('item-property1'),
                    new OA\Property('item-property2'),
                ]
            )
        )
    ]
)]
class RequiredPropertiesDataClass
{
}