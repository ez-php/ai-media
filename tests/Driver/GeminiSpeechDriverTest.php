<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Driver\GeminiConfig;
use EzPhp\AiMedia\Driver\GeminiSpeechDriver;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\SpeechInterface;
use EzPhp\HttpClient\FakeTransport;
use EzPhp\HttpClient\HttpClient;
use EzPhp\HttpClient\HttpResponse;
use Tests\AiMedia\TestCase;

final class GeminiSpeechDriverTest extends TestCase
{
    private function makeDriver(FakeTransport $transport): GeminiSpeechDriver
    {
        return new GeminiSpeechDriver(new HttpClient($transport), new GeminiConfig('test-key'));
    }

    private function inlineAudioBody(string $binary, string $mimeType = 'audio/pcm'): string
    {
        return (string) json_encode([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['inlineData' => ['mimeType' => $mimeType, 'data' => base64_encode($binary)]],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(SpeechInterface::class, $this->makeDriver(new FakeTransport()));
    }

    public function testSynthesizeDecodesInlineAudio(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, $this->inlineAudioBody('raw-pcm-bytes'))]);

        $response = $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.'));

        $this->assertSame('raw-pcm-bytes', $response->audio());
        $this->assertSame('audio/pcm', $response->mimeType());
    }

    public function testSynthesizeSendsTextAndVoiceConfig(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, $this->inlineAudioBody('bytes'))]);

        $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.')->withVoice('Kore'));

        $decoded = json_decode($transport->getRecorded()[0]['body'], true);
        $this->assertIsArray($decoded);

        $contents = $decoded['contents'];
        $generationConfig = $decoded['generationConfig'];
        $this->assertIsArray($contents);
        $this->assertIsArray($generationConfig);

        $firstContent = $contents[0];
        $this->assertIsArray($firstContent);
        $parts = $firstContent['parts'];
        $this->assertIsArray($parts);
        $firstPart = $parts[0];
        $this->assertIsArray($firstPart);

        $speechConfig = $generationConfig['speechConfig'];
        $this->assertIsArray($speechConfig);
        $voiceConfig = $speechConfig['voiceConfig'];
        $this->assertIsArray($voiceConfig);
        $prebuiltVoiceConfig = $voiceConfig['prebuiltVoiceConfig'];
        $this->assertIsArray($prebuiltVoiceConfig);

        $this->assertSame('Hello there.', $firstPart['text']);
        $this->assertSame(['AUDIO'], $generationConfig['responseModalities']);
        $this->assertSame('Kore', $prebuiltVoiceConfig['voiceName']);
    }

    public function testThrowsOnHttpError(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(400, '{"error":"bad request"}')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.'));
    }

    public function testThrowsOnMalformedResponse(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, '{}')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->synthesize(SpeechRequest::make('Hello there.'));
    }
}
