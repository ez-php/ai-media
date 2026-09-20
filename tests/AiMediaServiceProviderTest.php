<?php

declare(strict_types=1);

namespace Tests\AiMedia;

use EzPhp\AiMedia\AiMedia;
use EzPhp\AiMedia\AiMediaServiceProvider;
use EzPhp\AiMedia\Driver\GeminiImageDriver;
use EzPhp\AiMedia\Driver\GeminiSpeechDriver;
use EzPhp\AiMedia\Driver\GeminiTranscriberDriver;
use EzPhp\AiMedia\Driver\NullImageDriver;
use EzPhp\AiMedia\Driver\NullSpeechDriver;
use EzPhp\AiMedia\Driver\NullTranscriberDriver;
use EzPhp\AiMedia\Driver\OpenAiImageDriver;
use EzPhp\AiMedia\Driver\OpenAiSpeechDriver;
use EzPhp\AiMedia\Driver\OpenAiTranscriberDriver;
use Tests\AiMedia\Support\FakeConfig;
use Tests\AiMedia\Support\FakeContainer;

final class AiMediaServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        AiMedia::resetImageGenerator();
        AiMedia::resetTranscriber();
        AiMedia::resetSpeech();
    }

    /** @param array<string, mixed> $configData */
    private function makeProvider(array $configData = []): AiMediaServiceProvider
    {
        return new AiMediaServiceProvider(new FakeContainer(new FakeConfig($configData)));
    }

    /** @param array<string, mixed> $configData */
    private function boot(array $configData): void
    {
        $provider = $this->makeProvider($configData);
        $provider->register();
        $provider->boot();
    }

    public function testDefaultsToNullDrivers(): void
    {
        $this->boot([]);

        $this->assertInstanceOf(NullImageDriver::class, AiMedia::getImageGenerator());
        $this->assertInstanceOf(NullTranscriberDriver::class, AiMedia::getTranscriber());
        $this->assertInstanceOf(NullSpeechDriver::class, AiMedia::getSpeech());
    }

    public function testSelectsOpenAiDrivers(): void
    {
        $this->boot([
            'ai_media.image_driver' => 'openai',
            'ai_media.transcription_driver' => 'openai',
            'ai_media.speech_driver' => 'openai',
        ]);

        $this->assertInstanceOf(OpenAiImageDriver::class, AiMedia::getImageGenerator());
        $this->assertInstanceOf(OpenAiTranscriberDriver::class, AiMedia::getTranscriber());
        $this->assertInstanceOf(OpenAiSpeechDriver::class, AiMedia::getSpeech());
    }

    public function testSelectsGeminiDrivers(): void
    {
        $this->boot([
            'ai_media.image_driver' => 'gemini',
            'ai_media.transcription_driver' => 'gemini',
            'ai_media.speech_driver' => 'gemini',
        ]);

        $this->assertInstanceOf(GeminiImageDriver::class, AiMedia::getImageGenerator());
        $this->assertInstanceOf(GeminiTranscriberDriver::class, AiMedia::getTranscriber());
        $this->assertInstanceOf(GeminiSpeechDriver::class, AiMedia::getSpeech());
    }

    public function testCapabilitiesAreSelectedIndependently(): void
    {
        $this->boot([
            'ai_media.image_driver' => 'openai',
            'ai_media.transcription_driver' => 'gemini',
        ]);

        $this->assertInstanceOf(OpenAiImageDriver::class, AiMedia::getImageGenerator());
        $this->assertInstanceOf(GeminiTranscriberDriver::class, AiMedia::getTranscriber());
        $this->assertInstanceOf(NullSpeechDriver::class, AiMedia::getSpeech());
    }

    public function testUnknownDriverNameFallsBackToNull(): void
    {
        $this->boot(['ai_media.image_driver' => 'unknown']);

        $this->assertInstanceOf(NullImageDriver::class, AiMedia::getImageGenerator());
    }
}
