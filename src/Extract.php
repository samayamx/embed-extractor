<?php

namespace Samaya\Embed;

use Symfony\Component\DomCrawler\Crawler;

class Extract
{
    const USER_AGENT = 'Samaya-Embed 0.3';

    protected $useragent;

    protected $fbAppId = '';
    protected $fbAppSecret = '';

    /** @var Crawler[] */
    protected $cachedCrawlers = [];

    /** @var array */
    protected $cachedResponses = [];

    protected $cachedOembedData = [];

    /**
     * Extract constructor.
     * @param $useragent
     */
    public function __construct(
      string $useragent = null,
      ?string $fbAppId = null,
      ?string $fbAppSecret = null
    ) {
        $this->useragent = is_null($useragent)
            ? self::USER_AGENT
            : $useragent;
        $this->fbAppId = $fbAppId;
        $this->fbAppSecret = $fbAppSecret;
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
          CURLOPT_FOLLOWLOCATION => false,
        ];

        $options[CURLOPT_URL] = $this->withAccessTokenForClosedOEmbed($url);
        $options[CURLOPT_HEADER] = true;
        $options[CURLOPT_RETURNTRANSFER] = 1;

        $handler = curl_init();
        curl_setopt_array($handler, $options);
        $response = curl_exec($handler);

        $status = curl_getinfo($handler, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($handler, CURLINFO_HEADER_SIZE);
        $redirectUrl = curl_getinfo($handler, CURLINFO_REDIRECT_URL);

        $body = substr($response, $headerSize);
        curl_close($handler);

        if ($status === 302 && $redirectUrl) {
            return $this->retrieveUrlData($redirectUrl);
        }
        if ($status !== 200) {
            return $this->handleErrorResponse($body, $url, $status);
        }

        return $body;
    }

    protected function withAccessTokenForClosedOEmbed(string $url): string
    {
        return $this->isClosedFacebookEmbed($url)
          ? $url . '&access_token=' . $this->fbAppId . '|' . $this->fbAppSecret
          : $url;
    }

    protected function isClosedFacebookEmbed(string $url): bool
    {
        return boolval(preg_match('#^https://graph\.facebook\.com/v[1-9]+\.[0-9]+/oembed_[a-z]+\?#', $url));
    }

    protected function handleErrorResponse(string $body, string $url, $status): string
    {
        $maybeFBopenGraphError = $this->maybeFacebookAccessTokenMissing($body);
        if ($maybeFBopenGraphError) {
            throw new \DomainException($maybeFBopenGraphError, $status);
        }
        throw new \RuntimeException($status . ': Invalid response for ' . $url, $status);
    }

    protected function maybeFacebookAccessTokenMissing(string $body): ?string
    {
        $response = json_decode($body, true);
        if (! isset($response['error'])) {
            return null;
        }
        $error = $response['error'];

        if (isset($error['type']) && $error['type'] === 'OAuthException') {
            return $error['message'];
        }
        error_log(var_export($response, true), 0);
        return null;
    }
}
