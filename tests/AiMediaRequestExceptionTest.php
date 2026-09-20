<?php

declare(strict_types=1);

namespace Tests\AiMedia;

use EzPhp\AiMedia\AiMediaException;
use EzPhp\AiMedia\AiMediaRequestException;

final class AiMediaRequestExceptionTest extends TestCase
{
    public function testExtendsAiMediaException(): void
    {
        $this->assertInstanceOf(AiMediaException::class, AiMediaRequestException::fromResponse(500, 'oops'));
    }

    public function testFromResponseIncludesStatusAndBody(): void
    {
        $exception = AiMediaRequestException::fromResponse(429, '{"error":"rate limited"}');

        $this->assertStringContainsString('429', $exception->getMessage());
        $this->assertStringContainsString('rate limited', $exception->getMessage());
    }

    public function testMalformedResponseIncludesReason(): void
    {
        $exception = AiMediaRequestException::malformedResponse('missing "text" field');

        $this->assertStringContainsString('missing "text" field', $exception->getMessage());
    }
}
