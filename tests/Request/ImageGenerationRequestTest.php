<?php

declare(strict_types=1);

namespace Tests\AiMedia\Request;

use EzPhp\AiMedia\Request\ImageGenerationRequest;
use Tests\AiMedia\TestCase;

final class ImageGenerationRequestTest extends TestCase
{
    public function testMakeSetsDefaults(): void
    {
        $request = ImageGenerationRequest::make('a red bicycle');

        $this->assertSame('a red bicycle', $request->prompt());
        $this->assertSame(1, $request->count());
        $this->assertSame('1024x1024', $request->size());
        $this->assertNull($request->model());
    }

    public function testWithersReturnNewInstances(): void
    {
        $request = ImageGenerationRequest::make('a red bicycle');

        $withCount = $request->withCount(4);
        $withSize = $request->withSize('512x512');
        $withModel = $request->withModel('dall-e-2');

        $this->assertNotSame($request, $withCount);
        $this->assertSame(4, $withCount->count());
        $this->assertSame('512x512', $withSize->size());
        $this->assertSame('dall-e-2', $withModel->model());

        // Original is unchanged
        $this->assertSame(1, $request->count());
        $this->assertSame('1024x1024', $request->size());
        $this->assertNull($request->model());
    }
}
