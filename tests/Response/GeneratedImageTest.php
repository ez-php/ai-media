<?php

declare(strict_types=1);

namespace Tests\AiMedia\Response;

use EzPhp\AiMedia\Response\GeneratedImage;
use Tests\AiMedia\TestCase;

final class GeneratedImageTest extends TestCase
{
    public function testUrlBasedImage(): void
    {
        $image = new GeneratedImage(url: 'https://example.com/image.png', revisedPrompt: 'a cat');

        $this->assertTrue($image->hasUrl());
        $this->assertSame('https://example.com/image.png', $image->url());
        $this->assertSame('a cat', $image->revisedPrompt());
        $this->assertNull($image->base64Data());
    }

    public function testBase64BasedImageDecodesToBinary(): void
    {
        $binary = 'raw-image-bytes';
        $image = new GeneratedImage(base64Data: base64_encode($binary));

        $this->assertFalse($image->hasUrl());
        $this->assertSame($binary, $image->binary());
    }

    public function testBinaryReturnsEmptyStringWithoutBase64Data(): void
    {
        $image = new GeneratedImage(url: 'https://example.com/image.png');

        $this->assertSame('', $image->binary());
    }
}
