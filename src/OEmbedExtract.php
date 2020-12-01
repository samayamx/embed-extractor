<?php

namespace Samaya\Embed;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Samaya\Embed\Extract
 * @method static string getImage(string $url, string $property = null)
 * @method static string|null getHtml(string $url)
 */
class OEmbedExtract extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Extract::class;
    }
}
