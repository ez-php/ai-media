<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\Driver\NullSpeechDriver;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\SpeechInterface;
use Tests\AiMedia\TestCase;

final class NullSpeechDriverTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(SpeechInterface::class, new NullSpeechDriver());
    }

    public function testSynthesizeReturnsEmptyAudio(): void
    {
        $response = (new NullSpeechDriver())->synthesize(SpeechRequest::make('hi'));

        $this->assertSame('', $response->audio());
    }
}
