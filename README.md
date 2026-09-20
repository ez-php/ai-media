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

Register `EzPhp\AiMedia\AiMediaServiceProvider` and configure `config/ai_media.php`:

```php
return [
    'image_driver' => 'openai',
    'transcription_driver' => 'openai',
    'speech_driver' => 'openai',
    'openai' => ['api_key' => env('OPENAI_API_KEY')],
    'gemini' => ['api_key' => env('GEMINI_API_KEY')],
];
```

`image_driver`, `transcription_driver`, and `speech_driver` are selected independently
(`openai`, `gemini`, or `null` — the default) since a provider offering one capability
does not necessarily offer the others.

---

## License

MIT
