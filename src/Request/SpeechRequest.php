<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Request;

/**
 * Immutable value object describing a text-to-speech request.
 *
 * @package EzPhp\AiMedia\Request
 */
final readonly class SpeechRequest
{
    /**
     * @param string      $text   Text to synthesize.
     * @param string      $voice  Provider-specific voice identifier.
     * @param string      $format Requested audio container/codec, e.g. "mp3".
     * @param string|null $model  Model override; null uses the driver's default.
     */
    public function __construct(
        private string $text,
        private string $voice = 'alloy',
        private string $format = 'mp3',
        private ?string $model = null,
    ) {
    }

    /**
     * @param string $text
     *
     * @return self
     */
    public static function make(string $text): self
    {
        return new self($text);
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function voice(): string
    {
        return $this->voice;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->format;
    }

    /**
     * @return string|null
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * @param string $voice
     *
     * @return self
     */
    public function withVoice(string $voice): self
    {
        return new self($this->text, $voice, $this->format, $this->model);
    }

    /**
     * @param string $format
     *
     * @return self
     */
    public function withFormat(string $format): self
    {
        return new self($this->text, $this->voice, $format, $this->model);
    }

    /**
     * @param string $model
     *
     * @return self
     */
    public function withModel(string $model): self
    {
        return new self($this->text, $this->voice, $this->format, $model);
    }
}
