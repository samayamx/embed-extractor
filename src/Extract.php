<?php

namespace Samaya\Embed;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Extract
{
    /** @var Crawler[] */
    protected $cachedCrawlers = [];

    /** @var Client[] */
    protected $cachedClients = [];

    public function getOGImage(string $url): ?string
    {
        if (!filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('Invalid url %s', $url));
        }
        $crawler = $this->getCrawler($url);
        $node = $crawler->filterXPath("//meta[@property='og:image']");
        $content = $node->extract(['content']);

        return $content[0];
    }

    protected function getClient(string $url): Client
    {
        if (isset($this->cachedClients[$url])) {
            return $this->cachedClients[$url];
        }
        $client = new Client([
          'base_uri' => $url
        ]);

        $this->cachedClients[$url] = $client;
        return $this->cachedClients[$url];
    }

    protected function getCrawler(string $url): Crawler
    {
        if (isset($this->cachedCrawlers[$url])) {
            return $this->cachedCrawlers[$url];
        }
        $client = $this->getClient($url);
        $response = $client->get($url);

        $crawler = new Crawler($response->getBody()->getContents());

        $this->cachedCrawlers[$url] = $crawler;
        return $this->cachedCrawlers[$url];
    }
}
