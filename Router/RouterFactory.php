<?php

namespace Modera\DirectBundle\Router;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RouterFactory implements RouterFactoryInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(Request $request): Router
    {
        return new Router($this->container);
    }
}
