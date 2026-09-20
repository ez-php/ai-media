<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Driver\OpenAiConfig;
use EzPhp\AiMedia\Driver\OpenAiImageDriver;
use EzPhp\AiMedia\ImageGeneratorInterface;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\HttpClient\FakeTransport;
use EzPhp\HttpClient\HttpClient;
use EzPhp\HttpClient\HttpResponse;
use Tests\AiMedia\TestCase;

final class OpenAiImageDriverTest extends TestCase
{
    private function makeDriver(FakeTransport $transport): OpenAiImageDriver
    {
        return new OpenAiImageDriver(new HttpClient($transport), new OpenAiConfig('test-key'));
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(ImageGeneratorInterface::class, $this->makeDriver(new FakeTransport()));
    }

    public function testGenerateReturnsUrlBasedImages(): void
    {
        $body = (string) json_encode([
            'data' => [
                ['url' => 'https://example.com/1.png', 'revised_prompt' => 'a red bicycle, photorealistic'],
            ],
        ]);
        $transport = new FakeTransport(['*' => new HttpResponse(200, $body)]);

        $response = $this->makeDriver($transport)->generate(ImageGenerationRequest::make('a red bicycle'));

        $this->assertSame('https://example.com/1.png', $response->first()->url());
        $this->assertSame('a red bicycle, photorealistic', $response->first()->revisedPrompt());
    }

    public function testGenerateSendsPromptCountAndSize(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, (string) json_encode(['data' => []]))]);

        $this->makeDriver($transport)->generate(
            ImageGenerationRequest::make('a red bicycle')->withCount(2)->withSize('512x512'),
        );

        $recorded = $transport->getRecorded();
        $decoded = json_decode($recorded[0]['body'], true);

        $this->assertIsArray($decoded);
        $this->assertSame('a red bicycle', $decoded['prompt']);
        $this->assertSame(2, $decoded['n']);
        $this->assertSame('512x512', $decoded['size']);
        $this->assertSame('Bearer test-key', $recorded[0]['headers']['Authorization']);
    }

    public function testThrowsOnHttpError(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(400, '{"error":"bad request"}')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->generate(ImageGenerationRequest::make('a red bicycle'));
    }

    public function testThrowsOnMalformedResponse(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, '{}')]);

        $this->expectException(AiMediaRequestException::class);

        $this->makeDriver($transport)->generate(ImageGenerationRequest::make('a red bicycle'));
    }
}
