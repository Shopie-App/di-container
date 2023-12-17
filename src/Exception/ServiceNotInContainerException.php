<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Exception;

class ServiceNotInContainerException extends \Exception
{
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct('Object '.$message.' not found in container collection', $code, $previous);
    }
}