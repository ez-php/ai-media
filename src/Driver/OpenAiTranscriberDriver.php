<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\Response\TranscriptionResponse;
use EzPhp\AiMedia\TranscriberInterface;
use EzPhp\HttpClient\HttpClient;

/**
 * Transcription driver for the OpenAI audio transcriptions API (Whisper).
 *
 * Calls POST /v1/audio/transcriptions as multipart/form-data.
 * Uses ez-php/http-client for all HTTP I/O.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class OpenAiTranscriberDriver implements TranscriberInterface
{
    /**
     * @param HttpClient   $http   Injected HTTP client; use FakeTransport in tests.
     * @param OpenAiConfig $config Driver configuration.
     */
    public function __construct(
        private readonly HttpClient $http,
        private readonly OpenAiConfig $config,
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
        $httpRequest = $this->http
            ->post($this->config->baseUrl() . '/v1/audio/transcriptions')
            ->withHeader('Authorization', 'Bearer ' . $this->config->apiKey())
            ->attach('file', $request->audio(), $request->filename(), $request->mimeType())
            ->attach('model', $request->model() ?? $this->config->transcriptionModel())
            ->attach('response_format', 'verbose_json');

        if ($request->language() !== null) {
            $httpRequest = $httpRequest->attach('language', $request->language());
        }

        $httpResponse = $httpRequest->send();

        if (!$httpResponse->ok()) {
            throw AiMediaRequestException::fromResponse($httpResponse->status(), $httpResponse->body());
        }

        /** @var mixed $decoded */
        $decoded = json_decode($httpResponse->body(), true);

        if (!is_array($decoded) || !is_string($decoded['text'] ?? null)) {
            throw AiMediaRequestException::malformedResponse('missing "text" field');
        }

        return new TranscriptionResponse(
            text: $decoded['text'],
            language: is_string($decoded['language'] ?? null) ? $decoded['language'] : null,
            duration: is_numeric($decoded['duration'] ?? null) ? (float) $decoded['duration'] : null,
        );
    }
}
