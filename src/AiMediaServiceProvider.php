<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

use EzPhp\AiMedia\Driver\GeminiConfig;
use EzPhp\AiMedia\Driver\GeminiImageDriver;
use EzPhp\AiMedia\Driver\GeminiSpeechDriver;
use EzPhp\AiMedia\Driver\GeminiTranscriberDriver;
use EzPhp\AiMedia\Driver\NullImageDriver;
use EzPhp\AiMedia\Driver\NullSpeechDriver;
use EzPhp\AiMedia\Driver\NullTranscriberDriver;
use EzPhp\AiMedia\Driver\OpenAiConfig;
use EzPhp\AiMedia\Driver\OpenAiImageDriver;
use EzPhp\AiMedia\Driver\OpenAiSpeechDriver;
use EzPhp\AiMedia\Driver\OpenAiTranscriberDriver;
use EzPhp\Contracts\ConfigInterface;
use EzPhp\Contracts\ContainerInterface;
use EzPhp\Contracts\ServiceProvider;
use EzPhp\HttpClient\CurlTransport;
use EzPhp\HttpClient\HttpClient;

/**
 * Binds ImageGeneratorInterface, TranscriberInterface, and SpeechInterface to the
 * drivers configured via config/ai_media.php and wires the AiMedia static façade
 * on boot.
 *
 * The three capabilities are selected independently, mirroring ez-php/ai's split
 * between `ai.driver` and `ai.embedding_driver` — a provider offering images may
 * not offer transcription, and vice versa.
 *
 * Supported drivers per capability: openai, gemini, null (default).
 *
 * Minimal config/ai_media.php:
 *
 *   return [
 *       'image_driver' => 'openai',
 *       'transcription_driver' => 'openai',
 *       'speech_driver' => 'openai',
 *       'openai' => ['api_key' => env('OPENAI_API_KEY')],
 *   ];
 *
 * @package EzPhp\AiMedia
 */
final class AiMediaServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ImageGeneratorInterface::class, function (ContainerInterface $app): ImageGeneratorInterface {
            $config = $app->make(ConfigInterface::class);
            $driver = $config->get('ai_media.image_driver', 'null');

            return match (is_string($driver) ? $driver : 'null') {
                'openai' => new OpenAiImageDriver($this->makeHttp(), $this->makeOpenAiConfig($config)),
                'gemini' => new GeminiImageDriver($this->makeHttp(), $this->makeGeminiConfig($config)),
                default => new NullImageDriver(),
            };
        });

        $this->app->bind(TranscriberInterface::class, function (ContainerInterface $app): TranscriberInterface {
            $config = $app->make(ConfigInterface::class);
            $driver = $config->get('ai_media.transcription_driver', 'null');

            return match (is_string($driver) ? $driver : 'null') {
                'openai' => new OpenAiTranscriberDriver($this->makeHttp(), $this->makeOpenAiConfig($config)),
                'gemini' => new GeminiTranscriberDriver($this->makeHttp(), $this->makeGeminiConfig($config)),
                default => new NullTranscriberDriver(),
            };
        });

        $this->app->bind(SpeechInterface::class, function (ContainerInterface $app): SpeechInterface {
            $config = $app->make(ConfigInterface::class);
            $driver = $config->get('ai_media.speech_driver', 'null');

            return match (is_string($driver) ? $driver : 'null') {
                'openai' => new OpenAiSpeechDriver($this->makeHttp(), $this->makeOpenAiConfig($config)),
                'gemini' => new GeminiSpeechDriver($this->makeHttp(), $this->makeGeminiConfig($config)),
                default => new NullSpeechDriver(),
            };
        });
    }

    /**
     * Eagerly resolve the three interfaces and wire them to the AiMedia static façade.
     *
     * @return void
     */
    public function boot(): void
    {
        AiMedia::setImageGenerator($this->app->make(ImageGeneratorInterface::class));
        AiMedia::setTranscriber($this->app->make(TranscriberInterface::class));
        AiMedia::setSpeech($this->app->make(SpeechInterface::class));
    }

    /**
     * @param ConfigInterface $config
     *
     * @return OpenAiConfig
     */
    private function makeOpenAiConfig(ConfigInterface $config): OpenAiConfig
    {
        $apiKey = $config->get('ai_media.openai.api_key', '');
        $imageModel = $config->get('ai_media.openai.image_model', OpenAiConfig::DEFAULT_IMAGE_MODEL);
        $transcriptionModel = $config->get(
            'ai_media.openai.transcription_model',
            OpenAiConfig::DEFAULT_TRANSCRIPTION_MODEL,
        );
        $speechModel = $config->get('ai_media.openai.speech_model', OpenAiConfig::DEFAULT_SPEECH_MODEL);
        $baseUrl = $config->get('ai_media.openai.base_url', OpenAiConfig::DEFAULT_BASE_URL);

        return new OpenAiConfig(
            is_string($apiKey) ? $apiKey : '',
            is_string($imageModel) ? $imageModel : OpenAiConfig::DEFAULT_IMAGE_MODEL,
            is_string($transcriptionModel) ? $transcriptionModel : OpenAiConfig::DEFAULT_TRANSCRIPTION_MODEL,
            is_string($speechModel) ? $speechModel : OpenAiConfig::DEFAULT_SPEECH_MODEL,
            is_string($baseUrl) ? $baseUrl : OpenAiConfig::DEFAULT_BASE_URL,
        );
    }

    /**
     * @param ConfigInterface $config
     *
     * @return GeminiConfig
     */
    private function makeGeminiConfig(ConfigInterface $config): GeminiConfig
    {
        $apiKey = $config->get('ai_media.gemini.api_key', '');
        $imageModel = $config->get('ai_media.gemini.image_model', GeminiConfig::DEFAULT_IMAGE_MODEL);
        $transcriptionModel = $config->get(
            'ai_media.gemini.transcription_model',
            GeminiConfig::DEFAULT_TRANSCRIPTION_MODEL,
        );
        $speechModel = $config->get('ai_media.gemini.speech_model', GeminiConfig::DEFAULT_SPEECH_MODEL);
        $baseUrl = $config->get('ai_media.gemini.base_url', GeminiConfig::DEFAULT_BASE_URL);

        return new GeminiConfig(
            is_string($apiKey) ? $apiKey : '',
            is_string($imageModel) ? $imageModel : GeminiConfig::DEFAULT_IMAGE_MODEL,
            is_string($transcriptionModel) ? $transcriptionModel : GeminiConfig::DEFAULT_TRANSCRIPTION_MODEL,
            is_string($speechModel) ? $speechModel : GeminiConfig::DEFAULT_SPEECH_MODEL,
            is_string($baseUrl) ? $baseUrl : GeminiConfig::DEFAULT_BASE_URL,
        );
    }

    /**
     * @return HttpClient
     */
    private function makeHttp(): HttpClient
    {
        return new HttpClient(new CurlTransport());
    }
}
