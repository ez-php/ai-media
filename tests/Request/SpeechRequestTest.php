<?php

declare(strict_types=1);

namespace Tests\AiMedia\Request;

use EzPhp\AiMedia\Request\SpeechRequest;
use Tests\AiMedia\TestCase;

final class SpeechRequestTest extends TestCase
{
    public function testMakeSetsDefaults(): void
    {
        $request = SpeechRequest::make('Hello there.');

        $this->assertSame('Hello there.', $request->text());
        $this->assertSame('alloy', $request->voice());
        $this->assertSame('mp3', $request->format());
        $this->assertNull($request->model());
    }

    public function testWithersReturnNewInstances(): void
    {
        $request = SpeechRequest::make('Hello there.');

        $withVoice = $request->withVoice('nova');
        $withFormat = $request->withFormat('wav');
        $withModel = $request->withModel('tts-1-hd');

        $this->assertNotSame($request, $withVoice);
        $this->assertSame('nova', $withVoice->voice());
        $this->assertSame('wav', $withFormat->format());
        $this->assertSame('tts-1-hd', $withModel->model());
        $this->assertSame('alloy', $request->voice());
    }
}
