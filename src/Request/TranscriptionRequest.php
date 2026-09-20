<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Request;

/**
 * Immutable value object describing an audio transcription request.
 *
 * @package EzPhp\AiMedia\Request
 */
final readonly class TranscriptionRequest
{
    /**
     * @param string      $audio    Raw audio file contents.
     * @param string      $filename Filename sent to the provider (drives format detection).
     * @param string      $mimeType MIME type of the audio contents.
     * @param string|null $language Optional ISO-639-1 language hint.
     * @param string|null $model    Model override; null uses the driver's default.
     */
    public function __construct(
        private string $audio,
        private string $filename = 'audio.mp3',
        private string $mimeType = 'audio/mpeg',
        private ?string $language = null,
        private ?string $model = null,
    ) {
    }

    /**
     * @param string $audio
     * @param string $filename
     * @param string $mimeType
     *
     * @return self
     */
    public static function make(string $audio, string $filename = 'audio.mp3', string $mimeType = 'audio/mpeg'): self
    {
        return new self($audio, $filename, $mimeType);
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
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return string|null
     */
    public function language(): ?string
    {
        return $this->language;
    }

    /**
     * @return string|null
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * @param string $language
     *
     * @return self
     */
    public function withLanguage(string $language): self
    {
        return new self($this->audio, $this->filename, $this->mimeType, $language, $this->model);
    }

    /**
     * @param string $model
     *
     * @return self
     */
    public function withModel(string $model): self
    {
        return new self($this->audio, $this->filename, $this->mimeType, $this->language, $model);
    }
}
