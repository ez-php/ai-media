<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Response\SpeechResponse;
use EzPhp\AiMedia\SpeechInterface;
use EzPhp\HttpClient\HttpClient;

/**
 * Text-to-speech driver for the OpenAI audio speech API.
 *
 * Calls POST /v1/audio/speech; the response body is raw audio bytes, not JSON.
 * Uses ez-php/http-client for all HTTP I/O.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class OpenAiSpeechDriver implements SpeechInterface
{
    /**
     * Maps a requested container format to the MIME type reported on SpeechResponse.
     *
     * @var array<string, string>
     */
    private const array MIME_TYPES = [
        'mp3' => 'audio/mpeg',
        'opus' => 'audio/opus',
        'aac' => 'audio/aac',
        'flac' => 'audio/flac',
        'wav' => 'audio/wav',
        'pcm' => 'audio/pcm',
    ];

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
     * @param SpeechRequest $request
     *
     * @return SpeechResponse
     *
     * @throws AiMediaRequestException On HTTP error.
     */
    public function synthesize(SpeechRequest $request): SpeechResponse
    {
        $body = [
            'model' => $request->model() ?? $this->config->speechModel(),
            'input' => $request->text(),
            'voice' => $request->voice(),
            'response_format' => $request->format(),
        ];

        $httpResponse = $this->http
            ->post($this->config->baseUrl() . '/v1/audio/speech')
            ->withHeader('Authorization', 'Bearer ' . $this->config->apiKey())
            ->withJson($body)
            ->send();

        if (!$httpResponse->ok()) {
            throw AiMediaRequestException::fromResponse($httpResponse->status(), $httpResponse->body());
        }

        return new SpeechResponse(
            audio: $httpResponse->body(),
            mimeType: self::MIME_TYPES[$request->format()] ?? 'application/octet-stream',
        );
    }
}
