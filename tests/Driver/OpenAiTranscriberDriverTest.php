<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Driver\OpenAiConfig;
use EzPhp\AiMedia\Driver\OpenAiTranscriberDriver;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\TranscriberInterface;
use EzPhp\HttpClient\FakeTransport;
use EzPhp\HttpClient\HttpClient;
use EzPhp\HttpClient\HttpResponse;
use Tests\AiMedia\TestCase;

final class OpenAiTranscriberDriverTest extends TestCase
{
    private function makeDriver(FakeTransport $transport): OpenAiTranscriberDriver
    {
        return new OpenAiTranscriberDriver(new HttpClient($transport), new OpenAiConfig('test-key'));
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(TranscriberInterface::class, $this->makeDriver(new FakeTransport()));
    }

    public function testTranscribeReturnsTextLanguageAndDuration(): void
    {
        $body = (string) json_encode(['text' => 'hello world', 'language' => 'english', 'duration' => 2.4]);
        $transport = new FakeTransport(['*' => new HttpResponse(200, $body)]);

        $response = $this->makeDriver($transport)->transcribe(TranscriptionRequest::make('bytes'));

        $this->assertSame('hello world', $response->text());
        $this->assertSame('english', $response->language());
        $this->assertSame(2.4, $response->duration());
    }

    public function testTranscribeSendsMultipartBody(): void
    {
        $body = (string) json_encode(['text' => 'hi']);
        $transport = new FakeTransport(['*' => new HttpResponse(200, $body)]);

        $this->makeDriver($transport)->transcribe(
            TranscriptionRequest::make('audio-bytes', 'clip.mp3', 'audio/mpeg')->withLanguage('en'),
        );

        $recorded = $transport->getRecorded();
        $this->assertStringContainsString('multipart/form-data', $recorded[0]['headers']['Content-Type']);
        $this->assertStringContainsString('audio-bytes', $recorded[0]['body']);
        $this->assertStringContainsString('clip.mp3', $recorded[0]['body']);
        $this->assertStringContainsString('whisper-1', $recorded[0]['body']);
        $this->assertStringContainsString('en', $recorded[0]['body']);
    }

    public function testThrowsOnHttpError(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(400, '{"error":"bad request"}')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->transcribe(TranscriptionRequest::make('bytes'));
    }

    public function testThrowsOnMalformedResponse(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, '{}')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->transcribe(TranscriptionRequest::make('bytes'));
    }
}
