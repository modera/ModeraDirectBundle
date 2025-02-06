<?php

namespace Modera\DirectBundle\Router;

use Modera\DirectBundle\Exception\CallException;

class Call
{
    /**
     * The ExtDirect action called. With reference to Bundle via underscore '_'.
     */
    protected string $action;

    /**
     * The ExtDirect method called.
     */
    protected string $method;

    /**
     * The ExtDirect request type.
     */
    protected string $type;

    /**
     * The ExtDirect transaction id.
     */
    protected ?int $tid;

    /**
     * The ExtDirect call params.
     *
     * @var array<int|string, mixed>
     */
    protected array $data;

    /**
     * The ExtDirect request type. Where values in ('form', 'single').
     */
    protected string $callType;

    /**
     * The ExtDirect upload reference.
     */
    protected bool $upload;

    /**
     * @param array<string, mixed> $call
     */
    public function __construct(array $call, string $type)
    {
        $this->callType = $type;

        if ('single' === $type) {
            $this->initializeFromSingle($call);
        } else {
            $this->initializeFromForm($call);
        }
    }

    /**
     * Get the requested action.
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get the requested method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the request method params.
     *
     * @return array<int|string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Return a result wrapper to ExtDirect method call.
     *
     * @param array<mixed> $result
     *
     * @return array{
     *     'type': string,
     *     'tid': ?int,
     *     'action': string,
     *     'method': string,
     *     'result': array<mixed>
     *  }
     */
    public function getResponse(array $result): array
    {
        return [
            'type' => 'rpc',
            'tid' => $this->tid,
            'action' => $this->action,
            'method' => $this->method,
            'result' => $result,
        ];
    }

    /**
     * Return an exception to ExtDirect call stack.
     *
     * @return array{
     *     'type': string,
     *     'tid': ?int,
     *     'action': string,
     *     'method': string,
     *     'message'?: string,
     *     'class'?: string,
     *     'where'?: string
     * }
     */
    public function getException(\Exception $exception, string $environment = 'dev'): array
    {
        // https://docs.sencha.com/extjs/6.0.2/guides/backend_connectors/direct/specification.html
        $response = [
            'type' => 'exception',
            'tid' => $this->tid,
            'action' => $this->action,
            'method' => $this->method,
        ];

        if ($exception instanceof CallException) {
            if (null !== $exception->getMessage()) {
                $response = \array_merge($response, [
                    'message' => $exception->getMessage(),
                ]);
            }

            if ($exception->getPrevious()) {
                $exception = $exception->getPrevious();
            }
        }

        if (\in_array($environment, ['test', 'dev'])) {
            return \array_merge($response, [
                'class' => \get_class($exception),
                'message' => $exception->getMessage(),
                'where' => $exception->getTraceAsString(),
            ]);
        } else {
            return $response;
        }
    }

    /**
     * Initialize the call properties from a single call.
     *
     * @param array<string, mixed> $arr
     */
    private function initializeFromSingle(array $arr): void
    {
        /** @var array{
         *     'action': string,
         *     'method': string,
         *     'type': string,
         *     'tid'?: ?int,
         *     'data'?: array<int, mixed>|mixed
         * } & array<string, mixed> $call */
        $call = $arr;

        $this->action = $call['action'];
        $this->method = $call['method'];
        $this->type = $call['type'];
        $this->tid = $call['tid'] ?? null;
        $this->data = \is_array($call['data'] ?? null) ? $call['data'] : [];
    }

    /**
     * Initialize the call properties from a form call.
     *
     * @param array<string, mixed> $arr
     */
    private function initializeFromForm(array $arr): void
    {
        /** @var array{
         *     'extAction': string,
         *     'extMethod': string,
         *     'extType': string,
         *     'extTID'?: ?int,
         *     'extUpload': bool
         * } $call */
        $call = $arr;

        $this->action = $call['extAction'];
        unset($call['extAction']);
        $this->method = $call['extMethod'];
        unset($call['extMethod']);
        $this->type = $call['extType'];
        unset($call['extType']);
        $this->tid = $call['extTID'] ?? null;
        unset($call['extTID']);
        $this->upload = $call['extUpload'];
        unset($call['extUpload']);

        /**
         * @var array<string, mixed> $data
         */
        $data = $call;
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }
}
