# Laravel API Concern

Reusable JSON response helpers and API exception rendering for Laravel 11, 12, and 13.

## Installation

```bash
composer require curly-deni/laravel-api-concern
```

Laravel auto-discovers the service provider. It registers the package's English and Russian translations. To customize them, publish the translation files:

```bash
php artisan vendor:publish --tag=api-concern-translations
```

Published translations are placed in `lang/vendor/api-concern` and override the package defaults.

## Responses

Use `HasApiResponses` in a controller to return the standard response envelopes:

```php
use Aesis\ApiConcern\Concerns\HasApiResponses;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    use HasApiResponses;

    public function store()
    {
        return $this->created(['id' => 1], ['request_id' => 'abc']);
    }
}
```

Available helpers are `data`, `created`, `accepted`, `noContent`, `resource`, `createdResource`, `acceptedResource`, and `error`. Data responses use a `data` key and include `meta` only when provided. Error responses use an `error` object containing `code`, `message`, and optional `details`.

You can also inject `Aesis\ApiConcern\Http\ApiResponseFactory` directly.

## Exception rendering

Add the renderer to the existing `withExceptions` callback in `bootstrap/app.php`. Keep the path check in the application so web exceptions continue through Laravel's normal handling:

```php
use Aesis\ApiConcern\Http\ApiExceptionRenderer;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Throwable;

->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (Throwable $exception, Request $request) {
        if (! $request->is('api/v1/*')) {
            return null;
        }

        return app(ApiExceptionRenderer::class)->render($exception);
    });
})
```

For matching requests, the renderer converts validation, authentication, authorization, missing model, throttling, HTTP, and unexpected exceptions to a consistent JSON shape. Throw `Aesis\ApiConcern\Exceptions\ApiException` for an application-specific error:

```php
throw new ApiException('account_locked', 'This account is locked.', 423, ['retry_after' => 60]);
```

## Translations

The renderer uses Laravel's `api-concern::messages.*` translation keys. English and Russian defaults are included. Set the application's locale to choose a language, or publish the files and edit them for application-specific wording.

## Testing

```bash
composer test
composer analyse
composer format
```

## License

MIT. See [LICENSE.md](LICENSE.md).
