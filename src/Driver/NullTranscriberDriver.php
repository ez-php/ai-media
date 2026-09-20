<?php

declare(strict_types=1);

namespace EzPhp\AiMedia\Driver;

use EzPhp\AiMedia\Request\TranscriptionRequest;
use EzPhp\AiMedia\Response\TranscriptionResponse;
use EzPhp\AiMedia\TranscriberInterface;

/**
 * Transcription driver that always returns an empty transcript. Default when no driver is configured.
 *
 * @package EzPhp\AiMedia\Driver
 */
final class NullTranscriberDriver implements TranscriberInterface
{
    /**
     * @param TranscriptionRequest $request
     *
     * @return TranscriptionResponse
     */
    public function transcribe(TranscriptionRequest $request): TranscriptionResponse
    {
        return new TranscriptionResponse(text: '');
    }
}
