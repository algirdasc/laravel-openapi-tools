# Operation helpers

Operation helpers extends standard OpenApi operation attributes by adding addition features, which help defining API specs.

## `queryParameters`

Query parameters adds `FormRequest` schema properties to query parameters without explicitly copying them. Let's say
you have following Request in your project:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    required: ['date_from', 'date_to'],
    properties: [
        new OA\Property('date_from', type: 'string', format: 'date-time', example: '2025-05-01T00:00:00Z'),
        new OA\Property('date_to', type: 'string', format: 'date-time', example: '2025-06-01T00:00:00Z'),
    ]
)]
class MyQueryRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'date_from' => 'required|date|before:date_to',
            'date_to' => 'required|date|after:date_from',
        ];
    }
}
```
You'd have to copy schema properties to `parameters` in your operation attribute, which would create duplication, and we do not want that, but using `queryParameters` helper class will parse the request schema 
and add schema properties to query parameters:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MyQueryRequest;
use App\Http\Resources\MyResource;
use OpenApi\Attributes as OA;
use OpenApiTools\OpenApi\Operation;

#[Operation\Get(
    path: '/api/endpoint',
    queryParameters: MyQueryRequest::class,
)]
final class MyController extends Controller
{
    public function __invoke(MetricGetRequest $request): MyResource
    {
        return new MyResource('some data');
    }
}
```

