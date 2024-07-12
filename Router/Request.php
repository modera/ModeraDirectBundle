<?php

namespace Modera\DirectBundle\Router;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    /**
     * The Symfony request object taked by ModeraDirectBundle controller.
     */
    protected SymfonyRequest $request;

    /**
     * The HTTP_RAW_POST_DATA if the Direct call is a batch call.
     */
    protected string $rawPost;

    /**
     * The $_POST data if the Direct Call is a form call.
     *
     * @var array<mixed>
     */
    protected array $post;

    /**
     * Store the Direct Call type. Where values in ('form', 'batch').
     */
    protected string $callType;

    /**
     * Is upload request?
     */
    protected bool $isUpload = false;

    /**
     * Store the Direct calls. Only 1 if it is a form call or 1.* if it is a batch call.
     *
     * @var ?array<int, Call>
     */
    protected ?array $calls = null;

    /**
     * Store the $_FILES if it is a form call.
     *
     * @var array<mixed>
     */
    protected array $files;

    public function __construct(SymfonyRequest $request)
    {
        // before we were checking if $request->request->count() > 0 to
        // check if a given request is a form submission but in some cases
        // (Symfony?) merges all parameters (event decoded json body) into
        // $request->request, and it was causing problems. Hopefully this solution
        // will work in all cases
        $hasJsonInBody = \is_array(\json_decode($request->getContent(), true));

        $this->request = $request;
        $this->rawPost = $request->getContent() ? $request->getContent() : '{}';
        $this->post = $request->request->all();
        $this->files = $request->files->all();
        $this->callType = $hasJsonInBody ? 'batch' : 'form';
        $this->isUpload = 'true' == $request->request->get('extUpload');
    }

    /**
     * Return the type of Direct call.
     */
    public function getCallType(): string
    {
        return $this->callType;
    }

    /**
     * Is upload request?
     */
    public function isUpload(): bool
    {
        return $this->isUpload;
    }

    /**
     * Return the files from call.
     *
     * @return array<mixed>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the direct calls object.
     *
     * @return array<int, Call>
     */
    public function getCalls(): array
    {
        if (null === $this->calls) {
            $this->calls = $this->extractCalls();
        }

        return $this->calls;
    }

    /**
     * Extract the ExtDirect calls from request.
     *
     * @return array<int, Call>
     */
    public function extractCalls(): array
    {
        /** @var array<int, Call> $calls */
        $calls = [];

        if ('form' === $this->callType) {
            $calls[] = new Call($this->post, 'form');
        } else {
            $decoded = \json_decode($this->rawPost);
            $decoded = !\is_array($decoded) ? [$decoded] : $decoded;

            \array_walk_recursive($decoded, [$this, 'parseRawToArray']);
            // TODO: check utf8 config option from bundle
            // array_walk_recursive($decoded, array($this, 'decode'));

            foreach ($decoded as $call) {
                $calls[] = new Call((array) $call, 'single');
            }
        }

        return $calls;
    }

    /**
     * Force the utf8 decodification from all string values.
     *
     * @param mixed $value Mixed value
     */
    public function decode(&$value, string $key): void
    {
        if (\is_string($value)) {
            $value = \utf8_decode($value);
        }
    }

    /**
     * Parse a raw http post to a php array.
     *
     * @param mixed $value Mixed value
     */
    private function parseRawToArray(&$value, string $key): void
    {
        // parse a json string to an array
        if (\is_string($value)) {
            $pos = \substr($value, 0, 1);
            if ('[' === $pos || '(' === $pos || '{' === $pos) {
                $json = \json_decode($value);
            } else {
                $json = $value;
            }

            if ($json) {
                $value = $json;
            }
        }

        // if the value is an object, parse it to an array
        if (\is_object($value)) {
            $value = (array) $value;
        }

        // call the recursive function to all keys of array
        if (\is_array($value)) {
            \array_walk_recursive($value, [$this, 'parseRawToArray']);
        }
    }
}
