<?php

namespace Modera\DirectBundle\Router;

use Symfony\Component\HttpFoundation\Request;

interface RouterFactoryInterface
{
    public function create(Request $request): Router;
}
