<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\Response\TranscriptionResponse;

/**
 * Contract for an audio transcription driver.
 *
 * @package EzPhp\AiMedia
 */
interface TranscriberInterface
{
    /**
     * Transcribe audio into text.
     *
     * @param TranscriptionRequest $request
     *
     * @return TranscriptionResponse
     *
     * @throws AiMediaRequestException On HTTP error or malformed provider response.
     */
    public function transcribe(TranscriptionRequest $request): TranscriptionResponse;
}
