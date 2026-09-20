<?php

declare(strict_types=1);

namespace Tests\AiMedia;

use EzPhp\AiMedia\AiMediaException;
use RuntimeException;

final class AiMediaExceptionTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new AiMediaException('boom');

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertSame('boom', $exception->getMessage());
    }
}
