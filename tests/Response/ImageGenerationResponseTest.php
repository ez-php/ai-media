<?php

declare(strict_types=1);

namespace Tests\AiMedia\Response;

use EzPhp\AiMedia\Response\GeneratedImage;
use EzPhp\AiMedia\Response\ImageGenerationResponse;
use OutOfBoundsException;
use Tests\AiMedia\TestCase;

final class ImageGenerationResponseTest extends TestCase
{
    public function testImagesReturnsAllEntries(): void
    {
        $images = [new GeneratedImage(url: 'a'), new GeneratedImage(url: 'b')];
        $response = new ImageGenerationResponse($images);

        $this->assertSame($images, $response->images());
    }

    public function testFirstReturnsFirstImage(): void
    {
        $first = new GeneratedImage(url: 'a');
        $response = new ImageGenerationResponse([$first, new GeneratedImage(url: 'b')]);

        $this->assertSame($first, $response->first());
    }

    public function testFirstThrowsWhenEmpty(): void
    {
        $this->expectException(OutOfBoundsException::class);

        (new ImageGenerationResponse([]))->first();
    }
}
