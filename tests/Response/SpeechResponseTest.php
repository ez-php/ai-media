<?php

declare(strict_types=1);

namespace Tests\AiMedia\Response;

use EzPhp\AiMedia\Response\SpeechResponse;
use Tests\AiMedia\TestCase;

final class SpeechResponseTest extends TestCase
{
    public function testAccessors(): void
    {
        $response = new SpeechResponse('bytes', 'audio/wav');

        $this->assertSame('bytes', $response->audio());
        $this->assertSame('audio/wav', $response->mimeType());
    }

    public function testDefaultMimeTypeIsMpeg(): void
    {
        $response = new SpeechResponse('bytes');

        $this->assertSame('audio/mpeg', $response->mimeType());
    }
}
