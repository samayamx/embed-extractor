<?php

namespace Samaya\Embed;

use Symfony\Component\DomCrawler\Crawler;

class Extract
{
    const USER_AGENT = 'Samaya-Embed 0.1';

    protected $useragent;

    /** @var Crawler[] */
    protected $cachedCrawlers = [];

    /** @var array */
    protected $cachedResponses = [];

    protected $cachedOembedData = [];

    /**
     * Extract constructor.
     * @param $useragent
     */
    public function __construct(string $useragent = null)
    {
        $this->useragent = is_null($useragent)
            ? self::USER_AGENT
            : $useragent;
    }

    public function getImage(string $url, string $property = null): ?string
    {
        $this->validateUrl($url);

        $filter = $this->getMetaImageFilter($property ?? 'og:image');

        $crawler = $this->getCrawler($url);
        $node = $crawler->filterXPath($filter);
        $content = $node->extract(['content']);

        return $content[0];
    }

    protected function getMetaImageFilter(string $property = 'og:image'): string
    {
        switch ($property) {
            case 'og:image':
                return $filter = "//meta[@property='${property}']";
            case 'twitter:image':
                return $filter = "//meta[@name='${property}']";
            default:
                return '';
        }
    }

    public function getHtml(string $url): ?string
    {
        $this->validateUrl($url);

        $oembedData = $this->getOembedData($url);

        return str_replace("\n", "", $oembedData['html']);
    }

    protected function getOembedData(string $url): ?array
    {
        if (isset($this->cachedOembedData[$url])) {
            return $this->cachedOembedData[$url];
        }

        $crawler = $this->getCrawler($url);
        $node = $crawler->filterXPath("//link[@rel='alternate'][@type='application/json+oembed']");
        $content = $node->extract(['href']);

        $oembedUrl = $content[0];

        $jsonResponse = $this->getUrlData($oembedUrl);

        $this->cachedOembedData[$url] = json_decode($jsonResponse, true);
        return $this->cachedOembedData[$url];
    }

    public function validateUrl(string $url)
    {
        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('Invalid url %s', $url));
        }
    }

    protected function getCrawler(string $url): Crawler
    {
        if (isset($this->cachedCrawlers[$url])) {
            return $this->cachedCrawlers[$url];
        }
        $response = $this->getUrlData($url);

        $crawler = new Crawler($response);

        $this->cachedCrawlers[$url] = $crawler;
        return $this->cachedCrawlers[$url];
    }

    protected function getUrlData(string $url): string
    {
        if (isset($this->cachedResponses[$url])) {
            return $this->cachedResponses[$url];
        }
        $response = $this->retrieveUrlData($url);

        $this->cachedResponses[$url] = $response;
        return $response;
    }

    protected function retrieveUrlData(string $url): string
    {
        $options = [
          CURLOPT_USERAGENT => $this->useragent,
          CURLOPT_ENCODING => '',
          CURLOPT_FOLLOWLOCATION => true,
        ];

        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_HEADER] = true;
        $options[CURLOPT_RETURNTRANSFER] = 1;

        $handler = curl_init();
        curl_setopt_array($handler, $options);
        $response = curl_exec($handler);

        $status = curl_getinfo($handler, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($handler, CURLINFO_HEADER_SIZE);

        $body = substr($response, $headerSize);
        curl_close($handler);

        if (empty($body) || $status != '200') {
            throw new \RuntimeException($status . ': Invalid response for ' . $url);
        }

        return $body;
    }
}
