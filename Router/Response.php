<?php

namespace Modera\DirectBundle\Router;

class Response
{
    /**
     * Call type to respond. Where values in ('form', 'single).
     */
    protected string $type;

    /**
     * Is upload request?
     */
    protected bool $isUpload = false;

    /**
     * Initialize the object setting it type.
     */
    public function __construct(string $type, bool $isUpload)
    {
        $this->type = $type;
        $this->isUpload = $isUpload;
    }

    /**
     * Encode the response into a valid json ExtDirect result.
     *
     * @param array<mixed> $result
     */
    public function encode(array $result): string
    {
        if ('form' === $this->type && $this->isUpload) {
            return '<html><body><textarea>'.\json_encode($result[0] ?? null).'</textarea></body></html>';
        } else {
            return \json_encode($result) ?: '{}';
        }
    }
}
