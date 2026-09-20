<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Driver\GeminiConfig;
use EzPhp\AiMedia\Driver\GeminiTranscriberDriver;
use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\TranscriberInterface;
use EzPhp\HttpClient\FakeTransport;
use EzPhp\HttpClient\HttpClient;
use EzPhp\HttpClient\HttpResponse;
use Tests\AiMedia\TestCase;

final class GeminiTranscriberDriverTest extends TestCase
{
    private function makeDriver(FakeTransport $transport): GeminiTranscriberDriver
    {
        return new GeminiTranscriberDriver(new HttpClient($transport), new GeminiConfig('test-key'));
    }

    private function candidateBody(string $text): string
    {
        return (string) json_encode([
            'candidates' => [
                ['content' => ['parts' => [['text' => $text]]]],
            ],
        ]);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(TranscriberInterface::class, $this->makeDriver(new FakeTransport()));
    }

    public function testTranscribeReturnsCandidateText(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, $this->candidateBody('hello world'))]);

        $response = $this->makeDriver($transport)->transcribe(TranscriptionRequest::make('bytes'));

        $this->assertSame('hello world', $response->text());
    }

    public function testTranscribeSendsInlineAudioData(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, $this->candidateBody('hi'))]);

        $this->makeDriver($transport)->transcribe(
            TranscriptionRequest::make('raw-audio', 'clip.wav', 'audio/wav'),
        );

        $decoded = json_decode($transport->getRecorded()[0]['body'], true);
        $this->assertIsArray($decoded);

        $contents = $decoded['contents'];
        $this->assertIsArray($contents);
        $firstContent = $contents[0];
        $this->assertIsArray($firstContent);
        $parts = $firstContent['parts'];
        $this->assertIsArray($parts);
        $audioPart = $parts[1];
        $this->assertIsArray($audioPart);
        $inlineData = $audioPart['inline_data'];
        $this->assertIsArray($inlineData);

        $this->assertSame('audio/wav', $inlineData['mime_type']);
        $this->assertSame(base64_encode('raw-audio'), $inlineData['data']);
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
