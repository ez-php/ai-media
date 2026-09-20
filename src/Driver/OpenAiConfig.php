<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

/**
 * Configuration value object shared by the OpenAI image, transcription, and speech drivers.
 *
 * @package EzPhp\AiMedia\Driver
 */
final readonly class OpenAiConfig
{
    public const string DEFAULT_BASE_URL = 'https://api.openai.com';
    public const string DEFAULT_IMAGE_MODEL = 'dall-e-3';
    public const string DEFAULT_TRANSCRIPTION_MODEL = 'whisper-1';
    public const string DEFAULT_SPEECH_MODEL = 'tts-1';

    /**
     * @param string $apiKey            OpenAI API key.
     * @param string $imageModel        Default model used for image generation.
     * @param string $transcriptionModel Default model used for transcription.
     * @param string $speechModel       Default model used for text-to-speech.
     * @param string $baseUrl           Base URL override (useful for Azure OpenAI or API proxies).
     */
    public function __construct(
        private string $apiKey,
        private string $imageModel = self::DEFAULT_IMAGE_MODEL,
        private string $transcriptionModel = self::DEFAULT_TRANSCRIPTION_MODEL,
        private string $speechModel = self::DEFAULT_SPEECH_MODEL,
        private string $baseUrl = self::DEFAULT_BASE_URL,
    ) {
    }

    /**
     * @return string
     */
    public function apiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function imageModel(): string
    {
        return $this->imageModel;
    }

    /**
     * @return string
     */
    public function transcriptionModel(): string
    {
        return $this->transcriptionModel;
    }

    /**
     * @return string
     */
    public function speechModel(): string
    {
        return $this->speechModel;
    }

    /**
     * @return string
     */
    public function baseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }
}
