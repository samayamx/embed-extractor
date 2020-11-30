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

// use the extractor directly without parameters to inject the service
// into the route handler
Route::get('/embed', function (Extract $extract, Request $request) {
    $url = $request->get('url');
    try {
        $image = $extract->getImage($url);
        $html = $extract->getHtml($url);

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

### supports

- Facebook
- Vimeo
- Youtube
- Samaya Share
