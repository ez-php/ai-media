# ez-php/ai-media

Image generation, audio transcription, and text-to-speech via OpenAI/Gemini drivers over `ez-php/http-client`.

---

## Installation

```bash
composer require ez-php/ai-media
```

---

## Usage

```php
use EzPhp\AiMedia\AiMedia;
use EzPhp\AiMedia\Request\ImageGenerationRequest;
use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Request\TranscriptionRequest;

// Image generation
$images = AiMedia::image(ImageGenerationRequest::make('a red bicycle on a beach'));
$url = $images->first()->url();

// Transcription
$audioBytes = file_get_contents('recording.mp3');
$transcript = AiMedia::transcribe(TranscriptionRequest::make($audioBytes, 'recording.mp3', 'audio/mpeg'));
echo $transcript->text();

// Text-to-speech
$speech = AiMedia::speech(SpeechRequest::make('Hello there.'));
file_put_contents('hello.mp3', $speech->audio());
```

### Request options

Each request is immutable; the `with*()` methods return a modified copy. `withModel()` overrides
the driver's configured default model for one call.

| Request | Option | Default | Notes |
|---|---|---|---|
| `ImageGenerationRequest` | `withCount(int)` | `1` | Images to generate (OpenAI `n`, Gemini `sampleCount`) |
| | `withSize(string)` | `'1024x1024'` | OpenAI only; Gemini ignores it |
| | `withModel(string)` | config default | |
| `SpeechRequest` | `withVoice(string)` | `'alloy'` | OpenAI voice name, or a Gemini prebuilt voice name (e.g. `'Kore'`) |
| | `withFormat(string)` | `'mp3'` | OpenAI `response_format` (`mp3`, `opus`, `aac`, `flac`, `wav`, `pcm`); Gemini always returns raw PCM (`audio/pcm`) |
| | `withModel(string)` | config default | |
| `TranscriptionRequest` | `withLanguage(string)` | `null` (auto-detect) | ISO-639-1 hint sent to OpenAI; Gemini only echoes it back in the response |
| | `withModel(string)` | config default | |

```php
$images = AiMedia::image(
    ImageGenerationRequest::make('a red bicycle on a beach')
        ->withCount(2)
        ->withSize('1792x1024')
        ->withModel('dall-e-3'),
);

$speech = AiMedia::speech(
    SpeechRequest::make('Hallo zusammen.')->withVoice('nova')->withFormat('wav'),
);

$transcript = AiMedia::transcribe(
    TranscriptionRequest::make($audioBytes, 'recording.mp3', 'audio/mpeg')->withLanguage('de'),
);
```

Register `EzPhp\AiMedia\AiMediaServiceProvider` and configure `config/ai_media.php`:

```php
return [
    'image_driver' => 'openai',
    'transcription_driver' => 'openai',
    'speech_driver' => 'openai',
    'openai' => ['api_key' => getenv('OPENAI_API_KEY') ?: null],
    'gemini' => ['api_key' => getenv('GEMINI_API_KEY') ?: null],
];
```

`image_driver`, `transcription_driver`, and `speech_driver` are selected independently
(`openai`, `gemini`, or `null` — the default) since a provider offering one capability
does not necessarily offer the others.

---

## License

MIT
