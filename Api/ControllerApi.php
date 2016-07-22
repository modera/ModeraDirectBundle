<?php

namespace Modera\DirectBundle\Api;

class ControllerApi
{
    /**
     * Store the controller reflection object.
     * 
     * @var \Reflection
     */
    protected $reflection;

    /**
     * The controller ExtDirect api.
     * 
     * @var array
     */
    protected $api;

    /**
     * The application container.
     *
     * @var Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Initialize the object.
     * 
     * @param \Symfony\Component\Container $container
     * @param string                       $controller
     */
    public function __construct($container, $controller)
    {
        try {
            $this->reflection = new \ReflectionClass($controller);
        } catch (Exception $e) {
            // @todo: throw an exception
        }

        $this->container = $container;
        $this->remoteAttribute = $container->getParameter('direct.api.remote_attribute');
        $this->formAttribute = $container->getParameter('direct.api.form_attribute');
        $this->safeAttribute = $container->getParameter('direct.api.safe_attribute');
        $this->unsafeAttribute = $container->getParameter('direct.api.unsafe_attribute');
        $this->api = $this->createApi();
    }

    /**
     * Check if the controller has any method exposed.
     *
     * @return bool true if has exposed, otherwise return false
     */
    public function isExposed()
    {
        return (null != $this->api) ? true : false;
    }

    /**
     * Return the api.
     * 
     * @return array
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Return the name of exposed direct Action.
     * 
     * @return string
     */
    public function getActionName()
    {
        return str_replace('Controller', '', $this->reflection->getShortName());
    }

    /**
     * Check the method access type.
     *
     * @param string $method
     *
     * @return string s = safe access u = unsafe access n = none
     */
    public function getMethodAccess($method)
    {
        $doc = $this->reflection->getMethod($method)->getDocComment();

        // default access type is none
        $access = 'n';

        if (strlen($doc) > 0) {
            $safe = preg_match('/'.$this->safeAttribute.'/i', $doc);
            $unsafe = preg_match('/'.$this->unsafeAttribute.'/i', $doc);

            if ($safe) {
                $access = 'secure';
            } elseif ($unsafe) {
                $access = 'anonymous';
            }
        }

        return $access;
    }

    /**
     * Try create the controller api.
     *
     * @return array
     */
    protected function createApi()
    {
        $api = null;

        // get public methods from controller
        $methods = $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $mApi = $this->getMethodApi($method);

            if ($mApi) {
                $api[] = $mApi;
            }
        }

        return $api;
    }

    /**
     * Return the api of method.
     *
     * @param \ReflectionMethod $method
     *
     * @return mixed (array/boolean)
     */
    private function getMethodApi($method)
    {
        $api = false;

        if (strlen($method->getDocComment()) > 0) {
            $doc = $method->getDocComment();

            $isRemote = preg_match('/'.$this->remoteAttribute.'/i', $doc);

            if ($isRemote) {
                $api['name'] = str_replace('Action', '', $method->getName());
                $api['len'] = $method->getNumberOfParameters();

                if (preg_match('/'.$this->formAttribute.'/i', $doc)) {
                    $api['formHandler'] = true;
                }
            }
        }

        return $api;
    }
}
