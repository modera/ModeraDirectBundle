<?php

namespace Modera\DirectBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ControllerApi
{
    protected ContainerInterface $container;

    /**
     * Store the controller reflection object.
     */
    protected \ReflectionClass $reflection;

    protected string $remoteAttribute;

    protected string $formAttribute;

    protected string $safeAttribute;

    protected string $unsafeAttribute;

    /**
     * @var ?array<int, array{'name': string, 'len': int, 'formHandler': bool}>
     */
    protected ?array $api;

    public function __construct(ContainerInterface $container, string $controller)
    {
        $this->container = $container;

        /** @var class-string $reflectionClass */
        $reflectionClass = $controller;
        $this->reflection = new \ReflectionClass($reflectionClass);

        /** @var string $remoteAttribute */
        $remoteAttribute = $this->container->getParameter('direct.api.remote_attribute');

        /** @var string $formAttribute */
        $formAttribute = $this->container->getParameter('direct.api.form_attribute');

        /** @var string $safeAttribute */
        $safeAttribute = $this->container->getParameter('direct.api.safe_attribute');

        /** @var string $unsafeAttribute */
        $unsafeAttribute = $this->container->getParameter('direct.api.unsafe_attribute');

        $this->remoteAttribute = $remoteAttribute;
        $this->formAttribute = $formAttribute;
        $this->safeAttribute = $safeAttribute;
        $this->unsafeAttribute = $unsafeAttribute;
        $this->api = $this->createApi();
    }

    /**
     * Check if the controller has any method exposed.
     *
     * @return bool true if has exposed, otherwise return false
     */
    public function isExposed(): bool
    {
        return null !== $this->api;
    }

    /**
     * Return the api.
     *
     * @return ?array<int, array{'name': string, 'len': int, 'formHandler': bool}>
     */
    public function getApi(): ?array
    {
        return $this->api;
    }

    /**
     * Return the name of exposed direct Action.
     */
    public function getActionName(): string
    {
        return \str_replace('Controller', '', $this->reflection->getShortName());
    }

    /**
     * Check the method access type.
     */
    public function getMethodAccess(string $method): string
    {
        $doc = $this->reflection->getMethod($method)->getDocComment();

        // default access type is none
        $access = 'n';

        if (\is_string($doc) && \strlen($doc) > 0) {
            $safe = \preg_match('/'.$this->safeAttribute.'/i', $doc);
            $unsafe = \preg_match('/'.$this->unsafeAttribute.'/i', $doc);

            if ($safe) {
                $access = 'secure';
            } elseif ($unsafe) {
                $access = 'anonymous';
            }
        }

        return $access;
    }

    /**
     * Try to create the controller api.
     *
     * @return ?array<int, array{'name': string, 'len': int, 'formHandler': bool}>
     */
    protected function createApi(): ?array
    {
        $api = [];

        // get public methods from controller
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $mApi = $this->getMethodApi($method);

            if ($mApi) {
                $api[] = $mApi;
            }
        }

        return \count($api) ? $api : null;
    }

    /**
     * Return the api of method.
     *
     * @return ?array{'name': string, 'len': int, 'formHandler': bool}
     */
    private function getMethodApi(\ReflectionMethod $method): ?array
    {
        $doc = $method->getDocComment();
        if (\is_string($doc) && \strlen($doc) > 0) {
            $isRemote = \preg_match('/'.$this->remoteAttribute.'/i', $doc);
            if ($isRemote) {
                return [
                    'name' => \str_replace('Action', '', $method->getName()),
                    'len' => $method->getNumberOfParameters(),
                    'formHandler' => (bool) \preg_match('/'.$this->formAttribute.'/i', $doc),
                ];
            }
        }

        return null;
    }
}
