<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\AiMedia\Response\ImageGenerationResponse;

/**
 * Contract for an image generation driver.
 *
 * @package EzPhp\AiMedia
 */
interface ImageGeneratorInterface
{
    /**
     * Generate one or more images from a text prompt.
     *
     * @param ImageGenerationRequest $request
     *
     * @return ImageGenerationResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed provider response.
     */
    public function generate(ImageGenerationRequest $request): ImageGenerationResponse;
}
