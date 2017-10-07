<?php

namespace Modera\DirectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Modera\DirectBundle\Api\Api;
use Modera\DirectBundle\Router\Router;

class DirectController extends Controller
{
    /**
     * Generate the ExtDirect API.
     *
     * @return Response
     */
    public function getApiAction(Request $request)
    {
        $this->validateRequest($request);

        // instantiate the api object
        $api = new Api($this->container);

        $debug = $this->container->get('kernel')->isDebug();

        if ($debug) {
            $exceptionLogStmt =
                'Ext.direct.Manager.on("exception", function(error){console.error(Ext.util.Format.format("Remote Call: {0}.{1}\n{2}", error.action, error.method, error.message, error.where)); return false;});';
        } else {
            $exceptionLogStmt =
                sprintf('Ext.direct.Manager.on("exception", function(error){console.error("%s");});', $this->container->getParameter('direct.exception.message'));
        }

        $js = $this->renderView('@ModeraDirect/Direct/api.js.twig', array(
            'api' => $api,
            'exceptionLogStmt' => $exceptionLogStmt,
        ));

        // create the response
        $response = new Response($js);
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Generate the Remoting ExtDirect API.
     *
     * @return Response
     */
    public function getRemotingAction()
    {
        // instantiate the api object
        $api = new Api($this->container);

        $debug = $this->container->get('kernel')->isDebug();

        // create the response
        $response = new Response(sprintf('Ext.app.REMOTING_API = %s;', $api));
        $response->headers->set('Content-Type', 'text/javascript');

        return $response;
    }

    /**
     * Route the ExtDirect calls.
     *
     * @return Response
     */
    public function routeAction(Request $request)
    {
        $this->validateRequest($request);

        // instantiate the router object
        $router = new Router($this->container);

        // create response
        $response = new Response($router->route());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function validateRequest(Request $request)
    {
        if ($request->headers->has('referer')) {
            $allowedReferers = $this->getAllowedReferers();
            if (count($allowedReferers) == 0) {
                return;
            }

            $referer = $request->headers->get('referer');

            $isAllowed = false;
            foreach ($allowedReferers as $allowedReferer) {
                if (preg_match($allowedReferer, $referer)) {
                    $isAllowed = true;

                    break;
                }
            }

            if (!$isAllowed) {
                throw new \RuntimeException('Given referer is not in whitelist.');
            }
        }
    }

    private function getAllowedReferers()
    {
        return [
        ];
    }
}
