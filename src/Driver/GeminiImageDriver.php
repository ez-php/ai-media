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
 * Image generation driver for the Gemini Imagen predict API.
 *
 * Calls POST /v1beta/models/{model}:predict. Uses ez-php/http-client for all HTTP I/O.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class GeminiImageDriver implements ImageGeneratorInterface
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
     * @param ImageGenerationRequest $request
     *
     * @return ImageGenerationResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed response body.
     */
    public function generate(ImageGenerationRequest $request): ImageGenerationResponse
    {
        $model = $request->model() ?? $this->config->imageModel();
        $url = "{$this->config->baseUrl()}/v1beta/models/{$model}:predict?key={$this->config->apiKey()}";

        $body = [
            'instances' => [
                ['prompt' => $request->prompt()],
            ],
            'parameters' => [
                'sampleCount' => $request->count(),
            ],
        ];

        $httpResponse = $this->http
            ->post($url)
            ->withJson($body)
            ->send();

        if (!$httpResponse->ok()) {
            throw AiMediaRequestException::fromResponse($httpResponse->status(), $httpResponse->body());
        }

        /** @var mixed $decoded */
        $decoded = json_decode($httpResponse->body(), true);

        if (!is_array($decoded) || !is_array($decoded['predictions'] ?? null)) {
            throw AiMediaRequestException::malformedResponse('missing "predictions" array');
        }

        $images = [];

        foreach ($decoded['predictions'] as $prediction) {
            if (!is_array($prediction)) {
                continue;
            }

            $images[] = new GeneratedImage(
                base64Data: is_string($prediction['bytesBase64Encoded'] ?? null)
                    ? $prediction['bytesBase64Encoded']
                    : null,
            );
        }

        return new ImageGenerationResponse($images);
    }
}
