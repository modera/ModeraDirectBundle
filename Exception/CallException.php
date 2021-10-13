<?php

namespace Modera\DirectBundle\Exception;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2021 Modera Foundation
 */
class CallException extends \RuntimeException
{
    /**
     * @param string|null $message
     * @param \Throwable|null $previous
     * @param int $code
     */
    public function __construct($message = '', \Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}