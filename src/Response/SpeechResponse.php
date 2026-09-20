<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Response;

/**
 * Immutable value object wrapping synthesized speech audio.
 *
 * @package EzPhp\AiMedia\Response
 */
final readonly class SpeechResponse
{
    /**
     * @param string $audio    Raw audio bytes.
     * @param string $mimeType MIME type of the audio bytes, e.g. "audio/mpeg".
     */
    public function __construct(
        private string $audio,
        private string $mimeType = 'audio/mpeg',
    ) {
    }

    /**
     * @return string
     */
    public function audio(): string
    {
        return $this->audio;
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        return $this->mimeType;
    }
}
