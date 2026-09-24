<?php

namespace Tests\PHPStan\Rules\Laravel\FormRequest\Data;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property('enum-property-1'),
        new OA\Property('enum-property-2'),
        new OA\Property('enum-property-3'),
        new OA\Property('not-enum-property-1'),
        new OA\Property('not-enum-property-2'),
        new OA\Property('not-enum-property-3'),
        new OA\Property('enum-property-4'),
        new OA\Property('enum-property-5'),
        new OA\Property('not-enum-property-4'),
    ],
)]
class EnumRulesDataClass extends FormRequest
{
    public function rules(): array
    {
        return [
            'enum-property-1' => 'string|in:1,2,3',
            'enum-property-2' => ['string', 'in:1,2,3'],
            'enum-property-3' => ['string', Rule::in(['one', 'two', 'three'])],
            'not-enum-property-1' => 'string|min:1,2,3',
            'not-enum-property-2' => ['string', 'min:1,2,3'],
            'not-enum-property-3' => ['required', 'string', Rule::unique(self::class, 'id')],
            'enum-property-4' => ['string', Rule::in(['one', 'two']), 'max:255'],
            'enum-property-5' => ['string', Rule::enum(EnumRulesDataEnum::class)],
            'not-enum-property-4' => ['string', Rule::exists('users', 'id')],
        ];
    }
}