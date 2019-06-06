<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:30
 */

namespace ESD\Core;


use Throwable;

class Exception extends \Exception
{
    protected $trace = true;

    protected $time;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->time = (int)(microtime(true) * 1000 * 1000);
    }

    /**
     * @return bool
     */
    public function isTrace(): bool
    {
        return $this->trace;
    }

    /**
     * @param bool $trace
     */
    public function setTrace(bool $trace): void
    {
        $this->trace = $trace;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }
}