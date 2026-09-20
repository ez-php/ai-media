<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Request;

/**
 * Immutable value object describing an image generation request.
 *
 * @package EzPhp\AiMedia\Request
 */
final readonly class ImageGenerationRequest
{
    /**
     * @param string      $prompt Text prompt describing the desired image.
     * @param int         $count  Number of images to generate.
     * @param string      $size   Provider-specific size string, e.g. "1024x1024".
     * @param string|null $model  Model override; null uses the driver's default.
     */
    public function __construct(
        private string $prompt,
        private int $count = 1,
        private string $size = '1024x1024',
        private ?string $model = null,
    ) {
    }

    /**
     * @param string $prompt
     *
     * @return self
     */
    public static function make(string $prompt): self
    {
        return new self($prompt);
    }

    /**
     * @return string
     */
    public function prompt(): string
    {
        return $this->prompt;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return string
     */
    public function size(): string
    {
        return $this->size;
    }

    /**
     * @return string|null
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * @param int $count
     *
     * @return self
     */
    public function withCount(int $count): self
    {
        return new self($this->prompt, $count, $this->size, $this->model);
    }

    /**
     * @param string $size
     *
     * @return self
     */
    public function withSize(string $size): self
    {
        return new self($this->prompt, $this->count, $size, $this->model);
    }

    /**
     * @param string $model
     *
     * @return self
     */
    public function withModel(string $model): self
    {
        return new self($this->prompt, $this->count, $this->size, $model);
    }
}
