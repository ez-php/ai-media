<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

/**
 * Configuration value object shared by the Gemini image, transcription, and speech drivers.
 *
 * @package EzPhp\AiMedia\Driver
 */
final readonly class GeminiConfig
{
    public const string DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com';
    public const string DEFAULT_IMAGE_MODEL = 'imagen-3.0-generate-002';
    public const string DEFAULT_TRANSCRIPTION_MODEL = 'gemini-2.0-flash';
    public const string DEFAULT_SPEECH_MODEL = 'gemini-2.5-flash-preview-tts';

    /**
     * @param string $apiKey             Gemini API key.
     * @param string $imageModel         Default model used for image generation.
     * @param string $transcriptionModel Default model used for transcription.
     * @param string $speechModel        Default model used for text-to-speech.
     * @param string $baseUrl            Base URL override.
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
