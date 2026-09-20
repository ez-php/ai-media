<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\Response\TranscriptionResponse;
use EzPhp\AiMedia\TranscriberInterface;
use EzPhp\HttpClient\HttpClient;

/**
 * Transcription driver using Gemini's generateContent API with inline audio data.
 *
 * Calls POST /v1beta/models/{model}:generateContent. Uses ez-php/http-client for all HTTP I/O.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class GeminiTranscriberDriver implements TranscriberInterface
{
    private const string PROMPT = 'Transcribe this audio verbatim. Return only the transcript text.';

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
     * @param TranscriptionRequest $request
     *
     * @return TranscriptionResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed response body.
     */
    public function transcribe(TranscriptionRequest $request): TranscriptionResponse
    {
        $model = $request->model() ?? $this->config->transcriptionModel();
        $url = "{$this->config->baseUrl()}/v1beta/models/{$model}:generateContent?key={$this->config->apiKey()}";

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => self::PROMPT],
                        [
                            'inline_data' => [
                                'mime_type' => $request->mimeType(),
                                'data' => base64_encode($request->audio()),
                            ],
                        ],
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

        $text = $this->extractText($httpResponse->body());

        return new TranscriptionResponse(text: $text, language: $request->language());
    }

    /**
     * @param string $rawBody
     *
     * @return string
     *
     * @throws AiMediaRequestException When the candidate/part structure is missing.
     */
    private function extractText(string $rawBody): string
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

        if (!is_array($part) || !is_string($part['text'] ?? null)) {
            throw AiMediaRequestException::malformedResponse('missing text part');
        }

        return $part['text'];
    }
}
