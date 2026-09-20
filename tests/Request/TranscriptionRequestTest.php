<?php

declare(strict_types=1);

namespace Tests\AiMedia\Request;

use EzPhp\AiMedia\Request\TranscriptionRequest;
use Tests\AiMedia\TestCase;

final class TranscriptionRequestTest extends TestCase
{
    public function testMakeSetsDefaults(): void
    {
        $request = TranscriptionRequest::make('bytes');

        $this->assertSame('bytes', $request->audio());
        $this->assertSame('audio.mp3', $request->filename());
        $this->assertSame('audio/mpeg', $request->mimeType());
        $this->assertNull($request->language());
        $this->assertNull($request->model());
    }

    public function testMakeAcceptsFilenameAndMimeType(): void
    {
        $request = TranscriptionRequest::make('bytes', 'clip.wav', 'audio/wav');

        $this->assertSame('clip.wav', $request->filename());
        $this->assertSame('audio/wav', $request->mimeType());
    }

    public function testWithersReturnNewInstances(): void
    {
        $request = TranscriptionRequest::make('bytes');

        $withLanguage = $request->withLanguage('en');
        $withModel = $request->withModel('whisper-2');

        $this->assertNotSame($request, $withLanguage);
        $this->assertSame('en', $withLanguage->language());
        $this->assertSame('whisper-2', $withModel->model());
        $this->assertNull($request->language());
    }
}
