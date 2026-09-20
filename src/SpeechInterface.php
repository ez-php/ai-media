<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

use EzPhp\AiMedia\Request\SpeechRequest;
use EzPhp\AiMedia\Response\SpeechResponse;

/**
 * Contract for a text-to-speech driver.
 *
 * @package EzPhp\AiMedia
 */
interface SpeechInterface
{
    /**
     * Synthesize speech audio from text.
     *
     * @param SpeechRequest $request
     *
     * @return SpeechResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed provider response.
     */
    public function synthesize(SpeechRequest $request): SpeechResponse;
}
