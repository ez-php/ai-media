<?php

declare(strict_types=1);

namespace Tests\AiMedia;

use EzPhp\AiMedia\AiMedia;
use EzPhp\AiMedia\Driver\NullImageDriver;
use EzPhp\AiMedia\Driver\NullSpeechDriver;
use EzPhp\AiMedia\Driver\NullTranscriberDriver;
use EzPhp\AiMedia\ImageGeneratorInterface;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\Response\ImageGenerationResponse;
use EzPhp\AiMedia\Response\SpeechResponse;
use EzPhp\AiMedia\Response\TranscriptionResponse;
use EzPhp\AiMedia\SpeechInterface;
use EzPhp\AiMedia\TranscriberInterface;

final class AiMediaTest extends TestCase
{
    protected function tearDown(): void
    {
        AiMedia::resetImageGenerator();
        AiMedia::resetTranscriber();
        AiMedia::resetSpeech();
    }

    public function testLazilyDefaultsToNullDrivers(): void
    {
        $this->assertInstanceOf(NullImageDriver::class, AiMedia::getImageGenerator());
        $this->assertInstanceOf(NullTranscriberDriver::class, AiMedia::getTranscriber());
        $this->assertInstanceOf(NullSpeechDriver::class, AiMedia::getSpeech());
    }

    public function testImageDelegatesToImageGenerator(): void
    {
        $response = new ImageGenerationResponse([]);
        $driver = new class ($response) implements ImageGeneratorInterface {
            public function __construct(private readonly ImageGenerationResponse $response)
            {
            }

            public function generate(ImageGenerationRequest $request): ImageGenerationResponse
            {
                return $this->response;
            }
        };

        AiMedia::setImageGenerator($driver);

        $this->assertSame($response, AiMedia::image(ImageGenerationRequest::make('a cat')));
    }

    public function testTranscribeDelegatesToTranscriber(): void
    {
        $response = new TranscriptionResponse('hello world');
        $driver = new class ($response) implements TranscriberInterface {
            public function __construct(private readonly TranscriptionResponse $response)
            {
            }

            public function transcribe(TranscriptionRequest $request): TranscriptionResponse
            {
                return $this->response;
            }
        };

        AiMedia::setTranscriber($driver);

        $this->assertSame($response, AiMedia::transcribe(TranscriptionRequest::make('bytes')));
    }

    public function testSpeechDelegatesToSpeechDriver(): void
    {
        $response = new SpeechResponse('bytes');
        $driver = new class ($response) implements SpeechInterface {
            public function __construct(private readonly SpeechResponse $response)
            {
            }

            public function synthesize(SpeechRequest $request): SpeechResponse
            {
                return $this->response;
            }
        };

        AiMedia::setSpeech($driver);

        $this->assertSame($response, AiMedia::speech(SpeechRequest::make('hi')));
    }
}
