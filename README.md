# Laravel Opis JSON Schema

Laravel FormRequest With Opis JSON Schema Validator

Use [Opis JSON Schema](https://github.com/opis/json-schema) to validate your laravel form requests.

# installation

```bash
composer require mesak/laravel-opis-validator
```

Or you could directly reference it into your `composer.json` file as a dependency
  
```json
{
    "require": {
        "mesak/laravel-opis-validator": "^1.0.0"
    }
}
```
## Example

### Requests
```php
<?php

namespace App\Http\Requests;

use Mesak\LaravelOpisValidator\JsonSchemaRequest;

class JsonSchema extends JsonSchemaRequest
{
    protected $extendValidatorMessage = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '$schema' => "http://json-schema.org/draft-07/schema#",
            "type" => "object",
            "title" => "Base Preference",
            "description" => "Base Preference Setting",
            "properties" => [
                "limit" => [
                    "type" => "integer",
                    "minimum" => 5,
                    "maximum" => 15,
                    "title" => "limit",
                    "attrs" => [
                        "placeholder" => "limit (limit)"
                    ]
                ],
                "page" => [
                    "type" => "object",
                    "title" => "Page",
                    "attrs" => [
                        "placeholder" => "Page ( Page )"
                    ],
                    "properties" => [
                        "limit" => [
                            "type" => "integer"
                        ]
                    ]
                ]
            ],
            "additionalProperties" => false,
            "required" => [
                "limit",
                "page"
            ]
        ];
    }
```


### Controller
```php

use App\Http\Requests\JsonSchema as JsonSchemaRequest;

    public function update(JsonSchemaRequest $request)
    {
        dd($request->validated());
    }
```

use postman to test your request.

```
curl --location --request POST 'http://localhost/test/update' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "limit" :10,
    "page": {
        "limit" : 10
    }
}'
```