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
            $exceptionLogStr = 'Ext.direct.Manager.on("exception", function(error) { console.error(Ext.util.Format.format("Remote Call: {0}.{1}\n{2}", error.action, error.method, error.message, error.where)); return false; });';
        } else {
            /** @var string $exceptionMessage */
            $exceptionMessage = $this->getContainer()->getParameter('direct.exception.message');
            $exceptionLogStr = \sprintf('Ext.direct.Manager.on("exception", function(error) { console.error("%s"); });', $exceptionMessage);
        }
        // create the response
        $response = new Response(\sprintf('Ext.Direct.addProvider(%s);%s', $api, $exceptionLogStr));
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
