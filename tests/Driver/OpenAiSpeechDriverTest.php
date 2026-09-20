<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Driver\OpenAiConfig;
use EzPhp\AiMedia\Driver\OpenAiSpeechDriver;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\SpeechInterface;
use EzPhp\HttpClient\FakeTransport;
use EzPhp\HttpClient\HttpClient;
use EzPhp\HttpClient\HttpResponse;
use Tests\AiMedia\TestCase;

final class OpenAiSpeechDriverTest extends TestCase
{
    private function makeDriver(FakeTransport $transport): OpenAiSpeechDriver
    {
        return new OpenAiSpeechDriver(new HttpClient($transport), new OpenAiConfig('test-key'));
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(SpeechInterface::class, $this->makeDriver(new FakeTransport()));
    }

    public function testSynthesizeReturnsRawAudioBytes(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, 'raw-mp3-bytes')]);

        $response = $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.'));

        $this->assertSame('raw-mp3-bytes', $response->audio());
        $this->assertSame('audio/mpeg', $response->mimeType());
    }

    public function testMimeTypeMatchesRequestedFormat(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, 'raw-bytes')]);

        $response = $this->makeDriver($transport)->synthesize(
            SpeechRequest::make('Hello there.')->withFormat('wav'),
        );

        $this->assertSame('audio/wav', $response->mimeType());
    }

    public function testSendsTextVoiceAndFormat(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, 'bytes')]);

        $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.')->withVoice('nova'));

        $decoded = json_decode($transport->getRecorded()[0]['body'], true);

        $this->assertIsArray($decoded);
        $this->assertSame('Hello there.', $decoded['input']);
        $this->assertSame('nova', $decoded['voice']);
        $this->assertSame('mp3', $decoded['response_format']);
    }

    public function testThrowsOnHttpError(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(500, 'error')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.'));
    }
}
