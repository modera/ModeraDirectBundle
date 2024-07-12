<?php

namespace Modera\DirectBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class Api
{
    protected ContainerInterface $container;

    /**
     * The ExtDirect JSON API description.
     */
    protected string $api;

    /**
     * Initialize the API.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->api = $this->createApi();
    }

    /**
     * Return the API in JSON format.
     */
    public function __toString()
    {
        return $this->api;
    }

    /**
     * Create the ExtDirect API based on controllers files.
     */
    protected function createApi(): string
    {
        $bundles = $this->getControllers();

        $actions = [];

        foreach ($bundles as $bundle => $controllers) {
            $bundleShortName = \str_replace('Bundle', '', $bundle);

            foreach ($controllers as $controller) {
                $api = new ControllerApi($this->container, $controller);

                if ($api->isExposed()) {
                    $actions[$bundleShortName.'_'.$api->getActionName()] = $api->getApi();
                }
            }
        }

        /** @var RequestStack $rs */
        $rs = $this->container->get('request_stack');
        $request = $rs->getCurrentRequest();
        $baseUrl = $request ? $request->getBaseUrl() : '';

        /** @var string $routePattern */
        $routePattern = $this->container->getParameter('direct.api.route_pattern');

        /** @var bool $enableBuffer */
        $enableBuffer = $this->container->getParameter('direct.api.enable_buffer');

        /** @var string $type */
        $type = $this->container->getParameter('direct.api.type');

        /** @var string $namespace */
        $namespace = $this->container->getParameter('direct.api.namespace');

        /** @var string $id */
        $id = $this->container->getParameter('direct.api.id');

        $api = \json_encode([
            'url' => $baseUrl.$routePattern,
            'enableBuffer' => $enableBuffer,
            'type' => $type,
            'namespace' => $namespace,
            'id' => $id,
            'actions' => $actions,
        ]);

        if (!$api) {
            throw new \RuntimeException('API creation failed');
        }

        return $api;
    }

    /**
     * Get all controllers from all bundles.
     *
     * @return array<string, string[]> Controllers list
     */
    protected function getControllers(): array
    {
        $finder = new ControllerFinder();

        /** @var array<string, string[]> $controllers */
        $controllers = [];

        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');

        foreach ($kernel->getBundles() as $bundle) {
            $found = $finder->getControllers($bundle);
            if (!empty($found)) {
                $controllers[$bundle->getName()] = $found;
            }
        }

        return $controllers;
    }
}
