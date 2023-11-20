<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GiphyAPI
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->client = new Client();
        $this->apiKey = $apiKey;
    }

    /**
     * @throws NoGifFoundException
     * @throws RequestFailedException
     */
    public function getRandomGifUrl(string $query): string
    {
        try {
            $response = $this->client->request('GET', "https://api.giphy.com/v1/gifs/search", [
                'query' => [
                    'api_key' => $this->apiKey,
                    'q' => $query,
                    'limit' => 10,
                    'rating' => 'g',
                ]
            ]);
        } catch (GuzzleException $e) {
            throw new RequestFailedException("Giphy request failed: " . $e->getMessage());
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        if (empty($data['data'])) {
            throw new NoGifFoundException("No GIFs found for '$query'.");
        }

        $gifCount = count($data['data']);
        $startIndex = array_rand($data['data']);
        $currentIndex = $startIndex;

        echo "Found $gifCount GIFs for '$query'.\n";
        do {
            if (isset($data['data'][$currentIndex]['images']['original']['url'])) {
                echo "Found GIF at index $currentIndex.\n";
                return $data['data'][$currentIndex]['images']['original']['url'];
            }

            $currentIndex = ($currentIndex + 1) % $gifCount;
        } while ($currentIndex != $startIndex);

        throw new NoGifFoundException("All GIFs found lack a URL for '$query'.");
    }
}
