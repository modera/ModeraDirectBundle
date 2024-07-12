<?php

namespace Modera\DirectBundle\Controller;

use Modera\DirectBundle\Exception\CallException;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
trait ControllerTrait
{
    protected function createDirectCallException(string $message = '', ?\Throwable $previous = null): CallException
    {
        return new CallException($message, $previous);
    }
}
