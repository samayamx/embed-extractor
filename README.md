# Embed Extractor

Simple class to extract properties for embedding 3rd party resources supporting OEmbed protocol.

_Currently we support only resources with [Discovery](https://oembed.com/#section4) links_

### installation

    composer require samayamx/embed-extractor
    
### requirements

- cURL to get resources
- ext-json to extract data from oembed response

### example

You can use this simple service in a controller without too much hassle:

```php
use Samaya\Embed\Extract;

$extractor = new Extract(
    'User-Agent-Cool',
    '1234567890123456',
    '0123456789abcdef0123456789abcdef'
);

Route::get('/embed', function (Request $request) use ($extractor) {
    $url = $request->get('url');
    try {
        $image = $extractor->getImage($url);
        $html = $extractor->getHtml($url);

        return response()->json(compact('url', 'html', 'image'));
    } catch (\DomainException $domainException) {
        // for Facebook "OpenGraph" errors
        return response()->json(
          ['message' => $domainException->getMessage()],
          $domainException->getCode()
        );
    } catch (\Exception $exception) {
        // handle other exceptions as well
        return response(
          $exception->getMessage(),
          $exception->getCode()
        );
    }
});
```

If you prefer, you can use the provided Laravel facade or configuration
to provide the service with required parameters:

```php
use Samaya\Embed\Extract;

// injected into the action handler:
Route::get('/embed', function (Extract $extractor, Request $request) {
    // ...
});

use Samaya\Embed\OEmbedExtract;

// using the facade
$image = OEmbedExtract::getImage($url);
$html = OEmbedExtract::getHtml($url);
```

### configuration with Laravel

To use the provided facade or automatic service instantiation, you provide
the required secrets in environment and/or published configuration.

See `.env.example` for an example of the default environment variables.

Publish the configuration if you need to tweak the identifiers to avoid
collision with another package:

    php artisan vendor:publish --provider=Samaya\\Embed\\OEmbedExtractProvider --tag=config

### supports

- Facebook
- Vimeo
- Youtube
- Samaya Share
