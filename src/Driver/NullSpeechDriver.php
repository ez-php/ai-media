<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Response\SpeechResponse;
use EzPhp\AiMedia\SpeechInterface;

/**
 * Text-to-speech driver that always returns empty audio. Default when no driver is configured.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class NullSpeechDriver implements SpeechInterface
{
    /**
     * @param SpeechRequest $request
     *
     * @return SpeechResponse
     */
    public function synthesize(SpeechRequest $request): SpeechResponse
    {
        return new SpeechResponse(audio: '');
    }
}
