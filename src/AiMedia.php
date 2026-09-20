<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

use EzPhp\AiMedia\Driver\NullImageDriver;
use EzPhp\AiMedia\Driver\NullSpeechDriver;
use EzPhp\AiMedia\Driver\NullTranscriberDriver;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\Response\ImageGenerationResponse;
use EzPhp\AiMedia\Response\SpeechResponse;
use EzPhp\AiMedia\Response\TranscriptionResponse;

/**
 * Static façade for the active image, transcription, and speech drivers.
 *
 * The three drivers are wired independently by AiMediaServiceProvider on boot,
 * mirroring ez-php/ai's Ai façade split between AiClientInterface and
 * EmbeddingClientInterface — not every provider offers all three capabilities.
 * Without a service provider, the façade falls back to Null* drivers.
 *
 * Usage after AiMediaServiceProvider registration:
 *
 *   $images = AiMedia::image(ImageGenerationRequest::make('a red bicycle'));
 *   $text = AiMedia::transcribe(TranscriptionRequest::make($audioBytes))->text();
 *   $audio = AiMedia::speech(SpeechRequest::make('Hello there.'))->audio();
 *
 * In tests, wire any driver directly:
 *
 *   AiMedia::setImageGenerator(new NullImageDriver());
 *   // ... test code ...
 *   AiMedia::resetImageGenerator();
 *
 * @package EzPhp\AiMedia
 */
final class AiMedia
{
    private static ?ImageGeneratorInterface $imageGenerator = null;

    private static ?TranscriberInterface $transcriber = null;

    private static ?SpeechInterface $speech = null;

    // ─── Static client management ─────────────────────────────────────────────

    /**
     * @param ImageGeneratorInterface $imageGenerator
     *
     * @return void
     */
    public static function setImageGenerator(ImageGeneratorInterface $imageGenerator): void
    {
        self::$imageGenerator = $imageGenerator;
    }

    /**
     * @return ImageGeneratorInterface
     */
    public static function getImageGenerator(): ImageGeneratorInterface
    {
        if (self::$imageGenerator === null) {
            self::$imageGenerator = new NullImageDriver();
        }

        return self::$imageGenerator;
    }

    /**
     * Reset the static image generator (useful in tests).
     *
     * @return void
     */
    public static function resetImageGenerator(): void
    {
        self::$imageGenerator = null;
    }

    /**
     * @param TranscriberInterface $transcriber
     *
     * @return void
     */
    public static function setTranscriber(TranscriberInterface $transcriber): void
    {
        self::$transcriber = $transcriber;
    }

    /**
     * @return TranscriberInterface
     */
    public static function getTranscriber(): TranscriberInterface
    {
        if (self::$transcriber === null) {
            self::$transcriber = new NullTranscriberDriver();
        }

        return self::$transcriber;
    }

    /**
     * Reset the static transcriber (useful in tests).
     *
     * @return void
     */
    public static function resetTranscriber(): void
    {
        self::$transcriber = null;
    }

    /**
     * @param SpeechInterface $speech
     *
     * @return void
     */
    public static function setSpeech(SpeechInterface $speech): void
    {
        self::$speech = $speech;
    }

    /**
     * @return SpeechInterface
     */
    public static function getSpeech(): SpeechInterface
    {
        if (self::$speech === null) {
            self::$speech = new NullSpeechDriver();
        }

        return self::$speech;
    }

    /**
     * Reset the static speech driver (useful in tests).
     *
     * @return void
     */
    public static function resetSpeech(): void
    {
        self::$speech = null;
    }

    // ─── Static façade ────────────────────────────────────────────────────────

    /**
     * Generate one or more images using the active image driver.
     *
     * @param ImageGenerationRequest $request
     *
     * @return ImageGenerationResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed provider response.
     */
    public static function image(ImageGenerationRequest $request): ImageGenerationResponse
    {
        return self::getImageGenerator()->generate($request);
    }

    /**
     * Transcribe audio using the active transcription driver.
     *
     * @param TranscriptionRequest $request
     *
     * @return TranscriptionResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed provider response.
     */
    public static function transcribe(TranscriptionRequest $request): TranscriptionResponse
    {
        return self::getTranscriber()->transcribe($request);
    }

    /**
     * Synthesize speech audio using the active speech driver.
     *
     * @param SpeechRequest $request
     *
     * @return SpeechResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed provider response.
     */
    public static function speech(SpeechRequest $request): SpeechResponse
    {
        return self::getSpeech()->synthesize($request);
    }
}
