<?php

namespace Modera\DirectBundle\Router;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Modera\DirectBundle\Api\ControllerApi;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Router
{
    /**
     * @var \Modera\DirectBundle\Router\Request
     */
    protected $request;

    /**
     * @var \Modera\DirectBundle\Router\Response
     */
    protected $response;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        /* @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');

        $this->container = $container;
        $this->request = new Request($requestStack->getCurrentRequest());
        $this->response = new Response($this->request->getCallType(), $this->request->isUpload());
        $this->defaultAccess = $container->getParameter('direct.api.default_access');
        $this->session = $this->container->get('session')->get($container->getParameter('direct.api.session_attribute'));
    }

    /**
     * Do the ExtDirect routing processing.
     *
     * @return string
     */
    public function route()
    {
        $batch = array();

        foreach ($this->request->getCalls() as $call) {
            $batch[] = $this->dispatch($call);
        }

        return $this->response->encode($batch);
    }

    /**
     * Dispatch a remote method call.
     *
     * @param Call $call
     *
     * @return mixed
     */
    private function dispatch(Call $call)
    {
        $api = new ControllerApi($this->container, $this->getControllerClass($call->getAction()));

        $controller = $this->resolveController($call->getAction());
        $method = $call->getMethod().'Action';
        $accessType = $api->getMethodAccess($method);

        if (!is_callable(array($controller, $method))) {
            //todo: throw an exception method not callable
            return false;
        } elseif ($this->defaultAccess == 'secure' && $accessType != 'anonymous') {
            if (!$this->session) {
                $result = $call->getException(new \Exception('Access denied!'));
            }
        } elseif ($accessType == 'secure') {
            if (!$this->session) {
                $result = $call->getException(new \Exception('Access denied!'));
            }
        } elseif ('form' == $this->request->getCallType()) {
            $result = $call->getResponse($controller->$method($call->getData(), $this->request->getFiles()));
        }

        if (!isset($result)) {
            try {
                $result = $controller->$method($call->getData());
                $result = $call->getResponse($result);
            } catch (\Exception $e) {
                $result = $call->getException($e, $this->container->getParameter('kernel.environment'));
            }
        }

        return $result;
    }

    /**
     * Resolve the called controller from action.
     *
     * @param string $action
     *
     * @return <type>
     */
    private function resolveController($action)
    {
        $class = $this->getControllerClass($action);

        try {
            $controller = new $class();

            if ($controller instanceof ContainerAwareInterface) {
                $controller->setContainer($this->container);
            }

            return $controller;
        } catch (\Exception $e) {
            // todo: handle exception
            throw $e;
        }
    }

    /**
     * Return the controller class name.
     *
     * @param string $action
     */
    private function getControllerClass($action)
    {
        list($bundleName, $controllerName) = explode('_', $action);
        $bundleName .= 'Bundle';

        /* @var BundleInterface $bundle */
        $bundle = $this->container->get('kernel')->getBundle($bundleName);
        $namespace = $bundle->getNamespace().'\\Controller';

        $class = $namespace.'\\'.$controllerName.'Controller';

        return $class;
    }
}
