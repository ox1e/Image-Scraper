<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;

class ImageScraperService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function scrapeImages(string $url): array
    {
        try {
            $response = $this->client->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('URL недоступен. Статус-код: ' . $response->getStatusCode());
            }

            $htmlContent = $response->getContent();
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            throw new \Exception('Не удалось получить содержимое URL: ' . $e->getMessage());
        }

        $crawler = new Crawler($htmlContent);
        $images = $crawler->filter('img')->each(function ($node) use ($url) {
            $src = $node->attr('src');
            if (filter_var($src, FILTER_VALIDATE_URL) === false) {
                $parsedUrl = parse_url($url);
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                $src = $baseUrl . '/' . ltrim($src, '/');
            }
            return $src;
        });

        $imageData = [];
        $totalSize = 0;

        foreach ($images as $image) {
            $headers = @get_headers($image, 1);
            if ($headers && isset($headers['Content-Length'])) {
                $size = $headers['Content-Length'];
                $totalSize += $size;
                $imageData[] = ['url' => $image, 'size' => $size];
            } else {
                $imageData[] = ['url' => $image, 'size' => 0];
            }
        }

        return [
            'images' => $imageData,
            'totalImages' => count($imageData),
            'totalSize' => $totalSize / 1048576 // размер в МБ
        ];
    }
}
