<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\Driver\NullImageDriver;
use EzPhp\AiMedia\ImageGeneratorInterface;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use Tests\AiMedia\TestCase;

final class NullImageDriverTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(ImageGeneratorInterface::class, new NullImageDriver());
    }

    public function testGenerateReturnsNoImages(): void
    {
        $response = (new NullImageDriver())->generate(ImageGenerationRequest::make('a cat'));

        $this->assertSame([], $response->images());
    }
}
