<?php

namespace Modera\DirectBundle\Controller;

use Modera\DirectBundle\Api\Api;
use Modera\DirectBundle\Router\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class DirectController extends Controller
{
    protected function getContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = $this->container;

        return $container;
    }

    protected function isDebug(): bool
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');

        return $kernel->isDebug();
    }

    /**
     * Generate the ExtDirect API.
     */
    public function getApiAction(): Response
    {
        // instantiate the api object
        $api = new Api($this->getContainer());

        if ($this->isDebug()) {
            $exceptionLogStr = 'console.error("Remote Call:", event);';
        } else {
            /** @var string $exceptionMessage */
            $exceptionMessage = $this->getContainer()->getParameter('direct.exception.message');
            $exceptionLogStr = \sprintf('console.error(%s);', \json_encode($exceptionMessage));
        }
        // create the response
        $response = new Response(\sprintf(\implode(\PHP_EOL, [
            'Ext.Direct.addProvider(%s);',
            'Ext.direct.Manager.on("exception", function(event) {',
            '    %s',
            '});',
        ]), $api, $exceptionLogStr));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Generate the Remoting ExtDirect API.
     */
    public function getRemotingAction(): Response
    {
        // instantiate the api object
        $api = new Api($this->getContainer());

        // create the response
        $response = new Response(\sprintf('Ext.app.REMOTING_API = %s;', $api));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Route the ExtDirect calls.
     */
    public function routeAction(): Response
    {
        // instantiate the router object
        $router = new Router($this->getContainer());

        // create response
        $response = new Response($router->route());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
