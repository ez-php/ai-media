<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\ImageGeneratorInterface;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\AiMedia\Response\GeneratedImage;
use EzPhp\AiMedia\Response\ImageGenerationResponse;
use EzPhp\HttpClient\HttpClient;

/**
 * Image generation driver for the OpenAI images API (DALL-E).
 *
 * Calls POST /v1/images/generations. Uses ez-php/http-client for all HTTP I/O.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class OpenAiImageDriver implements ImageGeneratorInterface
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
     * @param ImageGenerationRequest $request
     *
     * @return ImageGenerationResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed response body.
     */
    public function generate(ImageGenerationRequest $request): ImageGenerationResponse
    {
        $body = [
            'model' => $request->model() ?? $this->config->imageModel(),
            'prompt' => $request->prompt(),
            'n' => $request->count(),
            'size' => $request->size(),
        ];

        $httpResponse = $this->http
            ->post($this->config->baseUrl() . '/v1/images/generations')
            ->withHeader('Authorization', 'Bearer ' . $this->config->apiKey())
            ->withJson($body)
            ->send();

        if (!$httpResponse->ok()) {
            throw AiMediaRequestException::fromResponse($httpResponse->status(), $httpResponse->body());
        }

        /** @var mixed $decoded */
        $decoded = json_decode($httpResponse->body(), true);

        if (!is_array($decoded) || !is_array($decoded['data'] ?? null)) {
            throw AiMediaRequestException::malformedResponse('missing "data" array');
        }

        $images = [];

        foreach ($decoded['data'] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $images[] = new GeneratedImage(
                url: is_string($item['url'] ?? null) ? $item['url'] : null,
                base64Data: is_string($item['b64_json'] ?? null) ? $item['b64_json'] : null,
                revisedPrompt: is_string($item['revised_prompt'] ?? null) ? $item['revised_prompt'] : null,
            );
        }

        return new ImageGenerationResponse($images);
    }
}
