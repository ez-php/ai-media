<?php

declare(strict_types=1);

namespace EzPhp\AiMedia;

/**
 * Thrown when a provider returns an HTTP error or a malformed response body.
 *
 * @package EzPhp\AiMedia
 */
final class AiMediaRequestException extends AiMediaException
{
    /**
     * @param int    $status HTTP status code returned by the provider.
     * @param string $body   Raw response body returned by the provider.
     *
     * @return self
     */
    public static function fromResponse(int $status, string $body): self
    {
        return new self("Provider request failed with status {$status}: {$body}");
    }

    /**
     * @param string $reason What was missing or malformed in the response body.
     *
     * @return self
     */
    public static function malformedResponse(string $reason): self
    {
        return new self("Malformed provider response: {$reason}");
    }
}
