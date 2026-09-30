<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

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
 * Without a service provider every call throws a RuntimeException naming
 * AiMediaServiceProvider (a silent Null* fallback returned empty results).
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
     * @throws \RuntimeException When nothing has been set (AiMediaServiceProvider not registered).
     *
     * @return ImageGeneratorInterface
     */
    public static function getImageGenerator(): ImageGeneratorInterface
    {
        if (self::$imageGenerator === null) {
            throw new \RuntimeException('AiMedia image generator not set. Did you register AiMediaServiceProvider?');
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
     * @throws \RuntimeException When nothing has been set (AiMediaServiceProvider not registered).
     *
     * @return TranscriberInterface
     */
    public static function getTranscriber(): TranscriberInterface
    {
        if (self::$transcriber === null) {
            throw new \RuntimeException('AiMedia transcriber not set. Did you register AiMediaServiceProvider?');
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
     * @throws \RuntimeException When nothing has been set (AiMediaServiceProvider not registered).
     *
     * @return SpeechInterface
     */
    public static function getSpeech(): SpeechInterface
    {
        if (self::$speech === null) {
            throw new \RuntimeException('AiMedia speech driver not set. Did you register AiMediaServiceProvider?');
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
