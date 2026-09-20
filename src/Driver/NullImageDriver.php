<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\ImageGeneratorInterface;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\AiMedia\Response\ImageGenerationResponse;

/**
 * Image generation driver that always returns an empty result. Default when no driver is configured.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class NullImageDriver implements ImageGeneratorInterface
{
    /**
     * @param ImageGenerationRequest $request
     *
     * @return ImageGenerationResponse
     */
    public function generate(ImageGenerationRequest $request): ImageGenerationResponse
    {
        return new ImageGenerationResponse([]);
    }
}
