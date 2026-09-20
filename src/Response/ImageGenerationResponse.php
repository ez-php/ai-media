<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Response;

use OutOfBoundsException;

/**
 * Immutable value object wrapping the images returned by an image generation request.
 *
 * @package EzPhp\AiMedia\Response
 */
final readonly class ImageGenerationResponse
{
    /**
     * @param list<GeneratedImage> $images
     */
    public function __construct(private array $images)
    {
    }

    /**
     * @return list<GeneratedImage>
     */
    public function images(): array
    {
        return $this->images;
    }

    /**
     * Convenience accessor for the first (or only) generated image.
     *
     * @return GeneratedImage
     */
    public function first(): GeneratedImage
    {
        if ($this->images === []) {
            throw new OutOfBoundsException('No images were generated.');
        }

        return $this->images[0];
    }
}
