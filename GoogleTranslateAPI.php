<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GoogleTranslateAPI
{
    private const HEADERS = [
        'Accept-Encoding' => 'application/gzip',
        'X-RapidAPI-Host' => 'google-translate1.p.rapidapi.com',
        'X-RapidAPI-Key' => '61e999c92fmshfa48706565c0895p121b55jsn2bf80e29edcd',
        'Content-Type' => 'application/x-www-form-urlencoded',
    ];

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://google-translate1.p.rapidapi.com']);
    }

    /**
     * @throws LanguageDetectionFailedException
     * @throws RequestFailedException
     */
    public function detectLanguage(string $query): string
    {
        $data = $this->request('/language/translate/v2/detect', ['q' => $query]);

        if (isset($data['data']['detections'][0][0]['language'])) {
            return $data['data']['detections'][0][0]['language'];
        }

        throw new LanguageDetectionFailedException("Language detection failed for '$query'");
    }

    /**
     * @throws RequestFailedException
     * @throws TranslationFailedException
     */
    public function translate(string $query, string $sourceLang): string
    {
        $data = $this->request('/language/translate/v2', [
            'q' => $query,
            'target' => 'en',
            'source' => $sourceLang,
        ]);

        if (isset($data['data']['translations'][0]['translatedText'])) {
            return $data['data']['translations'][0]['translatedText'];
        }

        throw new TranslationFailedException("Translation failed for '$query'");
    }

    /**
     * @throws RequestFailedException
     */
    private function request(string $endpoint, array $params): array
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'headers' => self::HEADERS,
                'form_params' => $params,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new RequestFailedException("Google request for $endpoint failed: " . $e->getMessage());
        }
    }
}
