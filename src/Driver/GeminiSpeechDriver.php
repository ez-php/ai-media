<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Response\SpeechResponse;
use EzPhp\AiMedia\SpeechInterface;
use EzPhp\HttpClient\HttpClient;

/**
 * Text-to-speech driver using Gemini's generateContent API with an AUDIO response modality.
 *
 * Calls POST /v1beta/models/{model}:generateContent. Uses ez-php/http-client for all HTTP I/O.
 * Gemini returns raw 24kHz 16-bit PCM audio, reported as "audio/pcm".
 *
 * @package EzPhp\AiMedia\Driver
 */
final class GeminiSpeechDriver implements SpeechInterface
{
    /**
     * @param HttpClient   $http   Injected HTTP client; use FakeTransport in tests.
     * @param GeminiConfig $config Driver configuration.
     */
    public function __construct(
        private readonly HttpClient $http,
        private readonly GeminiConfig $config,
    ) {
    }

    /**
     * @param SpeechRequest $request
     *
     * @return SpeechResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed response body.
     */
    public function synthesize(SpeechRequest $request): SpeechResponse
    {
        $model = $request->model() ?? $this->config->speechModel();
        $url = "{$this->config->baseUrl()}/v1beta/models/{$model}:generateContent?key={$this->config->apiKey()}";

        $body = [
            'contents' => [
                ['parts' => [['text' => $request->text()]]],
            ],
            'generationConfig' => [
                'responseModalities' => ['AUDIO'],
                'speechConfig' => [
                    'voiceConfig' => [
                        'prebuiltVoiceConfig' => ['voiceName' => $request->voice()],
                    ],
                ],
            ],
        ];

        $httpResponse = $this->http
            ->post($url)
            ->withJson($body)
            ->send();

        if (!$httpResponse->ok()) {
            throw AiMediaRequestException::fromResponse($httpResponse->status(), $httpResponse->body());
        }

        return $this->extractAudio($httpResponse->body());
    }

    /**
     * @param string $rawBody
     *
     * @return SpeechResponse
     *
     * @throws AiMediaRequestException When the candidate/part structure is missing.
     */
    private function extractAudio(string $rawBody): SpeechResponse
    {
        /** @var mixed $decoded */
        $decoded = json_decode($rawBody, true);

        if (!is_array($decoded) || !is_array($decoded['candidates'] ?? null)) {
            throw AiMediaRequestException::malformedResponse('missing "candidates" array');
        }

        $candidate = $decoded['candidates'][0] ?? null;
        $contentBlock = is_array($candidate) ? ($candidate['content'] ?? null) : null;
        $parts = is_array($contentBlock) ? ($contentBlock['parts'] ?? null) : null;

        if (!is_array($parts)) {
            throw AiMediaRequestException::malformedResponse('missing candidate content parts');
        }

        $part = $parts[0] ?? null;
        $inlineData = is_array($part) ? ($part['inlineData'] ?? null) : null;

        if (!is_array($inlineData) || !is_string($inlineData['data'] ?? null)) {
            throw AiMediaRequestException::malformedResponse('missing inline audio data');
        }

        $mimeType = is_string($inlineData['mimeType'] ?? null) ? $inlineData['mimeType'] : 'audio/pcm';

        return new SpeechResponse(audio: (string) base64_decode($inlineData['data'], true), mimeType: $mimeType);
    }
}
