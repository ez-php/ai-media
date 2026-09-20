<?php

declare(strict_types=1);

namespace Tests\AiMedia\Driver;

use EzPhp\AiMedia\AiMediaRequestException;
use EzPhp\AiMedia\Driver\GeminiConfig;
use EzPhp\AiMedia\Driver\GeminiImageDriver;
use EzPhp\AiMedia\ImageGeneratorInterface;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\HttpClient\FakeTransport;
use EzPhp\HttpClient\HttpClient;
use EzPhp\HttpClient\HttpResponse;
use Tests\AiMedia\TestCase;

final class GeminiImageDriverTest extends TestCase
{
    private function makeDriver(FakeTransport $transport): GeminiImageDriver
    {
        return new GeminiImageDriver(new HttpClient($transport), new GeminiConfig('test-key'));
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(ImageGeneratorInterface::class, $this->makeDriver(new FakeTransport()));
    }

    public function testGenerateReturnsBase64Images(): void
    {
        $body = (string) json_encode(['predictions' => [['bytesBase64Encoded' => base64_encode('bytes')]]]);
        $transport = new FakeTransport(['*' => new HttpResponse(200, $body)]);

        $response = $this->makeDriver($transport)->generate(ImageGenerationRequest::make('a red bicycle'));

        $this->assertSame('bytes', $response->first()->binary());
        $this->assertFalse($response->first()->hasUrl());
    }

    public function testGenerateSendsPromptAndSampleCount(): void
    {
        $transport = new FakeTransport(['*' => new HttpResponse(200, (string) json_encode(['predictions' => []]))]);

        $this->makeDriver($transport)->generate(ImageGenerationRequest::make('a red bicycle')->withCount(3));

        $recorded = $transport->getRecorded();
        $decoded = json_decode($recorded[0]['body'], true);

        $this->assertIsArray($decoded);
        $instances = $decoded['instances'];
        $parameters = $decoded['parameters'];
        $this->assertIsArray($instances);
        $this->assertIsArray($parameters);
        $firstInstance = $instances[0];
        $this->assertIsArray($firstInstance);

        $this->assertSame('a red bicycle', $firstInstance['prompt']);
        $this->assertSame(3, $parameters['sampleCount']);
        $this->assertStringContainsString('key=test-key', $recorded[0]['url']);
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
