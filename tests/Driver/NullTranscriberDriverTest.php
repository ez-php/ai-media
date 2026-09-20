<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\Driver\NullTranscriberDriver;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\TranscriberInterface;
use Tests\AiMedia\TestCase;

final class NullTranscriberDriverTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(TranscriberInterface::class, new NullTranscriberDriver());
    }

    public function testTranscribeReturnsEmptyText(): void
    {
        $response = (new NullTranscriberDriver())->transcribe(TranscriptionRequest::make('bytes'));

        $this->assertSame('', $response->text());
    }
}
