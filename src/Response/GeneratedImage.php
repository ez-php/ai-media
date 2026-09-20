<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Response;

/**
 * A single generated image: either a hosted URL or inline base64 data, never both.
 *
 * @package EzPhp\AiMedia\Response
 */
final readonly class GeneratedImage
{
    /**
     * @param string|null $url           Hosted URL of the image, when the provider returns one.
     * @param string|null $base64Data    Raw base64-encoded image bytes, when the provider returns inline data.
     * @param string|null $revisedPrompt Provider-revised prompt actually used for generation, if any.
     */
    public function __construct(
        private ?string $url = null,
        private ?string $base64Data = null,
        private ?string $revisedPrompt = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function url(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function base64Data(): ?string
    {
        return $this->base64Data;
    }

    /**
     * @return string|null
     */
    public function revisedPrompt(): ?string
    {
        return $this->revisedPrompt;
    }

    /**
     * @return bool
     */
    public function hasUrl(): bool
    {
        return $this->url !== null;
    }

    /**
     * Decode the inline base64 data into raw binary image bytes.
     *
     * @return string
     */
    public function binary(): string
    {
        return (string) base64_decode($this->base64Data ?? '', true);
    }
}
