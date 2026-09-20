<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Response;

/**
 * Immutable value object wrapping a transcription result.
 *
 * @package EzPhp\AiMedia\Response
 */
final readonly class TranscriptionResponse
{
    /**
     * @param string      $text     Transcribed text.
     * @param string|null $language Detected or provided language, if returned by the provider.
     * @param float|null  $duration Audio duration in seconds, if returned by the provider.
     */
    public function __construct(
        private string $text,
        private ?string $language = null,
        private ?float $duration = null,
    ) {
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return string|null
     */
    public function language(): ?string
    {
        return $this->language;
    }

    /**
     * @return float|null
     */
    public function duration(): ?float
    {
        return $this->duration;
    }
}
