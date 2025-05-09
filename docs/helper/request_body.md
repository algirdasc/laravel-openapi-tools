# Request Body helper

## `RequiredRequestBody`

This class is a shortcut class for `requestBody` parameter, and it includes set `requestBody` as required. It references
to your prefered `FormRequest`:

### From:

```php
#[OA\Post(
    path: '/v1/endpoint',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: StoreRequest::class)
    ),        
)]
public function store(StoreRequest $request): MyResource
{
    return new MyResource($postInstallScript);
}
```

### To:

```php
#[OA\Post(
    path: '/v1/endpoint',
    requestBody: new RequiredRequestBody(ref: StoreRequest::class),
)]
public function store(StoreRequest $request): MyResource
{
    return new MyResource($postInstallScript);
}
```
