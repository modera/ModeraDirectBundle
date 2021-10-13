<?php

namespace Modera\DirectBundle\Controller;

use Modera\DirectBundle\Exception\CallException;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
trait ControllerTrait
{
    /**
     * @param string|null $message
     * @param \Throwable|null $previous
     * @return CallException
     */
    protected function createDirectCallException($message = null, \Throwable $previous = null)
    {
        return new CallException($message, $previous);
    }
}