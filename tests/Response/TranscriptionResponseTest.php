<?php

declare(strict_types=1);

namespace Tests\AiMedia\Response;

use EzPhp\AiMedia\Response\TranscriptionResponse;
use Tests\AiMedia\TestCase;

final class TranscriptionResponseTest extends TestCase
{
    public function testAccessors(): void
    {
        $response = new TranscriptionResponse('hello world', 'en', 3.5);

        $this->assertSame('hello world', $response->text());
        $this->assertSame('en', $response->language());
        $this->assertSame(3.5, $response->duration());
    }

    public function testOptionalFieldsDefaultToNull(): void
    {
        $response = new TranscriptionResponse('hello world');

        $this->assertNull($response->language());
        $this->assertNull($response->duration());
    }
}
